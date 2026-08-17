<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Sunat;

/**
 * Convierte un monto a su representacion en letras para la leyenda obligatoria
 * SUNAT (codigo 1000), ej: 118.00 PEN -> "SON CIENTO DIECIOCHO CON 00/100 SOLES".
 */
final class NumeroALetras
{
    private const UNIDADES = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    private const ESPECIALES = [
        10 => 'DIEZ', 11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE', 15 => 'QUINCE',
        16 => 'DIECISEIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO', 19 => 'DIECINUEVE',
        20 => 'VEINTE', 21 => 'VEINTIUNO', 22 => 'VEINTIDOS', 23 => 'VEINTITRES', 24 => 'VEINTICUATRO',
        25 => 'VEINTICINCO', 26 => 'VEINTISEIS', 27 => 'VEINTISIETE', 28 => 'VEINTIOCHO', 29 => 'VEINTINUEVE',
    ];
    private const DECENAS = ['', '', '', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    private const CENTENAS = [
        '', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS',
        'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS',
    ];

    public function convertir(float $monto, string $moneda = 'PEN'): string
    {
        $entero = (int) floor($monto);
        $decimal = (int) round(($monto - $entero) * 100);
        $nombreMoneda = match (strtoupper($moneda)) {
            'PEN' => 'SOLES',
            'USD' => 'DOLARES AMERICANOS',
            default => strtoupper($moneda),
        };
        $letras = $entero === 0 ? 'CERO' : $this->enteroALetras($entero);
        return sprintf('SON %s CON %02d/100 %s', trim($letras), $decimal, $nombreMoneda);
    }

    private function enteroALetras(int $n): string
    {
        if ($n === 0) {
            return '';
        }
        if ($n === 100) {
            return 'CIEN';
        }
        if ($n < 10) {
            return self::UNIDADES[$n];
        }
        if ($n < 30) {
            return self::ESPECIALES[$n] ?? '';
        }
        if ($n < 100) {
            $d = intdiv($n, 10);
            $u = $n % 10;
            return self::DECENAS[$d] . ($u ? ' Y ' . self::UNIDADES[$u] : '');
        }
        if ($n < 1000) {
            $c = intdiv($n, 100);
            $r = $n % 100;
            return trim(self::CENTENAS[$c] . ' ' . $this->enteroALetras($r));
        }
        if ($n < 1000000) {
            $miles = intdiv($n, 1000);
            $r = $n % 1000;
            $pref = $miles === 1 ? 'MIL' : $this->enteroALetras($miles) . ' MIL';
            return trim($pref . ' ' . $this->enteroALetras($r));
        }
        $millones = intdiv($n, 1000000);
        $r = $n % 1000000;
        $pref = $millones === 1 ? 'UN MILLON' : $this->enteroALetras($millones) . ' MILLONES';
        return trim($pref . ' ' . $this->enteroALetras($r));
    }
}
