<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Models\Subscription;
use App\Services\CacheService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_031_cache_do_dashboard_usa_privacy_token_como_chave(): void
    {
        $this->markTestSkipped('Aguardando implementação dos CRUDs de itens.');
        $user = User::factory()->create();

        // Create a dummy subscription to populate total
        Subscription::create([
            'id' => Str::uuid()->toString(),
            'privacy_token' => $user->privacyToken->token,
            'name' => 'Test Service Monthly',
            'billing_cycle' => 'monthly',
            'amount' => 50.00,
            'start_date' => now(),
            'status' => 'active',
        ]);

        $reportService = app(\App\Services\ReportService::class);
        $total = $reportService->monthlyTotal($user->privacyToken->token);

        $this->assertEquals(50.00, $total);
        
        $key = "user_{$user->privacyToken->token}_dashboard_monthly_total";
        $this->assertTrue(Cache::has($key));
        
        // Ensure standard user_id is definitely NOT the key
        $this->assertFalse(Cache::has("user_{$user->id}_dashboard_monthly_total"));
    }

    public function test_032_cache_do_dashboard_e_invalidado_pelo_servico(): void
    {
        $this->markTestSkipped('Aguardando implementação dos CRUDs de itens.');
        $user = User::factory()->create();
        $token = $user->privacyToken->token;

        $cache = app(CacheService::class);
        $subService = app(SubscriptionService::class);

        // Populate fake cache
        $cacheKey = "user_{$token}_dashboard_monthly_total";
        Cache::put($cacheKey, 100);

        // Se we call invalidate manually (simulated observer hook will do this)
        $cache->invalidateUserCache($token);

        $this->assertFalse(Cache::has($cacheKey));
    }
}
