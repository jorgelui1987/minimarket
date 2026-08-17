@extends('layouts.app')

@section('title', 'Mi Suscripción')

@php
    $plan = $tenant->plan;
    $exp = $tenant->isExpired();
    $days = $tenant->daysLeft();
    $bar = fn ($used, $max) => $max <= 0 ? 8 : min(100, round($used / max($max, 1) * 100));
@endphp

@section('content')
    <div class="max-w-4xl">
        <h2 class="text-xl font-extrabold text-slate-800 mb-1">Mi Suscripción</h2>
        <p class="text-sm text-slate-500 mb-5">{{ $tenant->name }}</p>

        <div class="rounded-2xl p-6 mb-6 text-white shadow-lg {{ $exp ? 'bg-gradient-to-br from-red-400 to-rose-600' : 'bg-gradient-to-br from-brand-500 to-emerald-600' }}">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-white/80">Plan actual</p>
                    <p class="text-3xl font-extrabold">{{ $plan?->name ?? 'Sin plan' }}</p>
                    <p class="text-sm text-white/80 mt-1">Estado: {{ $tenant->statusLabel() }}</p>
                </div>
                <div class="text-right">
                    @if ($exp)
                        <p class="text-2xl font-extrabold">Vencida</p>
                        <p class="text-sm text-white/80">Contacta a la plataforma para reactivar</p>
                    @elseif (! is_null($days))
                        <p class="text-2xl font-extrabold">{{ $days }} días</p>
                        <p class="text-sm text-white/80">{{ $tenant->status === 'trial' ? 'restantes de prueba' : 'hasta la renovación' }}</p>
                    @endif
                </div>
            </div>
        </div>

        @if ($exp)
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                Tu acceso a los módulos está restringido hasta renovar. Escríbenos para reactivar tu cuenta.
            </div>
        @endif

        {{-- Uso del plan --}}
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 mb-6">
            <h3 class="font-bold text-slate-800 mb-4">Uso de tu plan</h3>
            <div class="space-y-4">
                @foreach ([
                    ['Productos', $usage['products'], $plan?->max_products ?? -1],
                    ['Usuarios', $usage['users'], $plan?->max_users ?? -1],
                    ['Ventas este mes', $usage['sales_month'], $plan?->max_monthly_sales ?? -1],
                ] as [$label, $used, $max])
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-slate-600">{{ $label }}</span>
                            <span class="font-semibold text-slate-800">{{ $used }} / {{ $max < 0 ? '∞' : $max }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full bg-brand-500" style="width: {{ $bar($used, $max) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Planes disponibles --}}
        <h3 class="font-bold text-slate-800 mb-3">Planes disponibles</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach ($plans as $p)
                <div class="rounded-2xl bg-white border shadow-sm p-6 {{ $plan && $plan->id === $p->id ? 'border-brand-400 ring-2 ring-brand-100' : 'border-slate-100' }}">
                    <h4 class="font-extrabold text-slate-800 text-lg">{{ $p->name }}</h4>
                    <p class="mt-1 text-2xl font-extrabold text-brand-600">S/ {{ number_format($p->price, 2) }}<span class="text-sm font-medium text-slate-400">/mes</span></p>
                    <ul class="mt-3 space-y-1.5 text-sm text-slate-600">
                        <li>Productos: <strong>{{ $p->limitLabel($p->max_products) }}</strong></li>
                        <li>Usuarios: <strong>{{ $p->limitLabel($p->max_users) }}</strong></li>
                        <li>Ventas/mes: <strong>{{ $p->limitLabel($p->max_monthly_sales) }}</strong></li>
                    </ul>
                    @if ($plan && $plan->id === $p->id)
                        <p class="mt-4 text-center text-sm font-semibold text-brand-600">Tu plan actual</p>
                    @endif
                </div>
            @endforeach
        </div>
        <p class="text-xs text-slate-400 mt-4">Para cambiar de plan o renovar tu suscripción, contacta al administrador de la plataforma.</p>
    </div>
@endsection
