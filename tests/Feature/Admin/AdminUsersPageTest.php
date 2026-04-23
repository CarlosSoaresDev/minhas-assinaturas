<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsersPageTest extends TestCase
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

    public function test_001_admin_pode_acessar_tela_visual_de_usuarios(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Gestão completa de contas');
        $response->assertSee('wire:id', false);
        $response->assertSee('livewireScriptConfig', false);
    }

    public function test_002_lista_exibe_usuarios_ativos_e_excluidos_antigos(): void
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

    public function test_003_usuario_nao_admin_e_redirecionado_para_dashboard_na_tela_de_usuarios(): void
    {
        $user = User::factory()->create(['lgpd_consent_at' => now()]);
        $user->assignRole('user');

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_004_acesso_a_rota_admin_users_legada_retorna_404(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');

        $response->assertNotFound();
    }

    public function test_005_rota_inexistente_renderiza_pagina_404_da_aplicacao(): void
    {
        $response = $this->actingAs($this->admin)->get('/rota-inexistente-teste-404');

        $response->assertNotFound();
        $response->assertSee('Página não encontrada');
        $response->assertDontSee('Levar-me para casa');
    }
}
