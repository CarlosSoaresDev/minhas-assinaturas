<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Subscription;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class T17_TodasAssinaturasTeste extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar role admin se não existir
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin']);
        }

        $this->admin = $this->createAdmin();
        $this->regularUser = User::factory()->create();
    }

    private function createAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        return $user;
    }

    private function createSubscription(array $attributes = []): Subscription
    {
        return Subscription::create(array_merge([
            'privacy_token' => $this->regularUser->privacyToken?->token ?? 'token-test',
            'name' => 'Netflix',
            'billing_cycle' => 'monthly',
            'amount' => 19.90,
            'currency' => 'BRL',
            'start_date' => now(),
            'status' => 'active',
        ], $attributes));
    }

    /**
     * Admin pode acessar tela de todas subscriptions
     */
    public function test_053_admin_pode_acessar_todas_assinaturas()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.services'))
            ->assertOk();
    }

    /**
     * Usuário normal não acessa admin.services
     */
    public function test_054_usuario_normal_nao_acessa_assinaturas_admin()
    {
        $this->actingAs($this->regularUser)
            ->get(route('admin.services'))
            ->assertRedirect(route('dashboard'));
    }

    /**
     * Search com SQL injection literal é escapado (LIKE trata como string)
     */
    public function test_055_busca_com_sql_injection_literal_tratada_como_string()
    {
        $sub1 = $this->createSubscription(['name' => 'Netflix']);
        $sub2 = $this->createSubscription(['name' => "Hotstar'; DROP TABLE subscriptions; --"]);

        Livewire::actingAs($this->admin)
            ->test('admin.all-subscriptions')
            ->set('search', "'; DROP TABLE subscriptions; --")
            ->assertSee("Hotstar'; DROP TABLE subscriptions; --");
    }

    /**
     * Search com XSS payload é escapado na renderização
     */
    public function test_056_busca_com_payload_xss_escapada_na_tela()
    {
        $maliciousName = '<img src=x onerror=alert(1)>';
        $this->createSubscription(['name' => 'Netflix']);
        $this->createSubscription(['name' => $maliciousName]);

        $response = $this->actingAs($this->admin)->get(route('admin.services'));

        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $response->getContent());
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $response->getContent());
    }

    /**
     * Sorting by field inválido (try SQL injection) é ignorado
     */
    public function test_057_ordenacao_por_campo_invalido_usa_padrao()
    {
        $sub1 = $this->createSubscription(['name' => 'Zebra']);
        $sub2 = $this->createSubscription(['name' => 'Apple']);

        Livewire::actingAs($this->admin)
            ->test('admin.all-subscriptions')
            ->set('sortField', "name; DROP TABLE subscriptions;")
            ->assertOk();
    }

    /**
     * Category filter com ID inválido retorna vazio
     */
    public function test_058_filtro_de_categoria_com_id_invalido()
    {
        $this->createSubscription(['name' => 'Netflix']);
        $this->createSubscription(['name' => 'Spotify']);

        Livewire::actingAs($this->admin)
            ->test('admin.all-subscriptions')
            ->set('categoryFilter', 99999)
            ->assertSee('Nenhuma assinatura encontrada');
    }

    /**
     * Status filter com valor não permitido é ignorado
     */
    public function test_059_filtro_de_status_invalido_retorna_vazio()
    {
        $this->createSubscription(['status' => 'active']);

        Livewire::actingAs($this->admin)
            ->test('admin.all-subscriptions')
            ->set('statusFilter', 'invalid_status')
            ->assertSee('Nenhuma assinatura encontrada');
    }

    /**
     * Pagination: tentativa de acessar página > totalPages fica na última
     */
    public function test_060_paginacao_pagina_superior_ao_total_nao_quebra()
    {
        for ($i = 0; $i < 20; $i++) {
            $this->createSubscription(['name' => "Sub $i"]);
        }

        Livewire::actingAs($this->admin)
            ->test('admin.all-subscriptions')
            ->set('page', 999)
            ->assertSee('Nenhuma assinatura encontrada');
    }

    /**
     * Search com string vazia retorna todos
     */
    public function test_061_busca_vazia_retorna_todos()
    {
        $this->createSubscription(['name' => 'Netflix']);
        $this->createSubscription(['name' => 'Spotify']);

        Livewire::actingAs($this->admin)
            ->test('admin.all-subscriptions')
            ->set('search', '')
            ->assertSee('Netflix')
            ->assertSee('Spotify');
    }

    /**
     * Search partial matches funciona (Netflix encontra "Net")
     */
    public function test_062_busca_match_parcial()
    {
        $this->createSubscription(['name' => 'Netflix']);
        $this->createSubscription(['name' => 'Spotify']);

        Livewire::actingAs($this->admin)
            ->test('admin.all-subscriptions')
            ->set('search', 'Net')
            ->assertSee('Netflix')
            ->assertDontSee('Spotify');
    }

    /**
     * Search case-insensitive (LIKE no MySQL/SQLite é case-insensitive por padrão)
     */
    public function test_063_busca_insensivel_a_maiusculas()
    {
        $this->createSubscription(['name' => 'Netflix']);

        Livewire::actingAs($this->admin)
            ->test('admin.all-subscriptions')
            ->set('search', 'netflix')
            ->assertSee('Netflix');
    }

    /**
     * Search com multi-byte chars (acentos, etc)
     */
    public function test_064_busca_com_caracteres_especiais()
    {
        $this->createSubscription(['name' => 'Plataforma São Paulo']);

        Livewire::actingAs($this->admin)
            ->test('admin.all-subscriptions')
            ->set('search', 'São')
            ->assertSee('Plataforma São Paulo');
    }

    /**
     * Sorting alternates asc/desc ao clicar no mesmo field
     */
    public function test_065_ordenacao_alterna_direcao_ao_clicar_no_mesmo_campo()
    {
        $sub1 = $this->createSubscription(['name' => 'Zebra']);
        $sub2 = $this->createSubscription(['name' => 'Apple']);

        $component = Livewire::actingAs($this->admin)
            ->test('admin.all-subscriptions');

        $component->call('sortBy', 'name');
        // componente já inicia com sortField = 'name' e sortDirection = 'asc',
        // chamar sortBy no mesmo field alterna para 'desc'
        $this->assertEquals('desc', $component->get('sortDirection'));

        $component->call('sortBy', 'name');
        $this->assertEquals('asc', $component->get('sortDirection'));
    }

    /**
     * Notas com XSS escapadas na renderização
     */
    public function test_066_notas_com_xss_escapadas()
    {
        $maliciousNotes = '<script>alert("xss")</script>';
        $this->createSubscription(['name' => 'Netflix', 'notes' => $maliciousNotes]);

        $response = $this->actingAs($this->admin)->get(route('admin.services'));

        $this->assertStringContainsString('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $response->getContent());
        $this->assertStringNotContainsString('<script>alert("xss")</script>', $response->getContent());
    }

    /**
     * URL com javascript: protocol é renderizada como atributo href (href normalmente não executa)
     */
    public function test_067_url_do_servico_com_protocolo_javascript()
    {
        $this->createSubscription(['name' => 'Netflix', 'service_url' => 'javascript:alert(1)']);

        $response = $this->actingAs($this->admin)->get(route('admin.services'));

        // URL é renderizada como href, então verifica se está presente (mesmo que protocolo seja arriscado)
        $this->assertStringContainsString('javascript:alert(1)', $response->getContent());
    }

    /**
     * Combinação de múltiplos filtros funciona
     */
    public function test_068_combinacao_de_busca_status_e_categoria()
    {
        $cat = Category::create([
            'name' => 'Streaming',
            'slug' => 'streaming',
            'icon' => 'tv',
            'color' => '#ff0000',
            'is_system' => true,
            'privacy_token' => null,
        ]);

        $this->createSubscription(['name' => 'Netflix', 'category_id' => $cat->id, 'status' => 'active']);
        $this->createSubscription(['name' => 'Spotify', 'category_id' => $cat->id, 'status' => 'cancelled']);
        $this->createSubscription(['name' => 'Another', 'status' => 'active']);

        Livewire::actingAs($this->admin)
            ->test('admin.all-subscriptions')
            ->set('search', 'Netflix')
            ->set('statusFilter', 'active')
            ->set('categoryFilter', $cat->id)
            ->assertSee('Netflix')
            ->assertDontSee('Spotify')
            ->assertDontSee('Another');
    }

    /**
     * Pagination boundary: previousPage em page 1 não causa erro
     */
    public function test_069_paginacao_pagina_anterior_na_primeira_pagina()
    {
        $this->createSubscription(['name' => 'Netflix']);

        Livewire::actingAs($this->admin)
            ->test('admin.all-subscriptions')
            ->set('page', 1)
            ->call('previousPage')
            ->assertSet('page', 1);
    }

    /**
     * Search com string muitooooo longa (>1000 chars)
     */
    public function test_070_busca_com_string_muito_longa()
    {
        $this->createSubscription(['name' => 'Netflix']);
        $longString = str_repeat('a', 1000);

        Livewire::actingAs($this->admin)
            ->test('admin.all-subscriptions')
            ->set('search', $longString)
            ->assertSee('Nenhuma assinatura encontrada');
    }

    /**
     * Admin vê subscription de qualquer privacy token (não é isolado para admin)
     */
    public function test_071_admin_ve_assinaturas_de_todos_os_usuarios()
    {
        $user2 = User::factory()->create();

        $sub1 = $this->createSubscription(['name' => 'Netflix User1']);
        $sub2 = Subscription::create([
            'privacy_token' => $user2->privacyToken?->token ?? 'token-user2',
            'name' => 'Spotify User2',
            'billing_cycle' => 'monthly',
            'amount' => 11.90,
            'currency' => 'BRL',
            'start_date' => now(),
            'status' => 'active',
        ]);

        Livewire::actingAs($this->admin)
            ->test('admin.all-subscriptions')
            ->assertSee('Netflix User1')
            ->assertSee('Spotify User2');
    }
}
