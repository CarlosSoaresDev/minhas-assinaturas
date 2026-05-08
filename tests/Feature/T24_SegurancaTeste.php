<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Features;
use Livewire\Livewire;
use Tests\TestCase;

class T24_SegurancaTeste extends TestCase
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

    // Testes 098, 099 e 100 removidos: Página de Segurança unificada no Perfil/Senhas/2FA.
    
    public function test_101_segredos_de_2fa_sao_removidos_quando_confirmacao_e_abandonada(): void
    {
        $user = User::factory()->create();

        $user->forceFill([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->actingAs($user);

        // Ao testar o componente, o mount deve disparar a limpeza
        Livewire::test('settings.⚡two-factor');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);
    }

    public function test_102_usuario_pode_atualizar_senha_com_senha_atual_correta(): void
    {

        $user = User::factory()->create([
            'password' => \App\Services\PasswordSecurityService::hashPassword('password'),
        ]);

        $this->actingAs($user);

        $response = Livewire::test('settings.⚡password')
            ->set('current_password', 'password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword');

        $response->assertHasNoErrors();

        $this->assertTrue(\App\Services\PasswordSecurityService::checkPassword('new-password', $user->refresh()->password));
    }

    public function test_103_atualizacao_de_senha_exige_senha_atual_correta(): void
    {

        $user = User::factory()->create([
            'password' => \App\Services\PasswordSecurityService::hashPassword('password'),
        ]);

        $this->actingAs($user);

        $response = Livewire::test('settings.⚡password')
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('updatePassword');

        $response->assertHasErrors(['current_password']);
    }
}
