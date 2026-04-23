<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_026_tela_de_registro_pode_ser_renderizada(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_027_novo_usuario_pode_se_registrar_com_consentimento_lgpd(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'lgpd_consent' => '1',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        
        $user = \App\Models\User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user->lgpd_consent_at);
        $this->assertNotNull($user->privacyToken);
    }

    public function test_028_registro_falha_sem_consentimento_lgpd(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['lgpd_consent']);
        $this->assertGuest();
    }
}
