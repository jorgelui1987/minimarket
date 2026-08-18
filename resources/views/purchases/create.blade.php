@extends('layouts.app')

@section('title', 'Nueva Compra')

@section('content')
    <div class="max-w-4xl">
        <a href="{{ route('compras.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Volver a compras</a>
        <h2 class="text-xl font-extrabold text-slate-800 mt-2 mb-5">Registrar Compra</h2>

        @include('partials.errors')

        <form method="POST" action="{{ route('compras.store') }}" id="purchaseForm" enctype="multipart/form-data">
            @csrf
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 mb-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Proveedor</label>
                        <select name="supplier_id" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                            <option value="">— Sin proveedor —</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">N° de documento (factura/guía)</label>
                        <input type="text" name="document" value="{{ old('document') }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                </div>

                {{-- Fotos de la compra (boleta y productos) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 pt-4 border-t border-slate-100">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">📷 Foto de la boleta/factura</label>
                        <input type="file" name="photo_receipt" accept="image/*" capture="environment"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                        <p class="text-xs text-slate-400 mt-1">Toma una foto del comprobante del mercado</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">📦 Foto de los productos</label>
                        <input type="file" name="photo_products" accept="image/*" capture="environment"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                        <p class="text-xs text-slate-400 mt-1">Toma una foto de lo que trajiste</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-800">Productos</h3>
                    <button type="button" onclick="addRow()" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-800 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Agregar producto
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-slate-500 text-left">
                            <tr>
                                <th class="py-2 font-semibold">Producto</th>
                                <th class="py-2 font-semibold w-28 text-right">Costo</th>
                                <th class="py-2 font-semibold w-24 text-center">Cantidad</th>
                                <th class="py-2 font-semibold w-28 text-right">Subtotal</th>
                                <th class="py-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="rows"></tbody>
                        <tfoot>
                            <tr class="border-t-2 border-slate-200">
                                <td colspan="3" class="py-3 text-right font-bold text-slate-700">Total</td>
                                <td class="py-3 text-right font-extrabold text-brand-600 text-lg" id="grandTotal">S/ 0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="flex gap-3 pt-5">
                    <button type="submit" class="rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:from-brand-600 hover:to-emerald-700">Registrar Compra</button>
                    <a href="{{ route('compras.index') }}" class="rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</a>
                </div>
            </div>
        </form>
    </div>

    <script>
        const products = @json($products);
        let idx = 0;

        function addRow() {
            const i = idx++;
            const options = products.map(p => `<option value="${p.id}" data-cost="${p.cost}">${p.name}</option>`).join('');
            const tr = document.createElement('tr');
            tr.className = 'border-b border-slate-100';
            tr.innerHTML = `
                <td class="py-2 pr-2">
                    <select name="items[${i}][product_id]" required onchange="onProduct(this)" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500">
                        <option value="">Seleccionar...</option>${options}
                    </select>
                </td>
                <td class="py-2 px-1"><input type="number" step="0.01" min="0" name="items[${i}][cost]" value="0" oninput="recalc()" class="w-full text-right rounded-lg border border-slate-200 px-2 py-2 text-sm outline-none focus:border-brand-500"></td>
                <td class="py-2 px-1"><input type="number" step="1" min="1" name="items[${i}][quantity]" value="1" oninput="recalc()" class="w-full text-center rounded-lg border border-slate-200 px-2 py-2 text-sm outline-none focus:border-brand-500"></td>
                <td class="py-2 text-right font-medium text-slate-800 sub">S/ 0.00</td>
                <td class="py-2 text-center"><button type="button" onclick="this.closest('tr').remove(); recalc()" class="text-slate-400 hover:text-red-600">✕</button></td>`;
            document.getElementById('rows').appendChild(tr);
        }

        function onProduct(sel) {
            const cost = sel.selectedOptions[0]?.dataset.cost || 0;
            sel.closest('tr').querySelector('input[name$="[cost]"]').value = cost;
            recalc();
        }

        function recalc() {
            let total = 0;
            document.querySelectorAll('#rows tr').forEach(tr => {
                const cost = parseFloat(tr.querySelector('input[name$="[cost]"]').value) || 0;
                const qty = parseFloat(tr.querySelector('input[name$="[quantity]"]').value) || 0;
                const sub = cost * qty;
                total += sub;
                tr.querySelector('.sub').textContent = 'S/ ' + sub.toFixed(2);
            });
            document.getElementById('grandTotal').textContent = 'S/ ' + total.toFixed(2);
        }

        addRow();
    </script>
@endsection
