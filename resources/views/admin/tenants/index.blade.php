@extends('layouts.admin')

@section('title', 'Empresas')

@section('content')
    <div class="mb-5">
        <h2 class="text-xl font-extrabold text-slate-800">Empresas</h2>
        <p class="text-sm text-slate-500">Administra las empresas registradas en la plataforma</p>
    </div>

    <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
        <form method="GET" class="p-4 border-b border-slate-100 flex flex-wrap gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar empresa..."
                class="flex-1 min-w-48 rounded-lg border border-slate-200 px-4 py-2 text-sm outline-none focus:border-indigo-500">
            <select name="status" class="rounded-lg border border-slate-200 px-4 py-2 text-sm outline-none focus:border-indigo-500">
                <option value="">Todos los estados</option>
                @foreach (['trial' => 'En prueba', 'active' => 'Activa', 'suspended' => 'Suspendida'] as $v => $l)
                    <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filtrar</button>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Empresa</th>
                        <th class="px-5 py-3 font-semibold">Plan</th>
                        <th class="px-5 py-3 font-semibold text-center">Usuarios</th>
                        <th class="px-5 py-3 font-semibold">Vence</th>
                        <th class="px-5 py-3 font-semibold text-center">Estado</th>
                        <th class="px-5 py-3 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tenants as $t)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-white text-sm font-bold">{{ strtoupper(substr($t->name, 0, 1)) }}</span>
                                    <div>
                                        <p class="font-medium text-slate-800">{{ $t->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $t->contact_email ?: ($t->ruc ? 'RUC '.$t->ruc : '—') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $t->plan?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-center text-slate-600">{{ $t->users_count }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ optional($t->endsAt())->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-5 py-3 text-center">
                                @php $exp = $t->isExpired(); @endphp
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $exp ? 'bg-red-50 text-red-600' : ($t->status === 'trial' ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700') }}">{{ $t->statusLabel() }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.tenants.edit', $t) }}" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-indigo-600 border border-indigo-200 hover:bg-indigo-50">Gestionar</a>
                                    <form method="POST" action="{{ route('admin.tenants.destroy', $t) }}" onsubmit="return confirm('¿Eliminar esta empresa y TODOS sus datos? Esta acción no se puede deshacer.')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-600" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">No hay empresas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $tenants->links() }}</div>
    </div>
@endsection
