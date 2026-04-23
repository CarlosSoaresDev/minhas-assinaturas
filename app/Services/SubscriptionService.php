<?php

namespace App\Services;

use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class SubscriptionService
{
    /**
     * Calcula a próxima data de cobrança baseado na data inicial e no ciclo.
     */
    public function calculateNextBillingDate(string $cycle, Carbon $startDate): Carbon
    {
        $nextDate = $startDate->copy();
        $now = Carbon::now();

        // Se a data de início estiver no futuro, a próxima é ela própria
        if ($nextDate->greaterThan($now)) {
            return $nextDate;
        }

        // Avançar até que nextDate seja maior que agora
        while ($nextDate->lessThanOrEqualTo($now)) {
            match ($cycle) {
                'monthly' => $nextDate->addMonth(),
                'yearly' => $nextDate->addYear(),
                'weekly' => $nextDate->addWeek(),
                'quarterly' => $nextDate->addMonths(3),
                default => $nextDate->addMonth(), // Fallback seguro
            };
        }

        return $nextDate;
    }

    /**
     * Cria uma assinatura e calcula o faturamento programaticamente.
     */
    public function create(array $data, string $privacyToken): Subscription
    {
        if (isset($data['start_date']) && isset($data['billing_cycle']) && empty($data['next_billing_date'])) {
            $data['next_billing_date'] = $this->calculateNextBillingDate(
                $data['billing_cycle'],
                Carbon::parse($data['start_date'])
            );
        }

        $data['privacy_token'] = $privacyToken;

        return Subscription::create($data);
    }
}
