<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Gateway\Chile;

use Facturacion\Domain\Exception\BusinessRejectionException;
use Facturacion\Domain\Model\DocumentType;

/**
 * Gestor de folios CAF (especifico de Chile). Reserva correlativos de un
 * rango autorizado por el SII, avisa cuando quedan pocos y bloquea la
 * emision si no hay CAF vigente.
 */
final class CafFolioManager
{
    private const ALERTA_FOLIOS = 10;
    private const FOLIO_STATE_FILE = 'caf_folio_state.json';

    public function __construct(
        private readonly string $cafDirectory,
        private readonly string $stateDirectory,
        private readonly string $rutEmisor
    ) {
    }

    /**
     * @param string[] $observations
     */
    public function next(DocumentType $type, array &$observations = []): int
    {
        $caf    = $this->loadActiveCaf($type);
        $dteTyp = $this->siiDocumentType($type);
        $key    = "{$dteTyp}:{$caf['desde']}-{$caf['hasta']}";
        $state  = $this->loadState();

        $used      = $state[$key] ?? 0;
        $siguiente = $caf['desde'] + $used;

        if ($siguiente > $caf['hasta']) {
            throw new BusinessRejectionException('300', "CAF agotado tipo {$dteTyp} (rango {$caf['desde']}-{$caf['hasta']}). Solicite uno nuevo al SII.");
        }

        $state[$key] = $used + 1;
        $this->saveState($state);

        $restantes = $caf['hasta'] - $siguiente;
        if ($restantes <= self::ALERTA_FOLIOS) {
            $observations[] = "Quedan {$restantes} folios en el CAF tipo {$dteTyp}. Solicite renovacion al SII.";
        }

        return $siguiente;
    }

    /**
     * @return array{desde:int,hasta:int,fechaAutorizacion:string}
     */
    private function loadActiveCaf(DocumentType $type): array
    {
        $dir = rtrim($this->cafDirectory, '/\\');
        if (!is_dir($dir)) {
            throw new BusinessRejectionException('300', "No hay directorio de CAF: {$dir}.");
        }

        $dteType = $this->siiDocumentType($type);
        $files   = glob($dir . DIRECTORY_SEPARATOR . $dteType . '-*.xml');

        if ($files === false || $files === []) {
            throw new BusinessRejectionException('300', "No hay CAF para tipo {$dteType}. Solicite uno al SII.");
        }

        $mejor = null;
        foreach ($files as $file) {
            $xml  = (string) file_get_contents($file);
            $meta = $this->parseCafMetadata($xml, $file);
            if ($mejor === null || $meta['fechaAutorizacion'] > $mejor['fechaAutorizacion']) {
                $mejor = $meta;
            }
        }

        return $mejor;
    }

    /**
     * @return array{desde:int,hasta:int,fechaAutorizacion:string}
     */
    private function parseCafMetadata(string $xml, string $file): array
    {
        $doc = new \DOMDocument();
        if (!@$doc->loadXML($xml, LIBXML_NONET | LIBXML_NOERROR)) {
            throw new BusinessRejectionException('300', 'CAF no es XML valido: ' . basename($file));
        }

        $xp    = new \DOMXPath($doc);
        $desde = $xp->evaluate('string(//RNG/D)');
        $hasta = $xp->evaluate('string(//RNG/H)');
        $fecha = $xp->evaluate('string(//FA)');
        $rut   = $xp->evaluate('string(//RE)');

        if ($desde === '' || $hasta === '') {
            throw new BusinessRejectionException('300', 'CAF sin rango de folios: ' . basename($file));
        }

        if ($rut !== '' && $rut !== $this->rutEmisor) {
            throw new BusinessRejectionException('300', "CAF no pertenece a este emisor: {$rut} != {$this->rutEmisor}");
        }

        return [
            'desde'             => (int) $desde,
            'hasta'             => (int) $hasta,
            'fechaAutorizacion' => $fecha !== '' ? $fecha : '1900-01-01',
        ];
    }

    /** @return array<string,int> */
    private function loadState(): array
    {
        $file = $this->stateFile();
        if (!is_file($file)) {
            return [];
        }
        $data = json_decode((string) file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }

    /** @param array<string,int> $state */
    private function saveState(array $state): void
    {
        $dir  = rtrim($this->stateDirectory, '/\\');
        $file = $this->stateFile();
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException("No se pudo crear directorio de estado CAF: {$dir}");
        }
        $tmp = $file . '.tmp';
        if (file_put_contents($tmp, json_encode($state, JSON_PRETTY_PRINT)) === false) {
            throw new \RuntimeException("No se pudo escribir estado de folios CAF: {$tmp}");
        }
        if (!@rename($tmp, $file)) {
            throw new \RuntimeException("No se pudo consolidar estado de folios CAF: {$file}");
        }
        @chmod($file, 0664);
    }

    private function stateFile(): string
    {
        return rtrim($this->stateDirectory, '/\\') . DIRECTORY_SEPARATOR . self::FOLIO_STATE_FILE;
    }

    private function siiDocumentType(DocumentType $type): string
    {
        return match ($type) {
            DocumentType::FACTURA      => '33',
            DocumentType::BOLETA       => '39',
            DocumentType::NOTA_CREDITO => '61',
            DocumentType::NOTA_DEBITO  => '56',
        };
    }
}
