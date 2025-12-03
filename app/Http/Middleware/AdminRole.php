<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si l'utilisateur est connecté et possède le rôle admin
        if ($request->user() && $request->user()->role === 'admin') {
            return $next($request);
        }

        // Retourne une erreur 403 si l'utilisateur n'est pas admin
        return response()->json(['message' => 'Unauthorized access'], 403);
    }
}
