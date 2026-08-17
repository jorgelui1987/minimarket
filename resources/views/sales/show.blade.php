@extends('layouts.app')

@section('title', 'Comprobante de Venta')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-3">
            <a href="{{ route('ventas.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Volver a ventas</a>
            <div class="flex gap-2">
                <button onclick="window.print()" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Imprimir</button>
                <a href="{{ route('pos') }}" class="rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:from-brand-600 hover:to-emerald-700">Nueva venta</a>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-8" id="ticket">
            <div class="text-center border-b border-dashed border-slate-200 pb-4 mb-4">
                <h2 class="text-lg font-extrabold text-slate-800">{{ \App\Models\Setting::get('business_name', 'Mi Minimarket') }}</h2>
                @if ($ruc = \App\Models\Setting::get('ruc'))
                    <p class="text-xs text-slate-500">RUC: {{ $ruc }}</p>
                @endif
                @if ($addr = \App\Models\Setting::get('address'))
                    <p class="text-xs text-slate-500">{{ $addr }}</p>
                @endif
                <p class="text-xs text-slate-500 mt-1">{{ ucfirst($venta->document_type) }} electrónica</p>
                <p class="text-sm font-semibold text-slate-700 mt-1">{{ $venta->series }}-{{ $venta->number }}</p>
            </div>

            <div class="grid grid-cols-2 gap-2 text-sm text-slate-500 mb-4">
                <p><span class="font-semibold text-slate-700">Fecha:</span> {{ $venta->created_at->format('d/m/Y H:i') }}</p>
                <p class="text-right"><span class="font-semibold text-slate-700">Cajero:</span> {{ $venta->user?->name ?? '—' }}</p>
                <p class="col-span-2"><span class="font-semibold text-slate-700">Cliente:</span> {{ $venta->customer?->name ?? 'Público general' }}</p>
            </div>

            <table class="w-full text-sm mb-4">
                <thead class="text-slate-500 text-left border-b border-slate-200">
                    <tr>
                        <th class="py-2 font-semibold">Producto</th>
                        <th class="py-2 font-semibold text-center">Cant.</th>
                        <th class="py-2 font-semibold text-right">P. Unit</th>
                        <th class="py-2 font-semibold text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($venta->items as $item)
                        <tr>
                            <td class="py-2 text-slate-800">{{ $item->product_name }}</td>
                            <td class="py-2 text-center text-slate-600">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                            <td class="py-2 text-right text-slate-500">S/ {{ number_format($item->price, 2) }}</td>
                            <td class="py-2 text-right font-medium text-slate-800">S/ {{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="border-t border-dashed border-slate-200 pt-4 space-y-1 text-sm">
                <div class="flex justify-between text-slate-500"><span>Op. Gravada</span><span>S/ {{ number_format($venta->subtotal, 2) }}</span></div>
                <div class="flex justify-between text-slate-500"><span>IGV (18%)</span><span>S/ {{ number_format($venta->tax, 2) }}</span></div>
                <div class="flex justify-between text-lg font-extrabold text-slate-800 pt-1"><span>Total</span><span class="text-brand-600">S/ {{ number_format($venta->total, 2) }}</span></div>
                <div class="flex justify-between text-slate-500 pt-2"><span>Forma de pago</span><span class="font-medium text-slate-700">{{ ucfirst($venta->payment_method) }}</span></div>
            </div>

            <p class="text-center text-xs text-slate-400 mt-6">{{ \App\Models\Setting::get('ticket_footer', '¡Gracias por su compra!') }}</p>
        </div>

        @if (in_array($venta->document_type, ['boleta', 'factura']))
            @php($doc = $venta->electronicDocument)
            <div class="mt-4 rounded-2xl bg-white border border-slate-100 shadow-sm p-6 print:hidden">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-700">Comprobante electrónico (SUNAT)</h3>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $doc?->estadoColor() ?? 'bg-slate-50 text-slate-500 ring-slate-200' }}">
                        {{ $doc?->estadoLabel() ?? 'No emitido' }}
                    </span>
                </div>

                @if ($doc)
                    <div class="grid grid-cols-2 gap-2 text-sm text-slate-500">
                        <p><span class="font-semibold text-slate-700">Tipo:</span> {{ ucfirst($doc->document_type) }} {{ $doc->series }}-{{ $doc->number }}</p>
                        @if ($doc->external_id)
                            <p class="text-right"><span class="font-semibold text-slate-700">ID SUNAT:</span> {{ $doc->external_id }}</p>
                        @endif
                    </div>
                    @if ($doc->message)
                        <p class="mt-2 text-xs text-slate-500">{{ $doc->message }}</p>
                    @endif
                    @if (!empty($doc->observations))
                        <ul class="mt-2 text-xs text-amber-600 list-disc list-inside">
                            @foreach ($doc->observations as $obs)
                                <li>{{ is_array($obs) ? json_encode($obs) : $obs }}</li>
                            @endforeach
                        </ul>
                    @endif
                @else
                    <p class="text-sm text-slate-500">Aún no se ha generado el comprobante electrónico de esta venta.</p>
                @endif

                <div class="flex flex-wrap gap-2 mt-4">
                    @if (!$doc || $doc->reintentable())
                        <form method="POST" action="{{ route('ventas.facturar', $venta) }}">
                            @csrf
                            <button class="rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:from-brand-600 hover:to-emerald-700">
                                {{ $doc && $doc->status !== 'pendiente' ? 'Reintentar emisión' : 'Emitir comprobante' }}
                            </button>
                        </form>
                    @endif
                    @if ($doc?->xml_path)
                        <a href="{{ route('ventas.xml', $venta) }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Descargar XML</a>
                    @endif
                    @if ($doc?->cdr_path)
                        <a href="{{ route('ventas.cdr', $venta) }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Descargar CDR</a>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection
