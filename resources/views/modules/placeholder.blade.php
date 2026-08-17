@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="flex flex-col items-center justify-center text-center py-20">
        <div class="flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-brand-50 to-emerald-100 mb-6">
            <svg class="w-10 h-10 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z"/>
            </svg>
        </div>
        <h2 class="text-2xl font-extrabold text-slate-800 mb-2">{{ $title }}</h2>
        <p class="text-slate-500 max-w-md mb-6">
            {{ $description ?: 'Este módulo está listo en el menú y la base de datos. La interfaz completa (CRUD) se construirá en la siguiente iteración.' }}
        </p>
        <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 border border-amber-200 px-4 py-1.5 text-sm font-medium text-amber-700">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span> Módulo en construcción
        </span>
        <a href="{{ route('dashboard') }}" class="mt-6 text-sm font-semibold text-brand-600 hover:text-brand-700">← Volver al Dashboard</a>
    </div>
@endsection
