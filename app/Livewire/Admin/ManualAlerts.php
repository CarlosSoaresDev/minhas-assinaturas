<?php

namespace App\Livewire\Admin;

use App\Models\Subscription;
use App\Notifications\ItemExpiringNotification;
use App\Services\AlertService;
use Livewire\Component;
use Livewire\WithPagination;

class ManualAlerts extends Component
{
    use WithPagination;

    public $timeframe = 7; // Default 7 days
    public $selectedSubscriptions = [];
    public $hasScanned = false;

    public function scan(AlertService $alertService)
    {
        $this->hasScanned = true;
        $this->resetPage();
        $this->selectedSubscriptions = [];
    }

    public function sendSelected(AlertService $alertService)
    {
        if (empty($this->selectedSubscriptions)) {
            session()->flash('error', 'Selecione ao menos um item para enviar o alerta.');
            return;
        }

        $count = 0;
        foreach ($this->selectedSubscriptions as $subId) {
            $subscription = Subscription::find($subId);
            if ($subscription) {
                // Determine the days until expiration for the notification text
                $daysUntil = now()->startOfDay()->diffInDays($subscription->next_billing_date->startOfDay(), false);
                
                $user = $alertService->getUserByPrivacyToken($subscription->privacy_token);
                if ($user) {
                    $user->notify(new ItemExpiringNotification(
                        $subscription->name,
                        max(0, $daysUntil),
                        $subscription->amount,
                        $subscription->id,
                        $subscription->service_url
                    ));

                    $alertService->markAlertSent($subscription, $this->timeframe);
                    $count++;
                }
            }
        }

        $this->selectedSubscriptions = [];
        $this->dispatch('refresh-notifications');
        session()->flash('success', "Sucesso! {$count} notificações foram disparadas.");
        
        activity()
            ->event('manual_alert')
            ->log("Disparo manual de alertas realizado pelo administrador para {$count} itens.");
    }

    public function render(AlertService $alertService)
    {
        $subscriptions = [];
        if ($this->hasScanned) {
            // Força conversão para int para evitar erro no Carbon (Argument #3 must be int|float)
            $days = (int) $this->timeframe;
            $targetDate = now()->addDays($days)->startOfDay();

            $subscriptions = Subscription::with(['category'])
                ->where('status', 'active')
                ->whereNotNull('next_billing_date')
                ->whereDate('next_billing_date', '<=', $targetDate)
                ->whereDate('next_billing_date', '>=', now()->startOfDay())
                ->orderBy('next_billing_date', 'asc')
                ->paginate(10);
        }

        return view('livewire.admin.manual-alerts', [
            'subscriptions' => $subscriptions
        ]);
    }
}
