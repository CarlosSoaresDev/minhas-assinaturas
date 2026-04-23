<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;

class LogLockout
{
    public function __construct(protected Request $request) {}

    public function handle(Lockout $event): void
    {
        $ip = $this->request->ip();
        $userAgent = $this->request->header('User-Agent');
        $email = $this->request->input('email') ?? 'desconhecido';

        activity('seguranca')
            ->withProperties([
                'ip' => $ip,
                'user_agent' => $userAgent,
                'email' => $email,
                'status' => 'conta_bloqueada'
            ])
            ->log("CONTA BLOQUEADA: Excesso de tentativas para o e-mail: $email");
    }
}
