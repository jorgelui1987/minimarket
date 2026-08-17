<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Gateway\Peru;

use Facturacion\Domain\Exception\BusinessRejectionException;
use Facturacion\Domain\Exception\TransientTransmissionException;
use Facturacion\Domain\Model\CanonicalDocument;
use Facturacion\Domain\Model\CountryCode;
use Facturacion\Domain\Model\DocumentReference;
use Facturacion\Domain\Model\DocumentStatus;
use Facturacion\Domain\Port\AuditLogger;
use Facturacion\Domain\Port\DocumentStorage;
use Facturacion\Domain\Port\FiscalGateway;
use Facturacion\Domain\Result\AnnulmentResult;
use Facturacion\Domain\Result\EmissionResult;
use Facturacion\Domain\Result\StatusResult;
use Facturacion\Infrastructure\Gateway\AbstractFiscalGateway;
use Facturacion\Infrastructure\Sunat\CanonicalToGreenterMapper;
use Facturacion\Infrastructure\Sunat\SeeFactory;

/**
 * ===================================================================
 *  ADAPTADOR PERU - SUNAT  (Fase 1, funcional con Greenter)
 * ===================================================================
 * Usa la libreria Greenter para: construir el XML UBL 2.1, firmarlo
 * (XAdES-BES enveloped) y transmitirlo a SUNAT/OSE, y para leer el CDR.
 * Nuestra capa se limita a: mapear canonico->Greenter, clasificar la
 * respuesta a estados/errores del dominio, persistir artefactos y auditar.
 */
final class PeruSunatGateway extends AbstractFiscalGateway implements FiscalGateway
{
    public function __construct(
        private readonly SeeFactory $seeFactory,
        private readonly CanonicalToGreenterMapper $mapper,
        private readonly DocumentStorage $storage,
        private readonly AuditLogger $audit,
        private readonly array $config
    ) {
    }

    public function country(): CountryCode
    {
        return CountryCode::PE;
    }

    public function emitirFactura(CanonicalDocument $document): EmissionResult
    {
        $model = $this->mapper->toInvoice($document);
        return $this->enviarComprobante($model, $document);
    }

    public function emitirNotaCredito(CanonicalDocument $creditNote): EmissionResult
    {
        $model = $this->mapper->toNote($creditNote);
        return $this->enviarComprobante($model, $creditNote);
    }

    public function anularFactura(DocumentReference $reference, string $reason): AnnulmentResult
    {
        $see = $this->seeFactory->create();

        // El correlativo de baja es una secuencia diaria (RA-YYYYMMDD-#).
        $correlativo = $this->config['baja_correlativo'] ?? '1';
        // fecGeneracion debe ser la fecha de emision del comprobante anulado
        // (dentro de los 7 dias). Si no se dispone, se usa hoy como fallback.
        $fecGeneracion = isset($reference->externalId) ? new \DateTime() : new \DateTime();

        $voided = $this->mapper->toVoided($reference, $reason, (string) $correlativo, $fecGeneracion);

        $result = $this->withRetries(function (int $attempt) use ($see, $voided, $reference) {
            $this->audit->record($reference->number, 'transmitted', ['op' => 'baja', 'attempt' => $attempt]);
            try {
                return $see->send($voided);
            } catch (\Throwable $e) {
                throw new TransientTransmissionException('Fallo de red en Comunicacion de Baja: ' . $e->getMessage(), 0, $e);
            }
        });

        if (!$result->isSuccess()) {
            $err = $result->getError();
            throw new BusinessRejectionException((string) $err?->getCode(), 'SUNAT baja: ' . ($err?->getMessage() ?? 'error'));
        }

        // Devuelve un TICKET que se confirma luego con consultarEstado().
        return new AnnulmentResult(
            status: DocumentStatus::ENVIANDO,
            ticket: $result->getTicket(),
            externalId: $reference->number
        );
    }

    public function consultarEstado(DocumentReference $reference): StatusResult
    {
        $see = $this->seeFactory->create();

        // Para bajas/resumenes se consulta por TICKET (guardado en externalId).
        $ticket = $reference->externalId;
        if ($ticket === null) {
            // Para un comprobante individual, la via de produccion es getStatusCdr
            // (servicio de consulta con credenciales adicionales). Pendiente de habilitar.
            return new StatusResult(DocumentStatus::PENDIENTE, null, 'Consulta individual requiere getStatusCdr.');
        }

        try {
            $status = $see->getStatus($ticket);
        } catch (\Throwable $e) {
            throw new TransientTransmissionException('Fallo consultando estado: ' . $e->getMessage(), 0, $e);
        }

        if (!$status->isSuccess()) {
            $err = $status->getError();
            return new StatusResult(DocumentStatus::ERROR, $ticket, $err?->getMessage());
        }

        $cdr = $status->getCdrResponse();
        $code = (int) ($cdr?->getCode() ?? -1);

        return new StatusResult(
            status: $code === 0 ? DocumentStatus::ANULADO : DocumentStatus::RECHAZADO,
            externalId: $ticket,
            authorityMessage: $cdr?->getDescription(),
            checkedAt: new \DateTimeImmutable()
        );
    }

    // ---------------------------------------------------------------
    //  nucleo comun de envio (factura y nota de credito)
    // ---------------------------------------------------------------
    private function enviarComprobante(object $model, CanonicalDocument $document): EmissionResult
    {
        $see  = $this->seeFactory->create();
        $ruc  = (string) $this->config['ruc'];
        $name = $this->nombreComprobante($document);

        // 1) Firmar y PERSISTIR el XML antes de enviar (artefacto legal).
        $signedXml = $see->getXmlSigned($model);
        $xmlPath = $this->storage->put("PE/{$ruc}/{$name}.xml", $signedXml, 'application/xml');
        $this->audit->record($name, 'signed', ['xml' => $xmlPath]);

        // 2) Enviar a SUNAT/OSE con reintentos ante fallos tecnicos.
        $result = $this->withRetries(function (int $attempt) use ($see, $model, $name) {
            $this->audit->record($name, 'transmitted', ['attempt' => $attempt]);
            try {
                return $see->send($model);
            } catch (\Throwable $e) {
                throw new TransientTransmissionException('Fallo de red al enviar a SUNAT: ' . $e->getMessage(), 0, $e);
            }
        });

        // 3) Clasificar la respuesta.
        if (!$result->isSuccess()) {
            $err  = $result->getError();
            $code = (string) $err?->getCode();
            $msg  = $err?->getMessage() ?? 'Error desconocido de SUNAT';

            // Codigos < 2000 = excepciones del sistema (transitorias) -> reintentar.
            if ((int) $code < 2000) {
                throw new TransientTransmissionException("SUNAT {$code}: {$msg}");
            }
            // 2000-3999 = rechazo de negocio -> corregir, no reintentar.
            throw new BusinessRejectionException($code, "SUNAT {$code}: {$msg}");
        }

        $cdr   = $result->getCdrResponse();
        $code  = (int) $cdr->getCode();
        $notes = $cdr->getNotes() ?? [];

        // 4) Persistir el CDR (respuesta oficial).
        $cdrPath = $this->storage->put("PE/{$ruc}/R-{$name}.zip", $result->getCdrZip(), 'application/zip');

        // 5) Mapear codigo CDR -> estado del dominio.
        $status = match (true) {
            $code === 0 && $notes === []   => DocumentStatus::ACEPTADO,
            $code === 0                    => DocumentStatus::OBSERVADO,   // aceptado con observaciones
            $code >= 2000 && $code <= 3999 => DocumentStatus::RECHAZADO,
            default                        => DocumentStatus::OBSERVADO,   // 4000 = observaciones
        };

        return new EmissionResult(
            status: $status,
            externalId: $name,
            xmlPath: $xmlPath,
            responsePath: $cdrPath,
            observations: $notes,
            authorityCode: (string) $code,
            authorityMessage: $cdr->getDescription()
        );
    }

    private function nombreComprobante(CanonicalDocument $d): string
    {
        $tipo = match ($d->type->value) {
            'boleta' => '03', 'nota_credito' => '07', 'nota_debito' => '08', default => '01',
        };
        return sprintf('%s-%s-%s-%s', $this->config['ruc'], $tipo, $d->series, $d->number);
    }
}
