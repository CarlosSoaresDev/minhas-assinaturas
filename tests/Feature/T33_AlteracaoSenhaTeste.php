<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PasswordSecurityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class T33_AlteracaoSenhaTeste extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_pode_alterar_senha(): void
    {
        $user = User::factory()->create([
            'password' => PasswordSecurityService::hashPassword('password123'),
            'created_via_google' => false,
        ]);

        $this->actingAs($user);

        $component = Volt::test('settings.⚡password')
            ->set('current_password', 'password123')
            ->set('password', 'new-password-123!')
            ->set('password_confirmation', 'new-password-123!')
            ->call('updatePassword');

        if ($component->errors()->any()) {
            fwrite(STDERR, print_r($component->errors()->toArray(), true));
        }

        $component->assertHasNoErrors();
        // $component->assertSessionHas('password_success');

        $user->refresh();
        $this->assertTrue(PasswordSecurityService::checkPassword('new-password-123!', $user->password));
    }

    public function test_usuario_google_pode_definir_senha_sem_senha_atual(): void
    {
        $user = User::factory()->create([
            'password' => PasswordSecurityService::hashPassword('password123'),
            'created_via_google' => true,
        ]);

        $this->actingAs($user);

        $component = Volt::test('settings.⚡password')
            ->set('password', 'new-password-google!')
            ->set('password_confirmation', 'new-password-google!')
            ->call('updatePassword');

        $component->assertHasNoErrors();

        $user->refresh();
        $this->assertTrue(PasswordSecurityService::checkPassword('new-password-google!', $user->password));
        $this->assertFalse($user->refresh()->created_via_google);
    }
}
