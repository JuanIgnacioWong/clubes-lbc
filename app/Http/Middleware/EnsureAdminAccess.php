<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active || ! $user->isAdmin()) {
            auth()->logout();

            return redirect()->route('login')->with('status', 'Acceso restringido.');
        }

        return $next($request);
    }
}
