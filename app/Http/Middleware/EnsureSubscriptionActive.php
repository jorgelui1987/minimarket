<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Auth::user()?->tenant;

        if ($tenant && $tenant->isExpired()) {
            return redirect()->route('suscripcion');
        }

        return $next($request);
    }
}
