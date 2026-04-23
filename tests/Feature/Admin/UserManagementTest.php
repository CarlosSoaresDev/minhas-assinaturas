<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Livewire\Admin\Users\Manager;

class UserManagementTest extends TestCase
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

    public function test_006_admin_pode_visualizar_lista_de_usuarios(): void
    {
        $response = $this->actingAs($this->admin, 'web')->get('/usuarios');

        $response->assertOk();
    }

    public function test_007_usuario_comum_e_redirecionado_ao_tentar_acessar_lista_de_usuarios(): void
    {
        $user = User::factory()->create(['lgpd_consent_at' => now()]);
        $user->assignRole('user');

        $response = $this->actingAs($user, 'web')->get('/usuarios');

        $response->assertRedirect(route('dashboard'));
    }

    public function test_008_visitante_e_redirecionado_para_login_ao_abrir_usuarios(): void
    {
        $response = $this->get('/usuarios');

        $response->assertRedirect('/login');
    }

    public function test_009_admin_pode_criar_usuario_pelo_componente_livewire(): void
    {
        $this->actingAs($this->admin, 'web');

        Livewire::test(Manager::class)
            ->set('name', 'Novo Usuario')
            ->set('email', 'novo.usuario@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'user')
            ->set('status', 'active')
            ->call('save');

        $this->assertDatabaseHas('users', [
            'email' => 'novo.usuario@example.com',
            'deleted_at' => null,
        ]);
    }

    public function test_010_admin_nao_pode_excluir_a_propria_conta(): void
    {
        $this->actingAs($this->admin, 'web');

        Livewire::test(Manager::class)
            ->call('deleteUser', $this->admin->id);

        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'deleted_at' => null]);
    }

    public function test_011_admin_pode_excluir_outro_usuario(): void
    {
        $user = User::factory()->create(['lgpd_consent_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($this->admin, 'web');

        Livewire::test(Manager::class)
            ->call('deleteUser', $user->id);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_012_admin_recebe_confirmacao_antes_de_excluir(): void
    {
        $user = User::factory()->create(['lgpd_consent_at' => now()]);
        $user->assignRole('user');

        $this->actingAs($this->admin, 'web');

        Livewire::test(Manager::class)
            ->call('confirmDeleteUser', $user->id)
            ->assertSet('showDeleteModal', true)
            ->assertSee('Essa ação é irreversível e não poderá ser desfeita.');
    }

    public function test_057_admin_pode_editar_usuario_pelo_componente_livewire(): void
    {
        $user = User::factory()->create([
            'name' => 'Nome Original',
            'email' => 'original@example.com',
            'status' => 'active',
            'lgpd_consent_at' => now(),
        ]);
        $user->assignRole('user');

        $this->actingAs($this->admin, 'web');

        Livewire::test(Manager::class)
            ->call('openEditModal', $user->id)
            ->set('name', 'Nome Editado')
            ->set('email', 'editado@example.com')
            ->set('role', 'admin')
            ->set('status', 'blocked')
            ->set('password', '')
            ->set('password_confirmation', '')
            ->call('save');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nome Editado',
            'email' => 'editado@example.com',
            'status' => 'blocked',
        ]);

        $this->assertTrue($user->fresh()->hasRole('admin'));
    }
}

