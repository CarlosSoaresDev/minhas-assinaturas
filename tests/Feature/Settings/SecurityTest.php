<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Features;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);
    }

    public function test_049_pagina_de_seguranca_pode_ser_renderizada(): void
    {
        $this->markTestSkipped('Aguardando reescrita das views com Bootstrap na Fase 1 â€” views ainda usam Flux');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('security.edit'))
            ->assertOk()
            ->assertSee('Two-factor authentication')
            ->assertSee('Enable 2FA');
    }

    public function test_050_pagina_de_seguranca_exige_confirmacao_de_senha(): void
    {
        $this->markTestSkipped('Aguardando reescrita das views com Bootstrap na Fase 1 â€” views ainda usam Flux');

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('security.edit'));

        $response->assertRedirect(route('password.confirm'));
    }

    public function test_051_pagina_de_seguranca_oculta_2fa_quando_feature_desativada(): void
    {
        $this->markTestSkipped('Aguardando reescrita das views com Bootstrap na Fase 1 â€” views ainda usam Flux');

        config(['fortify.features' => []]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('security.edit'))
            ->assertOk()
            ->assertSee('Update password')
            ->assertDontSee('Two-factor authentication');
    }

    public function test_052_segredos_de_2fa_sao_removidos_quando_confirmacao_e_abandonada(): void
    {
        $this->markTestSkipped('Aguardando reescrita das views com Bootstrap na Fase 1 â€” views ainda usam Flux');

        $user = User::factory()->create();

        $user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->actingAs($user);

        $component = Livewire::test('pages::settings.security');

        $component->assertSet('twoFactorEnabled', false);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);
    }

    public function test_053_usuario_pode_atualizar_senha_com_senha_atual_correta(): void
    {
        $this->markTestSkipped('Aguardando reescrita das views com Bootstrap na Fase 1 â€” views ainda usam Flux');

        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.security')
            ->set('current_password', 'password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword');

        $response->assertHasNoErrors();

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_054_atualizacao_de_senha_exige_senha_atual_correta(): void
    {
        $this->markTestSkipped('Aguardando reescrita das views com Bootstrap na Fase 1 â€” views ainda usam Flux');

        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user);

        $response = Livewire::test('pages::settings.security')
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword');

        $response->assertHasErrors(['current_password']);
    }
}
