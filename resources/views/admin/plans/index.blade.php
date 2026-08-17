@extends('layouts.admin')

@section('title', 'Planes')

@section('content')
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-xl font-extrabold text-slate-800">Planes</h2>
            <p class="text-sm text-slate-500">Define los planes y sus límites de uso</p>
        </div>
        <a href="{{ route('admin.plans.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nuevo Plan
        </a>
    </div>

    @php $masPopular = $plans->sortByDesc('tenants_count')->first(); @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($plans as $plan)
            @php $destacado = $masPopular && $plan->id === $masPopular->id && $plan->tenants_count > 0; @endphp
            <div class="relative rounded-2xl bg-white border shadow-sm flex flex-col overflow-hidden {{ $destacado ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-slate-100' }}">
                <div class="h-1.5 bg-gradient-to-r {{ $destacado ? 'from-indigo-500 to-violet-600' : 'from-slate-200 to-slate-300' }}"></div>
                <div class="p-6 flex flex-col flex-1">
                    <div class="flex items-center justify-between">
                        <h3 class="font-extrabold text-slate-800 text-lg">{{ $plan->name }}</h3>
                        @if ($destacado)
                            <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">Más popular</span>
                        @elseif (! $plan->is_active)
                            <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">Inactivo</span>
                        @endif
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-indigo-600">S/ {{ number_format($plan->price, 2) }}<span class="text-sm font-medium text-slate-400">/mes</span></p>

                    <ul class="mt-5 space-y-2.5 text-sm text-slate-600 flex-1">
                        @php
                            $features = [
                                ['Productos', $plan->limitLabel($plan->max_products)],
                                ['Usuarios', $plan->limitLabel($plan->max_users)],
                                ['Ventas / mes', $plan->limitLabel($plan->max_monthly_sales)],
                            ];
                        @endphp
                        @foreach ($features as [$label, $value])
                            <li class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                {{ $label }}: <strong class="text-slate-800">{{ $value }}</strong>
                            </li>
                        @endforeach
                        <li class="flex items-center gap-2 pt-1 border-t border-slate-100 mt-3">
                            <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                            Empresas activas: <strong class="text-slate-800">{{ $plan->tenants_count }}</strong>
                        </li>
                    </ul>

                    <div class="mt-6 flex gap-2">
                        <a href="{{ route('admin.plans.edit', $plan) }}" class="flex-1 text-center rounded-lg {{ $destacado ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'border border-indigo-200 text-indigo-600 hover:bg-indigo-50' }} px-3 py-2 text-sm font-semibold">Editar</a>
                        <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" onsubmit="return confirm('¿Eliminar este plan?')">
                            @csrf @method('DELETE')
                            <button class="rounded-lg border border-slate-200 p-2 text-slate-400 hover:bg-red-50 hover:text-red-600" title="Eliminar">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
