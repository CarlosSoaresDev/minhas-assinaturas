<?php

namespace Tests\Feature;

use App\Livewire\Admin\ManualAlerts;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\ItemExpiringNotification;
use App\Services\AlertService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class T14_AlertasManuaisTeste extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create([
            'lgpd_consent_at' => now(),
            'alerts_enabled' => true,
            'alert_days_before' => 7,
        ]);
        $this->admin->assignRole('admin');
    }

    private function createSubscriber(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'lgpd_consent_at' => now(),
            'alerts_enabled' => true,
            'alert_days_before' => 7,
        ], $attributes));
    }

    private function createSubscriptionFor(User $user, array $attributes = []): Subscription
    {
        return Subscription::create(array_merge([
            'privacy_token' => $user->privacyToken->token,
            'name' => 'Netflix',
            'billing_cycle' => 'monthly',
            'amount' => 39.90,
            'currency' => 'BRL',
            'start_date' => now()->subMonth(),
            'next_billing_date' => now()->addDays(7)->startOfDay(),
            'status' => 'active',
        ], $attributes));
    }

    public function test_036_varredura_lista_assinaturas_vencendo_e_reseta_selecao(): void
    {
        $subscriber = $this->createSubscriber();
        $due = $this->createSubscriptionFor($subscriber, [
            'name' => 'Netflix',
            'next_billing_date' => now()->addDays(7)->startOfDay(),
        ]);

        $future = $this->createSubscriptionFor($subscriber, [
            'name' => 'Spotify',
            'next_billing_date' => now()->addDays(30)->startOfDay(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(ManualAlerts::class)
            ->set('selectedSubscriptions', [$future->id])
            ->call('scan')
            ->assertSet('hasScanned', true)
            ->assertSet('selectedSubscriptions', []);

        Livewire::actingAs($this->admin)
            ->test(ManualAlerts::class)
            ->set('hasScanned', true)
            ->set('timeframe', 7)
            ->assertSee('Netflix')
            ->assertDontSee('Spotify');

        $this->assertTrue(true); // garante fechamento do fluxo de varredura sem exceção
    }

    public function test_037_envio_selecionado_dispara_notificacao_e_audita(): void
    {
        Notification::fake();

        $subscriber = $this->createSubscriber();
        $subscription = $this->createSubscriptionFor($subscriber, [
            'name' => 'Disney+',
            'next_billing_date' => now()->addDays(3)->startOfDay(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(ManualAlerts::class)
            ->set('timeframe', 3)
            ->set('selectedSubscriptions', [$subscription->id])
            ->call('sendSelected');

        Notification::assertSentTo(
            $subscriber,
            ItemExpiringNotification::class,
            function (ItemExpiringNotification $notification) use ($subscription) {
                return $notification->subscriptionName === 'Disney+'
                    && $notification->daysBefore === 3
                    && $notification->subscriptionId === $subscription->id;
            }
        );

        $this->assertDatabaseHas('subscription_alerts', [
            'subscription_id' => $subscription->id,
            'type' => 'expiration_warning',
            'days_before' => 3,
            'status' => 'sent',
        ]);

        $this->assertTrue(Activity::where('event', 'manual_alert')->exists());
    }

    public function test_038_envio_selecionado_sem_itens_retorna_erro_controlado(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ManualAlerts::class)
            ->call('sendSelected')
            ->assertSet('selectedSubscriptions', []);
    }
}
