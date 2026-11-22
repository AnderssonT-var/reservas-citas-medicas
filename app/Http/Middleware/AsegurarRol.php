<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AsegurarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->rol->value, $roles, true)) {
            abort(403, 'Esta sección no está disponible para tu perfil.');
        }

        return $next($request);
    }
}
