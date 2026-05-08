<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\AppServiceProvider;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class T15_PaginaUsuariosAdminTeste extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['lgpd_consent_at' => now()]);
        $this->admin->assignRole('admin');
    }

    public function test_039_admin_pode_acessar_tela_visual_de_usuarios(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Gestão completa de contas');
        $response->assertSee('wire:id', false);
        $response->assertSee('data-update-uri', false);
    }

    public function test_040_lista_exibe_usuarios_ativos_e_excluidos_antigos(): void
    {
        $ativo = User::factory()->create([
            'name' => 'Usuario Antigo Ativo',
            'email' => 'antigo.ativo@example.com',
            'lgpd_consent_at' => now(),
        ]);
        $ativo->assignRole('user');

        $excluido = User::factory()->create([
            'name' => 'Usuario Antigo Excluido',
            'email' => 'antigo.excluido@example.com',
            'lgpd_consent_at' => now(),
        ]);
        $excluido->assignRole('user');
        $excluido->delete();

        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Usuario Antigo Ativo');
        $response->assertSee('Usuario Antigo Excluido');
    }

    public function test_041_usuario_nao_admin_e_redirecionado_para_dashboard_na_tela_de_usuarios(): void
    {
        $user = User::factory()->create(['lgpd_consent_at' => now()]);
        $user->assignRole('user');

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_042_acesso_a_rota_admin_users_legada_retorna_404(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');

        $response->assertNotFound();
    }

    public function test_043_rota_inexistente_renderiza_pagina_404_da_aplicacao(): void
    {
        $response = $this->actingAs($this->admin)->get('/rota-inexistente-teste-404');

        $response->assertNotFound();
        $response->assertSee('Página não encontrada');
        $response->assertDontSee('Levar-me para casa');
    }

    public function test_044_nome_com_html_e_escapado_na_tela_admin_de_usuarios(): void
    {
        $maliciousName = '<'.'img src=x onerror=alert(1)'.'>';

        $userMalicioso = User::factory()->create([
            'name' => $maliciousName,
            'email' => 'xss.usuario@example.com',
            'lgpd_consent_at' => now(),
        ]);
        $userMalicioso->assignRole('user');

        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertOk();
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $response->getContent());
        $this->assertStringNotContainsString($maliciousName, $response->getContent());
    }



    public function test_046_rotas_geradas_respeitam_base_path_do_request_quando_app_url_sem_subdiretorio(): void
    {
        // Simula instalacao em subdiretorio mesmo quando APP_URL nao contem o path.
        config(['app.url' => 'https://carlossoares.dev']);

        $request = Request::create(
            'https://carlossoares.dev/projetos/minhas-assinaturas/settings/profile',
            'GET'
        );

        // BasePath em hosting compartilhada costuma ser inferido via SCRIPT_NAME.
        $request->server->set('SCRIPT_NAME', '/projetos/minhas-assinaturas/index.php');

        app()->instance('request', $request);

        (new AppServiceProvider(app()))->boot();

        $twoFactorUrl = route('two-factor.edit');

        $this->assertStringContainsString(
            '/projetos/minhas-assinaturas/settings/two-factor',
            $twoFactorUrl
        );
    }

    public function test_047_rotas_geradas_removem_public_do_app_url_e_script_name(): void
    {
        config(['app.url' => 'https://carlossoares.dev/projetos/minhas-assinaturas/public']);

        $request = Request::create(
            'https://carlossoares.dev/projetos/minhas-assinaturas/dashboard',
            'GET'
        );

        $request->server->set('SCRIPT_NAME', '/projetos/minhas-assinaturas/public/index.php');

        app()->instance('request', $request);

        (new AppServiceProvider(app()))->boot();

        $this->assertSame(
            'https://carlossoares.dev/projetos/minhas-assinaturas',
            route('home')
        );

        $this->assertSame(
            'https://carlossoares.dev/projetos/minhas-assinaturas/dashboard',
            route('dashboard')
        );
    }
}
