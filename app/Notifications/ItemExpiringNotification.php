<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Subscription;

class ItemExpiringNotification extends Notification
{
    public $subscriptionName;
    public $daysBefore;
    public $amount;
    public $subscriptionId;
    public $serviceUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $subscriptionName, int $daysBefore, float $amount, string $subscriptionId = null, string $serviceUrl = null)
    {
        $this->subscriptionName = $subscriptionName;
        $this->daysBefore = $daysBefore;
        $this->amount = $amount;
        $this->subscriptionId = $subscriptionId;
        $this->serviceUrl = $serviceUrl;
    }

    public function via(object $notifiable): array
    {
        // Alertas apenas no sistema (In-App), sem e-mail.
        return ['database'];
    }

    /**
     * Get the array representation of the notification for the database.
     */
    public function toDatabase(object $notifiable): array
    {
        $formattedAmount = number_format($this->amount, 2, ',', '.');
        $message = $this->daysBefore === 0 
            ? "Aviso: Sua assinatura {$this->subscriptionName} vence HOJE (R$ {$formattedAmount})." 
            : "Lembrete: Sua assinatura {$this->subscriptionName} vence em {$this->daysBefore} dia(s) (R$ {$formattedAmount}).";

        return [
            'type' => 'expiration_alert',
            'message' => $message,
            'subscription_name' => $this->subscriptionName,
            'subscription_id' => $this->subscriptionId,
            'service_url' => $this->serviceUrl,
            'days_before' => $this->daysBefore,
        ];
    }
}
