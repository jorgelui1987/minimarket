@extends('layouts.app')

@section('title', 'Punto de Venta')

@section('content')
    @if (! $current)
        <div class="mb-4 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
            No hay caja abierta. Puedes vender igual, pero te recomendamos <a href="{{ route('caja.index') }}" class="font-semibold underline">abrir la caja</a> primero.
        </div>
    @endif

    <form method="POST" action="{{ route('pos.store') }}" id="posForm">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Catálogo --}}
            <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
                <div class="flex items-center gap-3 mb-4">
                    <input type="text" id="search" placeholder="Buscar producto por nombre o código..." autofocus
                        class="flex-1 rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                </div>
                <div id="grid" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 max-h-[60vh] overflow-y-auto pr-1"></div>
            </div>

            {{-- Carrito --}}
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5 flex flex-col">
                <h3 class="font-bold text-slate-800 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                    Carrito
                </h3>

                <div id="cart" class="flex-1 space-y-2 overflow-y-auto max-h-[38vh]">
                    <p id="emptyCart" class="text-center text-sm text-slate-400 py-10">Agrega productos al carrito</p>
                </div>

                <div class="border-t border-slate-100 mt-3 pt-3 space-y-3">
                    <div class="flex justify-between text-sm text-slate-500"><span>{{ $taxLabel }} ({{ number_format($igvPercent, 0) }}% incl.)</span><span id="taxLabel">S/ 0.00</span></div>
                    <div class="flex justify-between text-lg font-extrabold text-slate-800"><span>Total</span><span id="totalLabel" class="text-brand-600">S/ 0.00</span></div>

                    <select name="customer_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500">
                        <option value="">Cliente: Público general</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} {{ $c->doc_number ? '· '.$c->doc_number : '' }}</option>
                        @endforeach
                    </select>

                    <div class="grid grid-cols-2 gap-2">
                        <select name="document_type" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500">
                            <option value="ticket">Ticket</option>
                            <option value="boleta">Boleta</option>
                            <option value="factura">Factura</option>
                        </select>
                        <select name="payment_method" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500">
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="yape">Yape</option>
                            <option value="plin">Plin</option>
                        </select>
                    </div>

                    <button type="submit" id="payBtn" disabled
                        class="w-full rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-4 py-3 text-sm font-bold text-white shadow disabled:opacity-40 disabled:cursor-not-allowed hover:from-brand-600 hover:to-emerald-700">
                        Cobrar
                    </button>
                </div>
            </div>
        </div>

        <div id="hiddenInputs"></div>
    </form>

    <script>
        const products = @json($products);
        const cart = {}; // id -> {id, name, price, stock, qty}
        const taxPercent = {{ $igvPercent }};
        const money = (v) => 'S/ ' + Number(v).toFixed(2);

        function renderGrid(filter = '') {
            const f = filter.toLowerCase();
            const grid = document.getElementById('grid');
            grid.innerHTML = '';
            products.filter(p => p.name.toLowerCase().includes(f) || (p.barcode || '').includes(f)).forEach(p => {
                const inCart = cart[p.id]?.qty || 0;
                const left = p.stock - inCart;
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.disabled = left <= 0;
                btn.onclick = () => addToCart(p);
                btn.className = 'text-left rounded-xl border border-slate-100 p-3 hover:border-brand-400 hover:shadow transition disabled:opacity-40 disabled:cursor-not-allowed';
                btn.innerHTML = `
                    <p class="text-sm font-semibold text-slate-800 leading-tight line-clamp-2" style="min-height:2.4em">${p.name}</p>
                    <p class="mt-1 text-brand-600 font-bold">${money(p.price)}</p>
                    <p class="text-xs text-slate-400">Stock: ${left}</p>`;
                grid.appendChild(btn);
            });
        }

        function addToCart(p) {
            if (!cart[p.id]) cart[p.id] = { ...p, qty: 0 };
            // Para productos en KG (verdulería), agrega 1 kg por defecto
            if (cart[p.id].qty < p.stock) cart[p.id].qty = (p.unit === 'KG' || p.unit === 'KGM') ? 1 : cart[p.id].qty + 1;
            renderCart();
            renderGrid(document.getElementById('search').value);
        }

        function changeQty(id, delta) {
            if (!cart[id]) return;
            cart[id].qty += delta;
            if (cart[id].qty <= 0) delete cart[id];
            else if (cart[id].qty > cart[id].stock) cart[id].qty = cart[id].stock;
            renderCart();
            renderGrid(document.getElementById('search').value);
        }

        // Permite escribir la cantidad exacta (peso en kg para verdulería)
        function setQty(id, value) {
            if (!cart[id]) return;
            const v = parseFloat(value);
            if (isNaN(v) || v <= 0) { delete cart[id]; }
            else if (v > cart[id].stock) { cart[id].qty = cart[id].stock; }
            else { cart[id].qty = v; }
            renderCart();
            renderGrid(document.getElementById('search').value);
        }

        // Botones de peso rápido para productos en KG
        function quickWeight(id, kg) {
            if (!cart[id]) return;
            cart[id].qty = kg;
            renderCart();
            renderGrid(document.getElementById('search').value);
        }

        function renderCart() {
            const el = document.getElementById('cart');
            const ids = Object.keys(cart);
            document.getElementById('emptyCart')?.remove();
            el.innerHTML = '';
            let total = 0;

            if (ids.length === 0) {
                el.innerHTML = '<p id="emptyCart" class="text-center text-sm text-slate-400 py-10">Agrega productos al carrito</p>';
            }

            ids.forEach(id => {
                const it = cart[id];
                const sub = it.price * it.qty;
                total += sub;
                const esKG = (it.unit === 'KG' || it.unit === 'KGM');
                const row = document.createElement('div');
                row.className = 'flex items-center gap-2 rounded-lg bg-slate-50 p-2';
                row.innerHTML = `
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">${it.name}</p>
                        <p class="text-xs text-slate-400">${money(it.price)} ${esKG ? '/kg' : 'c/u'}</p>
                    </div>
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="changeQty(${id},-0.5)" class="w-6 h-6 rounded bg-white border border-slate-200 text-slate-600">−</button>
                        <input type="number" step="0.001" min="0.001" value="${it.qty}" onchange="setQty(${id}, this.value)"
                            class="w-16 text-center text-sm font-semibold border border-slate-200 rounded px-1 py-0.5 outline-none focus:border-brand-500">
                        <button type="button" onclick="changeQty(${id},0.5)" class="w-6 h-6 rounded bg-white border border-slate-200 text-slate-600">+</button>
                    </div>
                    ${esKG ? `
                    <div class="flex gap-1">
                        <button type="button" onclick="quickWeight(${id},0.5)" class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold">½kg</button>
                        <button type="button" onclick="quickWeight(${id},1)" class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold">1kg</button>
                        <button type="button" onclick="quickWeight(${id},2)" class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold">2kg</button>
                    </div>` : ''}
                    <span class="w-16 text-right text-sm font-semibold text-slate-800">${money(sub)}</span>`;
                el.appendChild(row);
            });

            const tax = taxPercent > 0 ? total - (total / (1 + taxPercent / 100)) : 0;
            document.getElementById('taxLabel').textContent = money(tax);
            document.getElementById('totalLabel').textContent = money(total);
            document.getElementById('payBtn').disabled = ids.length === 0;

            const hidden = document.getElementById('hiddenInputs');
            hidden.innerHTML = '';
            ids.forEach((id, i) => {
                hidden.innerHTML += `<input type="hidden" name="items[${i}][product_id]" value="${id}">
                                     <input type="hidden" name="items[${i}][quantity]" value="${cart[id].qty}">`;
            });
        }

        document.getElementById('search').addEventListener('input', (e) => renderGrid(e.target.value));
        renderGrid();
        renderCart();
    </script>
@endsection
