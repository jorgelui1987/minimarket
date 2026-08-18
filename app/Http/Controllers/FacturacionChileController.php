<?php

namespace App\Http\Controllers;

use App\Models\DteLog;
use App\Models\ElectronicDocument;
use App\Models\Sale;
use App\Services\Billing\BillingClient;
use App\Services\Billing\BillingService;
use App\Services\Billing\BillingSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * ===================================================================
 *  FACTURACIÓN CHILE (SII) — DTE
 * ===================================================================
 * Portado y adaptado del controlador original (gimnasio) al modelado
 * Laravel de este proyecto. Gestiona el ciclo de vida de los DTEs:
 * emisión desde venta, notas de crédito, reintentos, logs, XML y
 * consulta de RUT contra el SII.
 *
 * El acceso está limitado a usuarios autenticados (middleware 'auth'
 * + 'tenant' + 'subscription' definidos en routes/web.php).
 */
class FacturacionChileController extends Controller
{
    public function __construct(private readonly BillingService $billing)
    {
    }

    // =========================================================
    //  LISTADO PRINCIPAL
    // =========================================================
    public function index(Request $request): View
    {
        $filtros = [
            'desde'    => $request->query('desde', now()->startOfMonth()->toDateString()),
            'hasta'    => $request->query('hasta', now()->toDateString()),
            'tipo_dte' => $request->query('tipo_dte', ''),
            'estado'   => $request->query('estado', ''),
        ];

        $query = ElectronicDocument::query()
            ->where('country', 'CL')
            ->whereBetween('created_at', [$filtros['desde'] . ' 00:00:00', $filtros['hasta'] . ' 23:59:59']);

        if ($filtros['tipo_dte'] !== '') {
            $query->where('document_type', $filtros['tipo_dte']);
        }
        if ($filtros['estado'] !== '') {
            $query->where('status', $filtros['estado']);
        }

        $documentos = $query->latest()->with('sale')->paginate(20);

        return view('facturacion_chile.index', compact('documentos', 'filtros'));
    }

    // =========================================================
    //  VER DETALLE DTE
    // =========================================================
    public function ver($id): View|RedirectResponse
    {
        $dte = ElectronicDocument::where('country', 'CL')
            ->with(['sale', 'sale.customer'])
            ->find((int) $id);

        if (!$dte) {
            return redirect()->route('facturacionchile.index')
                ->with('error', 'DTE no encontrado.');
        }

        $logs = DteLog::where('electronic_document_id', $dte->id)
            ->latest()
            ->take(50)
            ->get();

        return view('facturacion_chile.ver', compact('dte', 'logs'));
    }

    // =========================================================
    //  EMITIR DESDE VENTA (POS)
    // =========================================================
    public function emitirVenta($venta_id): RedirectResponse
    {
        $tipoDte = (int) (request()->query('tipo', 39)); // 39 = Boleta por defecto

        $venta = Sale::with(['items', 'customer'])->find((int) $venta_id);
        if (!$venta) {
            return back()->with('error', 'Venta no encontrada.');
        }

        // Si la venta ya tiene un DTE, no reemitir.
        if ($venta->electronicDocument && $venta->electronicDocument->aceptado()) {
            return redirect()->route('facturacionchile.ver', $venta->electronicDocument->id)
                ->with('success', 'Esta venta ya tiene un DTE emitido.');
        }

        $doc = $this->billing->emitForSale($venta);

        if (!$doc) {
            return back()->with('error', 'Esta venta no genera comprobante electrónico.');
        }

        $estado = match ($doc->status) {
            'aceptado'  => 'éxito: DTE aceptado por el SII.',
            'observado' => 'éxito: DTE aceptado con observaciones.',
            'rechazado' => 'error: DTE rechazado por el SII: ' . ($doc->message ?? 'sin detalle'),
            'enviando'  => 'envío: DTE enviado al SII (TrackId: ' . ($doc->external_id ?? 'N/D') . '). Pendiente de validación.',
            default     => 'estado ' . $doc->status . ': ' . ($doc->message ?? 'sin detalle'),
        };

        return ($doc->status === 'rechazado' ? back()->with('error', $estado) : back()->with('success', $estado));
    }

    // =========================================================
    //  NOTA DE CRÉDITO (anulación)
    // =========================================================
    public function notaCredito($dte_id, Request $request): RedirectResponse
    {
        $origen = ElectronicDocument::where('country', 'CL')->find((int) $dte_id);
        if (!$origen) {
            return back()->with('error', 'DTE origen no encontrado.');
        }

        if (!$origen->sale) {
            return back()->with('error', 'Este DTE no está asociado a una venta; no se puede anular desde aquí.');
        }

        $payload = [
            'country'   => 'CL',
            'type'      => 'nota_credito',
            'series'    => 'NC' . now()->format('Y'),
            'number'    => (string) $origen->id, // el folio real lo asigna el CAF
            'issued_at' => now()->toIso8601String(),
            'currency'  => 'CLP',
            'issuer'    => $this->issuerFromSettings(),
            'customer'  => $this->customerFromSale($origen->sale),
            'lines'     => $this->linesFromSale($origen->sale),
            'subtotal'  => (float) $origen->sale->subtotal,
            'total'     => (float) $origen->sale->total,
            'reference' => [
                'country'     => 'CL',
                'type'        => $origen->document_type,
                'series'      => $origen->series,
                'number'      => $origen->number,
                'external_id' => $origen->external_id,
            ],
            'annulment_reason' => $request->input('ref_razon', 'Anulación de la operación'),
        ];

        $client = app(BillingClient::class);
        $result = $client->emitirNotaCredito($payload, "nc-{$origen->sale->tenant_id}-{$origen->id}");

        // Registrar la NC ligada a la venta.
        $nc = ElectronicDocument::create([
            'tenant_id'       => $origen->tenant_id,
            'sale_id'         => $origen->sale_id,
            'country'         => 'CL',
            'document_type'   => 'nota_credito',
            'series'          => $payload['series'],
            'number'          => $payload['number'],
            'status'          => $result->status,
            'external_id'     => $result->externalId,
            'message'         => $result->message,
            'observations'    => $result->observations,
            'xml_path'        => $result->files['xml'] ?? null,
            'cdr_path'        => $result->files['cdr'] ?? null,
            'idempotency_key' => "nc-{$origen->sale->tenant_id}-{$origen->id}",
        ]);

        // Marcar el DTE original como anulado.
        $origen->update(['status' => 'anulado']);

        return redirect()->route('facturacionchile.ver', $nc->id)
            ->with($result->aceptado() ? 'success' : 'error', $result->message ?? 'Nota de crédito procesada.');
    }

    // =========================================================
    //  REINTENTO INDIVIDUAL
    // =========================================================
    public function reintentar($id): RedirectResponse
    {
        $dte = ElectronicDocument::where('country', 'CL')->find((int) $id);
        if (!$dte) {
            return back()->with('error', 'DTE no encontrado.');
        }
        if (!$dte->sale) {
            return back()->with('error', 'Este DTE no tiene venta asociada para reintentar.');
        }
        if (!$dte->reintentable()) {
            return back()->with('error', 'Este DTE no es reintentable (estado: ' . $dte->estadoLabel() . ').');
        }

        $doc = $this->billing->emitForSale($dte->sale);

        return $doc
            ? back()->with($doc->aceptado() ? 'success' : 'error', 'Reintento: ' . ($doc->message ?? $doc->estadoLabel()))
            : back()->with('error', 'No se pudo reintentar la emisión.');
    }

    // =========================================================
    //  REINTENTO EN LOTE
    // =========================================================
    public function reintentarLote(): RedirectResponse
    {
        $pendientes = ElectronicDocument::where('country', 'CL')
            ->whereIn('status', ['pendiente', 'error'])
            ->with('sale')
            ->latest()
            ->limit(50)
            ->get();

        $ok = $ko = 0;

        foreach ($pendientes as $dte) {
            if (!$dte->sale) {
                continue;
            }
            $doc = $this->billing->emitForSale($dte->sale);
            $doc?->aceptado() ? $ok++ : $ko++;
        }

        return back()->with(
            $ok > 0 ? 'success' : 'error',
            "Procesados: $ok OK, $ko con problemas (de " . $pendientes->count() . " documentos)."
        );
    }

    // =========================================================
    //  LOG DE UN DTE
    // =========================================================
    public function logs($id): View|RedirectResponse
    {
        $dte = ElectronicDocument::where('country', 'CL')->find((int) $id);
        if (!$dte) {
            return redirect()->route('facturacionchile.index')->with('error', 'DTE no encontrado.');
        }

        $logs = DteLog::where('electronic_document_id', $dte->id)
            ->orWhere('document_id', 'like', '%' . $dte->series . '-F' . $dte->number . '%')
            ->latest()
            ->paginate(50);

        return view('facturacion_chile.logs', compact('dte', 'logs'));
    }

    // =========================================================
    //  DESCARGAR XML
    // =========================================================
    public function descargarXml($id): BinaryFileResponse|RedirectResponse
    {
        $dte = ElectronicDocument::where('country', 'CL')->find((int) $id);
        if (!$dte || !$dte->xml_path) {
            return back()->with('error', 'Sin XML disponible para este DTE.');
        }

        $full = rtrim((string) config('facturacion.storage_path'), '/\\')
            . DIRECTORY_SEPARATOR . ltrim($dte->xml_path, '/\\');

        if (!is_file($full)) {
            return back()->with('error', 'El archivo XML no se encuentra en el almacenamiento.');
        }

        $nombre = 'DTE_' . $dte->document_type . '_' . $dte->series . '_' . $dte->number . '.xml';
        return response()->download($full, $nombre, ['Content-Type' => 'application/xml']);
    }

    // =========================================================
    //  CONSULTA RUT (SII online)
    // =========================================================
    public function consultarRut(Request $request): Response
    {
        $rut = preg_replace('/[^0-9kK\-]/', '', (string) $request->query('rut', ''));
        if (!$rut) {
            return response()->json(['ok' => false, 'error' => 'RUT vacío']);
        }

        // Servicio público de consulta RUT Chile.
        $url = 'https://siichile.cl/api/rut?rut=' . urlencode($rut);
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);

        $d = json_decode(is_string($resp) ? $resp : '', true);

        if (!$d || empty($d['razon_social'])) {
            return response()->json([
                'ok'    => false,
                'error' => 'RUT no encontrado o servicio no disponible. Ingresar manualmente.',
            ]);
        }

        return response()->json([
            'ok'        => true,
            'razon'     => $d['razon_social'] ?? '',
            'giro'      => $d['giro']         ?? '',
            'direccion' => $d['direccion']    ?? '',
        ]);
    }

    // =========================================================
    //  HELPERS
    // =========================================================
    private function issuerFromSettings(): array
    {
        $cl = BillingSettings::cl();
        return [
            'doc_type'   => 'tax_id',
            'doc_number' => (string) $cl['rut_emisor'],
            'legal_name' => (string) $cl['razon_social'],
            'address'    => $cl['direccion'],
            'email'      => null,
            'tax_regime' => $cl['giro'],
        ];
    }

    private function customerFromSale(Sale $sale): array
    {
        $c = $sale->customer;
        if (!$c) {
            return ['doc_type' => 'sin_doc', 'doc_number' => '66666666-6', 'legal_name' => 'CONSUMIDOR FINAL'];
        }
        return [
            'doc_type'   => $c->doc_type === 'RUT' ? 'tax_id' : 'national_id',
            'doc_number' => (string) ($c->doc_number ?: '66666666-6'),
            'legal_name' => (string) $c->name,
            'address'    => $c->address,
            'email'      => $c->email,
        ];
    }

    private function linesFromSale(Sale $sale): array
    {
        $iva = 0.19;
        return $sale->items->map(function ($item) use ($iva) {
            $lineTotalWithIva = (float) $item->subtotal;
            $lineBase         = round($lineTotalWithIva / (1 + $iva), 2);
            $unitBase         = round(((float) $item->price) / (1 + $iva), 2);
            $ivaAmount        = round($lineTotalWithIva - $lineBase, 2);

            return [
                'sku'         => (string) ($item->product?->barcode ?? $item->product_id),
                'description' => (string) $item->product_name,
                'quantity'    => (float) $item->quantity,
                'unit'        => 'UN',
                'unit_price'  => $unitBase,
                'line_total'  => $lineBase,
                'taxes'       => [[
                    'kind'   => 'vat',
                    'rate'   => 19.0,
                    'amount' => $ivaAmount,
                ]],
            ];
        })->all();
    }
}