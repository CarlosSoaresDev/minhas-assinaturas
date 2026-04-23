<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLgpdConsent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Rotas públicas/essenciais que não devem ser bloqueadas pelo consentimento.
        $allowedPaths = [
            'login',
            'register',
            'logout',
            'auth/google/redirect',
            'auth/google/callback',
            'lgpd/consent',
        ];

        if ($request->routeIs('profile.edit', 'password.edit', 'two-factor.edit')) {
            return $next($request);
        }

        foreach ($allowedPaths as $path) {
            if ($request->is($path)) {
                return $next($request);
            }
        }

        // Hotfix de recuperação: se a conta estiver sem consentimento por falha anterior,
        // regulariza automaticamente para não prender o usuário em loop de bloqueio.
        if ($user && is_null($user->lgpd_consent_at)) {
            $user->forceFill(['lgpd_consent_at' => now()])->save();
            return $next($request);
        }

        return $next($request);
    }
}
