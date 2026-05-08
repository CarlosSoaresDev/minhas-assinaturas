<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AlertService
{
    /**
     * Get subscriptions that are expiring within the specified number of days.
     */
    public function getExpiringSubscriptions(int $daysAhead = 7): Collection
    {
        $targetDate = now()->addDays($daysAhead)->startOfDay();

        return Subscription::with(['category'])
            ->where('status', 'active')
            ->whereNotNull('next_billing_date')
            ->whereDate('next_billing_date', '<=', $targetDate)
            ->whereDate('next_billing_date', '>=', now()->startOfDay())
            ->get();
    }

    /**
     * Mark an alert as sent for a specific subscription and timeframe to prevent duplicates.
     */
    public function markAlertSent(Subscription $subscription, int $daysBefore): void
    {
        // This relies on the 'subscription_alerts' table
        // Usamos updateOrInsert para evitar erros de duplicidade se o admin disparar manualmente mais de uma vez
        \DB::table('subscription_alerts')->updateOrInsert(
            [
                'subscription_id' => $subscription->id,
                'type' => 'expiration_warning',
                'days_before' => $daysBefore,
            ],
            [
                'scheduled_at' => now(),
                'sent_at' => now(),
                'status' => 'sent',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    /**
     * Check if an alert was already sent for this subscription for this specific timeframe.
     */
    public function wasAlertAlreadySent(Subscription $subscription, int $daysBefore): bool
    {
        return \DB::table('subscription_alerts')
            ->where('subscription_id', $subscription->id)
            ->where('days_before', $daysBefore)
            ->where('type', 'expiration_warning')
            // Only check alerts sent recently for this billing cycle
            ->where('sent_at', '>=', now()->subDays(15))
            ->exists();
    }

    /**
     * Get the User instance associated with a privacy token.
     */
    public function getUserByPrivacyToken(string $token): ?User
    {
        return User::whereHas('privacyToken', function ($query) use ($token) {
            $query->where('token', $token);
        })->first();
    }
}
