<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     */
    public function __construct(protected Request $request)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;
        $ip = $this->request->ip();
        $userAgent = $this->request->header('User-Agent');

        Log::info('Login bem-sucedido detectado', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'ip' => $ip,
            'device_browser' => $userAgent,
            'timestamp' => now()->toDateTimeString(),
        ]);

        activity('auth')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'ip' => $ip,
                'user_agent' => $userAgent
            ])
            ->log('Usuário realizou login com sucesso');
    }
}
