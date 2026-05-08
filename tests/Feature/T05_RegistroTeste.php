<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Tests\TestCase;

class T05_RegistroTeste extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());

        if (! Route::has('register')) {
            $this->markTestSkipped('Cadastro publico desativado ou rota register nao registrada.');
        }
    }

    public function test_006_tela_de_registro_pode_ser_renderizada(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_007_novo_usuario_pode_se_registrar_com_consentimento_lgpd(): void
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

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user->lgpd_consent_at);
        $this->assertNotNull($user->privacyToken);
    }

    public function test_008_registro_falha_sem_consentimento_lgpd(): void
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
