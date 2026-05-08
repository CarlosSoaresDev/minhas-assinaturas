<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Subscription;
use App\Services\CacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class T27_PainelUsuarioTeste extends TestCase
{
    use RefreshDatabase;

    public function test_114_cache_do_dashboard_usa_privacy_token_como_chave(): void
    {
        $user = User::factory()->create();

        // Create a dummy subscription to populate total
        Subscription::create([
            'privacy_token' => $user->privacyToken->token,
            'name' => 'Test Service Monthly',
            'billing_cycle' => 'monthly',
            'amount' => 50.00,
            'currency' => 'BRL',
            'start_date' => now(),
            'status' => 'active',
        ]);

        $reportService = app(\App\Services\ReportService::class);
        $total = $reportService->monthlyTotal($user->privacyToken->token);

        $this->assertSame(['BRL' => 50.0], $total);

        $key = "user_{$user->privacyToken->token}_v1_monthly_totals_v2";
        $this->assertTrue(Cache::has($key));

        // Ensure standard user_id is definitely NOT the cache key
        $this->assertFalse(Cache::has("user_{$user->id}_v1_monthly_totals_v2"));
    }

    public function test_115_cache_do_dashboard_e_invalidado_pelo_servico(): void
    {
        $user = User::factory()->create();
        $token = $user->privacyToken->token;

        $cache = app(CacheService::class);

        Subscription::create([
            'privacy_token' => $token,
            'name' => 'Cached Service',
            'billing_cycle' => 'monthly',
            'amount' => 100.00,
            'currency' => 'BRL',
            'start_date' => now(),
            'status' => 'active',
        ]);

        // Primeira leitura cria a cache versionada em v1
        $reportService = app(\App\Services\ReportService::class);
        $this->assertSame(['BRL' => 100.0], $reportService->monthlyTotal($token));

        $cacheKeyV1 = "user_{$token}_v1_monthly_totals_v2";
        $this->assertTrue(Cache::has($cacheKeyV1));

        $cache->invalidateUserCache($token);

        $this->assertTrue(Cache::has("user_{$token}_cache_version"));
        $this->assertSame(2, Cache::get("user_{$token}_cache_version"));

        // Nova leitura usa v2 sem apagar a v1 antiga
        $this->assertSame(['BRL' => 100.0], $reportService->monthlyTotal($token));
        $this->assertTrue(Cache::has("user_{$token}_v2_monthly_totals_v2"));
    }
}
