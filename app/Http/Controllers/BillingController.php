<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\Billing\BillingService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BillingController extends Controller
{
    public function __construct(private readonly BillingService $billing)
    {
    }

    /** Reintenta la emisión del comprobante de una venta. */
    public function retry(Sale $venta): RedirectResponse
    {
        if (!$this->billing->facturable($venta)) {
            return back()->with('error', 'Esta venta no genera comprobante electrónico.');
        }

        $doc = $this->billing->emitForSale($venta);

        return match (true) {
            $doc?->aceptado() => back()->with('success', 'Comprobante emitido: ' . $doc->estadoLabel() . ' (' . $doc->external_id . ').'),
            $doc?->status === 'rechazado' => back()->with('error', 'Comprobante rechazado por SUNAT: ' . $doc->message),
            default => back()->with('error', 'No se pudo emitir: ' . ($doc?->message ?? 'sin detalle') . '. Puede reintentar.'),
        };
    }

    /** Descarga el XML firmado (si está disponible en el almacenamiento). */
    public function downloadXml(Sale $venta): BinaryFileResponse|RedirectResponse
    {
        return $this->download($venta, 'xml_path', 'application/xml');
    }

    /** Descarga el CDR / respuesta de la autoridad. */
    public function downloadCdr(Sale $venta): BinaryFileResponse|RedirectResponse
    {
        return $this->download($venta, 'cdr_path', 'application/zip');
    }

    private function download(Sale $venta, string $field, string $mime): BinaryFileResponse|RedirectResponse
    {
        $doc = $venta->electronicDocument;
        $relative = $doc?->{$field};

        if (!$relative) {
            return back()->with('error', 'Archivo aún no disponible para este comprobante.');
        }

        $full = rtrim((string) config('billing.storage_path'), '/\\') . DIRECTORY_SEPARATOR . ltrim($relative, '/\\');
        if (!is_file($full)) {
            return back()->with('error', 'El archivo no se encuentra en el almacenamiento del servicio de facturación.');
        }

        return response()->download($full, basename($full), ['Content-Type' => $mime]);
    }
}
