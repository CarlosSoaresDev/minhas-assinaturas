<?php

namespace App\Livewire\Notifications;

use Livewire\Component;

class Bell extends Component
{
    public $unreadCount = 0;
    public $unreadNotifications = [];
    public $readNotifications = [];

    public function getListeners()
    {
        return [
            'echo-private:App.Models.User.' . auth()->id() . ',.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated' => 'loadNotifications',
            'refresh-notifications' => 'loadNotifications',
        ];
    }

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = auth()->user();
        if ($user) {
            // Limpeza automática de notificações lidas há mais de 15 dias (Manutenção Silenciosa)
            $user->notifications()
                ->whereNotNull('read_at')
                ->where('read_at', '<', now()->subDays(15))
                ->delete();

            $this->unreadCount = $user->unreadNotifications()->count();
            $this->unreadNotifications = $user->unreadNotifications()->take(5)->get();
            
            // Carrega as últimas 5 notificações lidas (que ainda não foram deletadas pela regra dos 15 dias)
            $this->readNotifications = $user->notifications()
                ->whereNotNull('read_at')
                ->latest('read_at')
                ->take(5)
                ->get();
        }
    }

    public function markAsRead($id)
    {
        $user = auth()->user();
        if ($user) {
            $notification = $user->notifications()->find($id);
            if ($notification) {
                $notification->markAsRead();
                $this->loadNotifications();
            }
        }
    }

    public function readAndRedirect($id)
    {
        $this->markAsRead($id);
        return redirect()->route('front.subscriptions.index');
    }

    public function render()
    {
        return view('livewire.notifications.bell');
    }
}
