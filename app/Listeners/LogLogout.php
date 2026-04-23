<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;

class LogLogout
{
    public function __construct(protected Request $request) {}

    public function handle(Logout $event): void
    {
        if (!$event->user) return;

        $ip = $this->request->ip();
        $userAgent = $this->request->header('User-Agent');

        activity('acesso')
            ->performedOn($event->user)
            ->causedBy($event->user)
            ->withProperties([
                'ip' => $ip,
                'user_agent' => $userAgent,
                'status' => 'logout'
            ])
            ->log("Logout efetuado pelo usuário: {$event->user->email}");
    }
}
