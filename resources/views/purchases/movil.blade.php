@extends('layouts.app')

@section('title', 'Registrar Compra (Móvil)')

@section('content')
    <div class="max-w-md mx-auto">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-slate-800">📱 Registrar Compra</h2>
                <p class="text-sm text-slate-500">Desde tu celular, con fotos</p>
            </div>
            <a href="{{ route('compras.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Ver compras</a>
        </div>

        @include('partials.errors')

        <form method="POST" action="{{ route('compras.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            {{-- Fotos --}}
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
                <h3 class="font-bold text-slate-800 mb-3">📷 Fotos</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Foto de la boleta/factura</label>
                        <input type="file" name="photo_receipt" accept="image/*" capture="environment"
                            class="w-full rounded-lg border border-slate-200 px-4 py-3 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Foto de los productos</label>
                        <input type="file" name="photo_products" accept="image/*" capture="environment"
                            class="w-full rounded-lg border border-slate-200 px-4 py-3 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                </div>
            </div>

            {{-- Datos --}}
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
                <h3 class="font-bold text-slate-800 mb-3">🛒 Datos</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Proveedor</label>
                        <select name="supplier_id" class="w-full rounded-lg border border-slate-200 px-4 py-3 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                            <option value="">— Sin proveedor —</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">N° de documento</label>
                        <input type="text" name="document" placeholder="Ej: B001-123"
                            class="w-full rounded-lg border border-slate-200 px-4 py-3 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                </div>
            </div>

            {{-- Productos --}}
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-slate-800">🥬 Productos</h3>
                    <button type="button" onclick="addRow()" class="inline-flex items-center gap-1 rounded-lg bg-slate-800 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                        + Agregar
                    </button>
                </div>
                <div id="rows" class="space-y-3"></div>
            </div>

            <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-brand-500 to-emerald-600 px-5 py-4 text-base font-bold text-white shadow-lg hover:from-brand-600 hover:to-emerald-700">
                ✅ Guardar Compra
            </button>
        </form>
    </div>

    <script>
        const products = @json($products);
        let idx = 0;

        function addRow() {
            const i = idx++;
            const options = products.map(p => `<option value="${p.id}" data-cost="${p.cost}">${p.name}</option>`).join('');
            const div = document.createElement('div');
            div.className = 'rounded-xl border border-slate-200 p-3 space-y-2 bg-slate-50';
            div.innerHTML = `
                <select name="items[${i}][product_id]" required onchange="onProduct(this)" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-brand-500">
                    <option value="">Seleccionar producto...</option>${options}
                </select>
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" step="0.01" min="0" name="items[${i}][cost]" value="0" placeholder="Costo" oninput="recalc()" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-brand-500">
                    <input type="number" step="0.001" min="0.01" name="items[${i}][quantity]" value="1" placeholder="Cantidad (kg)" oninput="recalc()" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-brand-500">
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-semibold text-slate-700 sub">S/ 0.00</span>
                    <button type="button" onclick="this.closest('div').remove(); recalc()" class="text-xs font-semibold text-red-500">✕ Quitar</button>
                </div>`;
            document.getElementById('rows').appendChild(div);
        }

        function onProduct(sel) {
            const cost = sel.selectedOptions[0]?.dataset.cost || 0;
            sel.closest('div').querySelector('input[name$="[cost]"]').value = cost;
            recalc();
        }

        function recalc() {
            document.querySelectorAll('#rows > div').forEach(div => {
                const cost = parseFloat(div.querySelector('input[name$="[cost]"]').value) || 0;
                const qty = parseFloat(div.querySelector('input[name$="[quantity]"]').value) || 0;
                div.querySelector('.sub').textContent = 'S/ ' + (cost * qty).toFixed(2);
            });
        }

        addRow();
    </script>
@endsection