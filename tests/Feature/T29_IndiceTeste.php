<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Subscription;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Livewire\Livewire;
use Tests\TestCase;

class T29_IndiceTeste extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->privacyToken->token;
    }

    private function createSubscription(array $attributes = []): Subscription
    {
        return Subscription::create(array_merge([
            'privacy_token' => $this->token,
            'name' => 'Netflix',
            'billing_cycle' => 'monthly',
            'amount' => 19.90,
            'currency' => 'BRL',
            'start_date' => now(),
            'status' => 'active',
        ], $attributes));
    }

    public function test_118_pode_listar_assinaturas()
    {
        $this->createSubscription(['name' => 'Netflix']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Subscriptions\Index::class)
            ->assertSee('Netflix');
    }

    public function test_119_pode_criar_assinatura()
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

    public function test_120_pode_exportar_csv()
    {
        $this->createSubscription(['name' => 'Netflix']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Subscriptions\Index::class)
            ->call('exportCsv')
            ->assertFileDownloaded('Minhas_Assinaturas.csv');
    }

    public function test_121_exportacao_csv_neutraliza_formula_injection_em_campos_textuais()
    {
        $category = Category::create([
            'name' => '=CategoriaPerigosa',
            'slug' => 'categoria-perigosa',
            'icon' => 'tv',
            'color' => '#ff0000',
            'is_system' => true,
            'privacy_token' => null,
        ]);

        $this->createSubscription([
            'name' => '=2+2',
            'category_id' => $category->id,
            'notes' => '@SUM(1,1)',
            'service_url' => '=HYPERLINK("http://evil")',
        ]);

        $this->actingAs($this->user);
        $component = app(\App\Livewire\Subscriptions\Index::class);
        $response = $component->exportCsv();

        $this->assertInstanceOf(StreamedResponse::class, $response);

        ob_start();
        $response->sendContent();
        $csv = (string) ob_get_clean();

        $this->assertStringContainsString("'=2+2", $csv);
        $this->assertStringContainsString("'=HYPERLINK(\"\"http://evil\"\")", $csv);
        $this->assertStringContainsString("'=CategoriaPerigosa", $csv);
        $this->assertStringContainsString("'@SUM(1,1)", $csv);
    }

    public function test_122_pode_importar_csv_com_verificacao_de_duplicatas()
    {
        // Pre-existing
        $this->createSubscription(['name' => 'Netflix']);

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

    public function test_123_rejeita_inputs_com_payloads_maliciosos_e_tamanhos_excessivos()
    {
        Livewire::actingAs($this->user)
            ->test(
                \App\Livewire\Subscriptions\Index::class
            )
            ->set('name', str_repeat('A', 256))
            ->set('amount', '21.90')
            ->set('billing_cycle', 'monthly')
            ->set('start_date', now()->format('Y-m-d'))
            ->set('notes', str_repeat('B', 1001))
            ->set('service_url', 'javascript:alert(1)')
            ->call('save')
            ->assertHasErrors(['name', 'notes', 'service_url']);
    }

    public function test_124_busca_com_payload_sql_injection_eh_tratada_como_texto_literal()
    {
        $this->createSubscription(['name' => 'Netflix']);
        $this->createSubscription(['name' => 'Disney+']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Subscriptions\Index::class)
            ->set('search', "' OR 1=1 --")
            ->assertSee('Nenhuma assinatura encontrada')
            ->assertDontSee('Netflix')
            ->assertDontSee('Disney+');
    }

    public function test_125_html_injection_e_renderizada_como_texto_literal_na_listagem()
    {
        $this->createSubscription([
            'name' => '<script>alert(1)</script>',
            'notes' => '<' . 'img src=x onerror=alert(1)' . '>',
        ]);

        $maliciousMarkup = '<' . 'script>alert(1)</script>';
        $maliciousImage = '<' . 'img src=x onerror=alert(1)' . '>';

        $response = $this->actingAs($this->user)->get(route('front.subscriptions.index'));

        $response->assertOk();
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $response->getContent());
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $response->getContent());
        $this->assertStringNotContainsString($maliciousMarkup, $response->getContent());
        $this->assertStringNotContainsString($maliciousImage, $response->getContent());
    }

    public function test_126_importacao_csv_com_bom_e_linha_incompleta_e_isolamento_por_privacy_token()
    {
        $otherUser = User::factory()->create();

        Subscription::create([
            'privacy_token' => $otherUser->privacyToken->token,
            'name' => 'Netflix',
            'billing_cycle' => 'monthly',
            'amount' => 19.90,
            'currency' => 'BRL',
            'start_date' => now(),
            'status' => 'active',
        ]);

        $csvContent = "\xEF\xBB\xBFNome;Valor;Ciclo;Categoria;Início;Vencimento;Status;Renovação;Anotações\n";
        $csvContent .= "Netflix;40,00;monthly;Streaming;01/01/2024;01/02/2024;active;Sim;Nota1\n";
        $csvContent .= "Linha incompleta apenas com nome\n";

        $file = UploadedFile::fake()->createWithContent('import.csv', $csvContent);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Subscriptions\Index::class)
            ->set('csvFile', $file)
            ->assertSet('showImportModal', true)
            ->assertSet('importSummary.total', 2)
            ->assertSet('importSummary.new', 2)
            ->call('confirmImport')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('subscriptions', [
            'privacy_token' => $this->token,
            'name' => 'Netflix',
        ]);

        $this->assertSame(1, Subscription::where('privacy_token', $this->token)->count());
        $this->assertSame(1, Subscription::where('privacy_token', $otherUser->privacyToken->token)->count());
    }
}
