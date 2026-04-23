<?php

namespace Tests\Feature\Subscriptions;

use App\Models\User;
use App\Models\PrivacyToken;
use App\Models\Subscription;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $privacyToken = PrivacyToken::create([
            'user_id' => $this->user->id,
            'token' => 'test-token-123'
        ]);
        $this->token = $privacyToken->token;
    }

    public function test_can_list_subscriptions()
    {
        Subscription::factory()->create([
            'privacy_token' => $this->token,
            'name' => 'Netflix'
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Subscriptions\Index::class)
            ->assertSee('Netflix');
    }

    public function test_can_create_subscription()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Subscriptions\Index::class)
            ->set('name', 'Spotify')
            ->set('amount', '21.90')
            ->set('billing_cycle', 'monthly')
            ->set('start_date', now()->format('Y-m-d'))
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('subscriptions', [
            'name' => 'Spotify',
            'privacy_token' => $this->token
        ]);
    }

    public function test_can_export_csv()
    {
        Subscription::factory()->create([
            'privacy_token' => $this->token,
            'name' => 'Netflix'
        ]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Subscriptions\Index::class)
            ->call('exportCsv')
            ->assertFileDownloaded('Minhas_Assinaturas.csv');
    }

    public function test_can_import_csv_with_duplicate_check()
    {
        // Pre-existing
        Subscription::factory()->create([
            'privacy_token' => $this->token,
            'name' => 'Netflix'
        ]);

        $csvContent = "Nome;Valor;Ciclo;Categoria;Início;Vencimento;Status;Renovação;Anotações\n";
        $csvContent .= "Netflix;40,00;monthly;Streaming;01/01/2024;01/02/2024;active;Sim;Nota1\n";
        $csvContent .= "Disney+;30,00;monthly;Streaming;01/01/2024;01/02/2024;active;Sim;Nota2\n";

        $file = UploadedFile::fake()->createWithContent('import.csv', $csvContent);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Subscriptions\Index::class)
            ->set('csvFile', $file)
            ->assertSet('showImportModal', true)
            ->assertSet('importSummary.total', 2)
            ->assertSet('importSummary.duplicates', 1)
            ->set('ignoreDuplicates', true)
            ->call('confirmImport')
            ->assertHasNoErrors();

        $this->assertEquals(2, Subscription::where('privacy_token', $this->token)->count());
        $this->assertDatabaseHas('subscriptions', ['name' => 'Disney+', 'privacy_token' => $this->token]);
    }
}
