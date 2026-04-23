<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifySessionFingerprint
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se for a página inicial (pública), permitimos o acesso para evitar loop no 403
        if ($request->routeIs('home') || $request->is('/')) {
            return $next($request);
        }

        // Só verificamos o "carimbo" se o usuário estiver autenticado no sistema
        if (auth()->check()) {
            $userAgent = $request->header('User-Agent');
            
            // Se a sessão já tem um fingerprint, verifica se bate
            if (Session::has('session_fingerprint')) {
                if (Session::get('session_fingerprint') !== $userAgent) {
                    
                    Log::warning('Tentativa de acesso com navegador divergente', [
                        'ip' => $request->ip(),
                        'expected' => Session::get('session_fingerprint'),
                        'received' => $userAgent
                    ]);

                    activity('security')
                        ->withProperties(['ip' => $request->ip(), 'user_agent' => $userAgent])
                        ->log('Acesso bloqueado por divergência de navegador.');

                    // Bloqueio direto para evitar loops e redirecionamento de 5s via view customizada
                    abort(403, 'Acesso negado.');
                }
            } else {
                Session::put('session_fingerprint', $userAgent);
            }
        }

        return $next($request);
    }
}
