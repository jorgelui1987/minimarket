@extends('layouts.app')

@section('title', 'Backup')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-xl font-extrabold text-slate-800">Backup</h2>
            <p class="text-sm text-slate-500">Copias de seguridad de la base de datos</p>
        </div>
        <form method="POST" action="{{ route('backup.store') }}">
            @csrf
            <button class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:from-brand-600 hover:to-emerald-700">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Generar Backup
            </button>
        </form>
    </div>

    <div class="mb-5 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
        <span><strong>Importante:</strong> restaurar un backup reemplaza TODOS los datos actuales por los del archivo seleccionado. Esta acción no se puede deshacer; genera un backup actual antes de restaurar.</span>
    </div>

    <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Archivo</th>
                        <th class="px-5 py-3 font-semibold">Fecha</th>
                        <th class="px-5 py-3 font-semibold text-right">Tamaño</th>
                        <th class="px-5 py-3 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($files as $f)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $f['name'] }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ \Carbon\Carbon::createFromTimestamp($f['date'])->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3 text-right text-slate-500">{{ number_format($f['size'] / 1024, 1) }} KB</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('backup.download', $f['name']) }}" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-600 border border-slate-200 hover:bg-slate-50">Descargar</a>
                                    <form method="POST" action="{{ route('backup.restore', $f['name']) }}" onsubmit="return confirm('¿Restaurar este backup? Se reemplazarán todos los datos actuales.')">
                                        @csrf
                                        <button class="rounded-lg px-3 py-1.5 text-xs font-semibold text-amber-700 border border-amber-200 hover:bg-amber-50">Restaurar</button>
                                    </form>
                                    <form method="POST" action="{{ route('backup.destroy', $f['name']) }}" onsubmit="return confirm('¿Eliminar este archivo de backup?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-600" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">Aún no hay backups. Genera el primero con el botón de arriba.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
