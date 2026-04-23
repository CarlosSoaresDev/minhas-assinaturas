<?php

namespace App\Services;

use App\Models\Subscription;
use Illuminate\Support\Str;

class SubscriptionImportService
{
    /**
     * Check if a subscription already exists for a user (by privacy token).
     * Logic: Match by name (fuzzy) and amount or billing day.
     */
    public function isDuplicate(string $privacyToken, string $name, float $amount, $nextBillingDate): bool
    {
        $normalizedName = Str::slug($name);

        return Subscription::where('privacy_token', $privacyToken)
            ->get()
            ->contains(function ($sub) use ($normalizedName, $amount, $nextBillingDate) {
                $subNameSlug = Str::slug($sub->name);
                
                // Match exact name and amount
                if ($subNameSlug === $normalizedName && abs($sub->amount - $amount) < 0.01) {
                    return true;
                }

                // Match name and same billing day (useful for monthly services)
                if ($subNameSlug === $normalizedName && 
                    $sub->next_billing_date->day === \Carbon\Carbon::parse($nextBillingDate)->day) {
                    return true;
                }

                return false;
            });
    }

    /**
     * Process an imported record, checking for duplicates.
     */
    public function importRecord(string $privacyToken, array $data)
    {
        if ($this->isDuplicate($privacyToken, $data['name'], $data['amount'], $data['next_billing_date'])) {
            return [
                'status' => 'duplicate',
                'name' => $data['name']
            ];
        }

        $subscription = Subscription::create(array_merge($data, [
            'privacy_token' => $privacyToken,
            'status' => 'active',
        ]));

        return [
            'status' => 'imported',
            'id' => $subscription->id,
            'name' => $data['name']
        ];
    }
}
