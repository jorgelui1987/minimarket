@extends('layouts.app')

@section('title', 'Logs del DTE #' . $dte->id)

@section('content')
    <div class="max-w-5xl mx-auto">

        <div class="flex items-center gap-3 mb-5">
            <a href="{{ route('facturacionchile.ver', $dte->id) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                Volver al DTE
            </a>
            <h1 class="text-2xl font-extrabold text-slate-800">Bitácora completa — DTE #{{ $dte->id }}</h1>
            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $dte->estadoColor() }}">{{ $dte->estadoLabel() }}</span>
        </div>

        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/60">
                <div>
                    <p class="font-bold text-slate-800">Eventos de auditoría</p>
                    <p class="text-xs text-slate-500">{{ $dte->series }}-{{ $dte->number }} · {{ $dte->document_type }}</p>
                </div>
                <span class="text-xs text-slate-400">{{ $logs->total() }} eventos</span>
            </div>

            <div class="divide-y divide-slate-50">
                @forelse ($logs as $log)
                    <div class="px-5 py-4 flex items-start gap-3">
                        <span class="mt-1.5 w-2.5 h-2.5 rounded-full shrink-0 {{ $log->event === 'error' ? 'bg-rose-500' : ($log->event === 'transmitted' ? 'bg-sky-500' : 'bg-emerald-500') }}"></span>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-bold text-slate-700">{{ $log->event }}</p>
                                <span class="text-xs text-slate-400">{{ $log->actor ?? 'sistema' }}</span>
                            </div>
                            @if ($log->context)
                                <pre class="text-xs text-slate-500 mt-1.5 bg-slate-50 rounded-lg p-3 ring-1 ring-slate-100 whitespace-pre-wrap overflow-x-auto">{{ json_encode($log->context, JSON_PRETTY_PRINT) }}</pre>
                            @endif
                        </div>
                        <span class="text-xs text-slate-400 shrink-0">{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                @empty
                    <p class="px-5 py-12 text-center text-sm text-slate-400">Sin eventos registrados para este DTE.</p>
                @endforelse
            </div>

            @if ($logs->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection