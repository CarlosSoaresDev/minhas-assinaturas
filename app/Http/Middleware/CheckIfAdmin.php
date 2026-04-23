<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIfAdmin
{
    /**
     * Verifica se o usuário logado é um administrador.
     */
    private function checkIfUserIsAdmin($user): bool
    {
        return ($user?->hasRole('admin') ?? false) && session('admin_mode', true);
    }

    /**
     * Resposta para acesso não autorizado.
     */
    private function respondToUnauthorizedRequest(Request $request): Response
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        return redirect()->route('dashboard');
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->guest()) {
            return redirect()->route('login');
        }

        if (! $this->checkIfUserIsAdmin(auth()->user())) {
            return $this->respondToUnauthorizedRequest($request);
        }

        return $next($request);
    }
}

