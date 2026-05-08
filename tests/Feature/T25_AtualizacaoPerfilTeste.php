<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class T25_AtualizacaoPerfilTeste extends TestCase
{
    use RefreshDatabase;

    private function createProfileUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'alerts_enabled' => true,
            'alert_days_before' => 7,
        ], $attributes));
    }

    public function test_104_pagina_de_perfil_pode_ser_exibida(): void
    {
        $this->actingAs($user = $this->createProfileUser());

        $this->get(route('profile.edit'))->assertOk();
    }

    public function test_105_usuario_pode_atualizar_apenas_o_nome_no_perfil(): void
    {
        $user = $this->createProfileUser();

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.profile')
            ->set('name', 'Test User')
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $user->refresh();

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals($user->getOriginal('email'), $user->email);
    }

    public function test_106_nome_com_html_e_escapado_na_pagina_de_perfil(): void
    {
        $user = $this->createProfileUser([
            'name' => '<svg onload=alert(1)>'
        ]);

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk();
        $this->assertStringContainsString('&lt;svg onload=alert(1)&gt;', $response->getContent());
        $this->assertStringNotContainsString('<svg onload=alert(1)>', $response->getContent());
    }

    public function test_107_formulario_de_perfil_rejeita_nome_grande_e_alerta_fora_da_faixa(): void
    {
        $user = $this->createProfileUser();

        $this->actingAs($user);

        $response = Livewire::test(\App\Livewire\Settings\ProfileForm::class)
            ->set('name', str_repeat('A', 256))
            ->set('alerts_enabled', true)
            ->set('alert_days_before', 0)
            ->call('updateProfileInformation');

        $response->assertHasErrors(['name', 'alert_days_before']);
    }

    public function test_108_usuario_nao_consegue_alterar_email_pela_tela_de_perfil(): void
    {
        $user = $this->createProfileUser();
        $emailOriginal = $user->email;

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.profile')
            ->set('name', 'Test User')
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $this->assertEquals($emailOriginal, $user->refresh()->email);
    }

    public function test_109_usuario_pode_excluir_a_propria_conta_com_soft_delete(): void
    {
        $user = $this->createProfileUser();

        $this->actingAs($user);

        $response = Livewire::test('settings.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $response
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'inactive',
        ]);
        $this->assertFalse(auth()->check());
    }

    public function test_110_exclusao_de_conta_exige_senha_correta(): void
    {
        $user = $this->createProfileUser();

        $this->actingAs($user);

        $response = Livewire::test('settings.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        $response->assertHasErrors(['password']);

        $this->assertNotSoftDeleted('users', ['id' => $user->id]);
    }
}
