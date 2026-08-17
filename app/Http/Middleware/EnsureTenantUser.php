<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if (! $user || ! $user->tenant_id) {
            Auth::logout();

            return redirect()->route('login')->with('error', 'Tu cuenta no está asociada a una empresa.');
        }

        return $next($request);
    }
}
