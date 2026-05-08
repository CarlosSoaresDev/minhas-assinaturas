<?php

namespace Tests\Feature;

use App\Jobs\SendAlertsJob;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\ItemExpiringNotification;
use App\Services\AlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class T30_EnviarAlertasJobTeste extends TestCase
{
    use RefreshDatabase;

    private function createUserWithSettings(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'alerts_enabled' => true,
            'alert_days_before' => 7,
            'lgpd_consent_at' => now(),
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

    public function test_136_job_envia_alertas_apenas_para_assinaturas_ativas_com_timeframe_do_usuario(): void
    {
        Notification::fake();

        $enabledUser = $this->createUserWithSettings(['alert_days_before' => 7]);
        $enabledSub = $this->createSubscriptionFor($enabledUser, [
            'name' => 'Netflix',
            'next_billing_date' => now()->addDays(7)->startOfDay(),
        ]);

        $disabledUser = $this->createUserWithSettings(['alerts_enabled' => false, 'alert_days_before' => 3]);
        $this->createSubscriptionFor($disabledUser, [
            'name' => 'Spotify',
            'next_billing_date' => now()->addDays(3)->startOfDay(),
        ]);

        $this->createSubscriptionFor($enabledUser, [
            'name' => 'Inactive Plan',
            'status' => 'cancelled',
            'next_billing_date' => now()->addDays(7)->startOfDay(),
        ]);

        app(SendAlertsJob::class)->handle(app(AlertService::class));

        Notification::assertSentTo(
            $enabledUser,
            ItemExpiringNotification::class,
            function (ItemExpiringNotification $notification) use ($enabledSub) {
                return $notification->subscriptionName === 'Netflix'
                    && $notification->daysBefore === 7
                    && $notification->subscriptionId === $enabledSub->id;
            }
        );

        Notification::assertNotSentTo($disabledUser, ItemExpiringNotification::class);

        $this->assertDatabaseHas('subscription_alerts', [
            'subscription_id' => $enabledSub->id,
            'type' => 'expiration_warning',
            'days_before' => 7,
        ]);
    }

    public function test_137_job_nao_redispara_alerta_ja_registrado(): void
    {
        Notification::fake();

        $user = $this->createUserWithSettings(['alert_days_before' => 3]);
        $subscription = $this->createSubscriptionFor($user, [
            'name' => 'Prime Video',
            'next_billing_date' => now()->addDays(3)->startOfDay(),
        ]);

        \DB::table('subscription_alerts')->insert([
            'subscription_id' => $subscription->id,
            'type' => 'expiration_warning',
            'days_before' => 3,
            'scheduled_at' => now(),
            'sent_at' => now(),
            'status' => 'sent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(SendAlertsJob::class)->handle(app(AlertService::class));

        Notification::assertNotSentTo($user, ItemExpiringNotification::class);
        $this->assertSame(1, \DB::table('subscription_alerts')->where('subscription_id', $subscription->id)->count());
    }
}
