<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Gateway\Chile;

use Facturacion\Domain\Exception\BusinessRejectionException;
use Facturacion\Domain\Exception\TransientTransmissionException;

/**
 * Cliente de transmision hacia el SII de Chile.
 *
 * - EnvioDTE  : envia un sobre XML con uno o varios DTEs firmados. Devuelve
 *               un TrackId que permite consultar el estado del proceso.
 * - getStatus : consulta el estado del DTE con el TrackId devuelto.
 *
 * En produccion se debe usar el WSDL del SII. En este adaptador el transporte
 * real se inyecta como callable para poder testear sin red; la implementacion
 * por defecto usa SOAP (SoapClient) contra los endpoints del SII.
 */
final class SiiClient
{
    public function __construct(
        private readonly string $endpoint,
        private readonly \Closure $soapFactory
    ) {
    }

    /**
     * Envia el sobre de DTEs firmados al SII y devuelve el TrackId.
     */
    public function enviarDte(array $dteSignedXmls, string $rutEmisor): string
    {
        // Arma el sobre EnvioDTE segun el esquema del SII.
        $sobre = $this->buildEnvioDte($dteSignedXmls, $rutEmisor);

        try {
            $client = ($this->soapFactory)($this->endpoint);
            $resp   = $client->__soapCall('enviar', [['archivo' => $sobre]]);
        } catch (\SoapFault $e) {
            // Fallo tecnico (timeout, WS caido) -> reintentable
            throw new TransientTransmissionException('SII EnvioDTE fallo SOAP: ' . $e->getMessage(), 0, $e);
        } catch (\Throwable $e) {
            throw new TransientTransmissionException('SII EnvioDTE error transporte: ' . $e->getMessage(), 0, $e);
        }

        // La respuesta del SII incluye el TrackId o un error de negocio.
        $trackId = $resp->RESP_HDR->TRACKID ?? null;
        if ($trackId === null) {
            throw new BusinessRejectionException(
                'SII',
                $resp->RESP_HDR->DESCRIPCION ?? 'El SII no devolvio TrackId (revise el sobre enviado)'
            );
        }

        return (string) $trackId;
    }

    /**
     * Consulta el estado de un DTE por TrackId.
     *
     * @return array{estado:string,descripcion:string}
     */
    public function consultarEstado(string $trackId, string $rutEmisor): array
    {
        try {
            $client = ($this->soapFactory)($this->endpoint);
            $resp   = $client->__soapCall('getEstado', [
                [
                    'RUT'     => $rutEmisor,
                    'TrackId' => $trackId,
                ],
            ]);
        } catch (\SoapFault $e) {
            throw new TransientTransmissionException('SII getEstado fallo SOAP: ' . $e->getMessage(), 0, $e);
        } catch (\Throwable $e) {
            throw new TransientTransmissionException('SII getEstado error transporte: ' . $e->getMessage(), 0, $e);
        }

        // El SII devuelve codigos de estado (0 = OK / aceptado, 2 = rechazado, etc.).
        $estado  = (string) ($resp->RESP_HDR->ESTADO ?? $resp->ESTADO ?? '0');
        $descrip = (string) ($resp->RESP_HDR->DESCRIPCION ?? $resp->DESCRIPCION ?? $resp?->RESP_BODY?->OBS ?? '');

        return [
            'estado'      => $estado,
            'descripcion' => $descrip,
        ];
    }

    /**
     * Construye el sobre EnvioDTE con los DTEs firmados.
     * Formato minimalista segun el esquema del SII.
     */
    private function buildEnvioDte(array $dteSignedXmls, string $rutEmisor): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');

        $envio = $doc->createElement('EnvioDTE');
        $envio->setAttribute('version', '1.0');
        $doc->appendChild($envio);

        $id = $doc->createElement('SetDTE');
        $id->setAttribute('ID', 'SetDte' . date('YmdHis'));
        $envio->appendChild($id);

        $caratula = $doc->createElement('Caratula');
        $caratula->setAttribute('version', '1.0');
        $caratula->appendChild($doc->createElement('RutEnvia', $rutEmisor));
        $caratula->appendChild($doc->createElement('RutReceptor', '60803000-7')); // SII
        $caratula->appendChild($doc->createElement('FchResol', date('Y-m-d')));
        $caratula->appendChild($doc->createElement('NroResol', '0'));
        $id->appendChild($caratula);

        foreach ($dteSignedXmls as $xml) {
            $fragment = $doc->createDocumentFragment();
            $ok = @$fragment->appendXML($xml);
            if ($ok) {
                $id->appendChild($fragment);
            }
        }

        return $doc->saveXML();
    }
}