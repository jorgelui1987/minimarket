<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::with('plan')->withCount('users')->get();

        $total = $tenants->count();
        $activas = $tenants->where('status', 'active')->where('is_active', true)->filter(fn ($t) => ! $t->isExpired())->count();
        $enPrueba = $tenants->where('status', 'trial')->filter(fn ($t) => ! $t->isExpired())->count();
        $vencidas = $tenants->filter(fn ($t) => $t->isExpired())->count();

        $totalUsuarios = User::where('is_super_admin', false)->count();

        // MRR estimado: suma del precio del plan de las empresas activas
        $activasColl = $tenants->where('status', 'active')->where('is_active', true)
            ->filter(fn ($t) => ! $t->isExpired());
        $mrr = $activasColl->sum(fn ($t) => (float) ($t->plan->price ?? 0));

        $recientes = $tenants->sortByDesc('created_at')->take(8);
        $planes = Plan::withCount('tenants')->orderBy('price')->get();

        // ===== Datos para gráficos =====

        // 1) Empresas por plan (dona)
        $chartPlanLabels = $planes->pluck('name')->values();
        $chartPlanData = $planes->pluck('tenants_count')->values();

        // 2) Crecimiento de empresas: altas por mes (últimos 6 meses)
        $growthLabels = collect();
        $growthData = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $growthLabels->push($month->locale('es')->isoFormat('MMM YY'));
            $growthData->push(
                $tenants->filter(fn ($t) => $t->created_at
                    && $t->created_at->year === $month->year
                    && $t->created_at->month === $month->month)->count()
            );
        }

        // 3) MRR por plan: precio del plan × empresas activas en ese plan (barras)
        $mrrPorPlan = $planes->map(function ($p) use ($activasColl) {
            $count = $activasColl->where('plan_id', $p->id)->count();

            return round((float) $p->price * $count, 2);
        })->values();

        // 4) Estado de suscripciones (dona)
        $estadoData = collect([$activas, $enPrueba, $vencidas]);

        return view('admin.dashboard', compact(
            'total', 'activas', 'enPrueba', 'vencidas', 'totalUsuarios', 'mrr', 'recientes', 'planes',
            'chartPlanLabels', 'chartPlanData', 'growthLabels', 'growthData', 'mrrPorPlan', 'estadoData'
        ));
    }
}
