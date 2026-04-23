<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use App\Models\User;

class LogFailedLogin
{
    public function __construct(protected Request $request) {}

    public function handle(Failed $event): void
    {
        $ip = $this->request->ip();
        $userAgent = $this->request->header('User-Agent');
        $email = $event->credentials['email'] ?? 'desconhecido';
        $user = $event->user;

        activity('seguranca')
            ->performedOn($user ?: new User())
            ->withProperties([
                'ip' => $ip,
                'user_agent' => $userAgent,
                'email' => $email,
                'status' => 'senha_incorreta'
            ])
            ->log("Tentativa de login falhou: Senha incorreta para $email");
    }
}
