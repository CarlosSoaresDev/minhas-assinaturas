<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Subscription;
use App\Services\ReportService;
use App\Services\CacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class T23_ServicoRelatorioCacheTeste extends TestCase
{
    use RefreshDatabase;

    public function test_127_total_mensal_armazenado_em_cache_e_invalidado_na_alteracao()
    {
        $user = User::factory()->create();
        $token = $user->privacyToken?->token;

        Subscription::create([
            'privacy_token' => $token,
            'name' => 'A',
            'billing_cycle' => 'monthly',
            'amount' => 50.0,
            'currency' => 'BRL',
            'start_date' => now(),
            'status' => 'active',
        ]);

        $report = app(ReportService::class);
        $totals1 = $report->monthlyTotal($token);
        $this->assertSame(['BRL' => 50.0], $totals1);

        // cache key v1 exists
        $this->assertTrue(Cache::has("user_{$token}_v1_monthly_totals_v2"));

        // Add another subscription and invalidate cache
        Subscription::create([
            'privacy_token' => $token,
            'name' => 'B',
            'billing_cycle' => 'monthly',
            'amount' => 25.0,
            'currency' => 'BRL',
            'start_date' => now(),
            'status' => 'active',
        ]);

        app(CacheService::class)->invalidateUserCache($token);

        $totals2 = $report->monthlyTotal($token);
        $this->assertSame(['BRL' => 75.0], $totals2);
        $this->assertTrue(Cache::has("user_{$token}_v2_monthly_totals_v2"));
    }
}
