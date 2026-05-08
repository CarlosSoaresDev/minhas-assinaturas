<?php

namespace Tests\Feature;

use App\Livewire\Settings\DataManagement;
use App\Models\Subscription;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class T26_GerenciamentoDadosTeste extends TestCase
{
    use RefreshDatabase;

    private function createUserWithDefaults(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'alerts_enabled' => true,
            'alert_days_before' => 7,
            'lgpd_consent_at' => now(),
        ], $attributes));
    }

    public function test_111_download_de_dados_lgpd_exporta_apenas_escopo_do_usuario_autenticado_e_registra_atividade(): void
    {
        $user = $this->createUserWithDefaults(['name' => 'Alice', 'email' => 'alice@example.com']);
        $otherUser = $this->createUserWithDefaults(['name' => 'Bob', 'email' => 'bob@example.com']);

        Subscription::create([
            'privacy_token' => $user->privacyToken->token,
            'name' => 'Netflix',
            'billing_cycle' => 'monthly',
            'amount' => 39.9,
            'currency' => 'BRL',
            'start_date' => now()->subMonth(),
            'status' => 'active',
        ]);

        Subscription::create([
            'privacy_token' => $otherUser->privacyToken->token,
            'name' => 'Spotify',
            'billing_cycle' => 'monthly',
            'amount' => 19.9,
            'currency' => 'BRL',
            'start_date' => now()->subMonth(),
            'status' => 'active',
        ]);

        $this->actingAs($user);

        $response = app(DataManagement::class)->downloadLgpdData();

        $this->assertInstanceOf(StreamedResponse::class, $response);

        ob_start();
        $response->sendContent();
        $json = (string) ob_get_clean();

        $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('Alice', $payload['personal_info']['name']);
        $this->assertSame('alice@example.com', $payload['personal_info']['email']);
        $this->assertCount(1, $payload['subscriptions']);
        $this->assertSame('Netflix', $payload['subscriptions'][0]['name']);
        $this->assertNotSame('Spotify', $payload['subscriptions'][0]['name']);
        $this->assertTrue(Activity::where('event', 'lgpd_export')->exists());
    }

    public function test_112_download_de_dados_lgpd_sem_token_de_privacidade_para_com_erro(): void
    {
        $user = User::factory()->create([
            'alerts_enabled' => true,
            'alert_days_before' => 7,
            'lgpd_consent_at' => now(),
        ]);
        $user->privacyToken()->delete();

        $this->actingAs($user);

        $response = app(DataManagement::class)->downloadLgpdData();

        $this->assertNull($response);
        $this->assertSame('Token de privacidade não encontrado.', session('error'));
    }

    public function test_113_importacao_csv_cria_assinaturas_com_escopo_de_token_e_invalida_cache(): void
    {
        $user = $this->createUserWithDefaults();
        $token = $user->privacyToken->token;

        app(CacheService::class)->getUserCache($token, 'monthly_totals_v2', fn () => ['BRL' => 0.0], 60);

        $csvContent = "Nome,Valor,Ciclo\n";
        $csvContent .= "Plano A,19,90,monthly\n";
        $csvContent .= "Plano B,29,90,yearly\n";

        $file = UploadedFile::fake()->createWithContent('data.csv', $csvContent);

        Livewire::actingAs($user)
            ->test(DataManagement::class)
            ->set('csvFile', $file)
            ->call('importCsv')
            ->assertSet('importStatus', '2 assinaturas importadas com sucesso!');

        $this->assertDatabaseHas('subscriptions', [
            'privacy_token' => $token,
            'name' => 'Plano A',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'privacy_token' => $token,
            'name' => 'Plano B',
        ]);

        $this->assertSame(2, Subscription::where('privacy_token', $token)->count());
        $this->assertSame(2, (int) cache()->get("user_{$token}_cache_version"));
        $this->assertTrue(Activity::where('event', 'csv_import')->exists());
    }
}
