<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminHasTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('administrateur') && is_null($user->two_factor_confirmed_at)) {

            // Évite la boucle infinie si on est déjà sur la page de config
            if (! $request->routeIs('two-factor.setup')) {
                return redirect()->route('two-factor.setup')
                    ->with('warning', 'La double authentification est obligatoire pour les administrateurs.');
            }
        }

        return $next($request);
    }
}
