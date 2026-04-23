<?php

namespace Tests\Unit\Services;

use App\Models\Subscription;
use App\Models\User;
use App\Services\AlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AlertServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_056_alerta_detecta_assinaturas_vencendo_no_prazo(): void
    {
        $this->markTestSkipped('Fora de escopo atual: depende da fase de CRUD de assinaturas consolidada.');

        $user = User::factory()->create();
        $token = $user->privacyToken->token;

        $subExpiringIn7Days = Subscription::create([
            'id' => Str::uuid()->toString(),
            'privacy_token' => $token,
            'name' => 'Netflix',
            'amount' => 50,
            'billing_cycle' => 'monthly',
            'start_date' => now()->subMonth(),
            'next_billing_date' => now()->addDays(7)->startOfDay(),
            'status' => 'active',
        ]);

        Subscription::create([
            'id' => Str::uuid()->toString(),
            'privacy_token' => $token,
            'name' => 'Spotify',
            'amount' => 20,
            'billing_cycle' => 'monthly',
            'start_date' => now()->subMonth(),
            'next_billing_date' => now()->addDays(15)->startOfDay(),
            'status' => 'active',
        ]);

        $service = new AlertService();
        $expiring = $service->getExpiringSubscriptions(7);

        // Only the 7 days one should be matched
        $this->assertCount(1, $expiring);
        $this->assertEquals($subExpiringIn7Days->id, $expiring->first()->id);
    }
}
