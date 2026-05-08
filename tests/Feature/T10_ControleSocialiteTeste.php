<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class T10_ControleSocialiteTeste extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.public_registration' => true,
            'services.google.enabled' => true,
        ]);
    }

    public function test_023_google_oauth_desativado_retorna_404(): void
    {
        config(['services.google.enabled' => false]);

        $this->get(route('auth.google.redirect'))->assertNotFound();
        $this->get(route('auth.google.callback'))->assertNotFound();
    }

    public function test_024_redirecionamento_para_google_delega_para_o_driver_socialite(): void
    {
        $driver = Mockery::mock();
        $driver->shouldReceive('redirect')->once()->andReturn(redirect('/google-oauth'));

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($driver);

        $response = $this->get(route('auth.google.redirect'));

        $response->assertRedirect('/google-oauth');
    }

    public function test_025_callback_com_erro_redireciona_para_login_com_mensagem(): void
    {
        $driver = Mockery::mock();
        $driver->shouldReceive('user')->once()->andThrow(new \Exception('google down'));

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($driver);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'Falha na autenticação com Google.');
        $this->assertGuest();
    }

    public function test_026_callback_rejeita_google_sem_email_valido(): void
    {
        $socialUser = Mockery::mock();
        $socialUser->shouldReceive('getEmail')->once()->andReturn('');
        $socialUser->shouldReceive('getName')->never();
        $socialUser->shouldReceive('getNickname')->never();

        $driver = Mockery::mock();
        $driver->shouldReceive('user')->once()->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($driver);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error', 'A conta Google não retornou e-mail válido.');
        $this->assertGuest();
    }

    public function test_027_callback_cria_novo_usuario_google_e_faz_login(): void
    {
        $socialUser = Mockery::mock();
        $socialUser->shouldReceive('getEmail')->once()->andReturn('novo.google@example.com');
        $socialUser->shouldReceive('getName')->once()->andReturn('Google User');
        $socialUser->shouldReceive('getNickname')->never();

        $driver = Mockery::mock();
        $driver->shouldReceive('user')->once()->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($driver);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'novo.google@example.com',
            'name' => 'Google User',
            'created_via_google' => true,
        ]);
        $this->assertNotNull(User::where('email', 'novo.google@example.com')->first()?->lgpd_consent_at);
    }

    public function test_028_callback_atualiza_lgpd_de_usuario_existente_e_faz_login(): void
    {
        $user = User::factory()->create([
            'email' => 'existente@example.com',
            'lgpd_consent_at' => null,
        ]);

        $socialUser = Mockery::mock();
        $socialUser->shouldReceive('getEmail')->once()->andReturn('existente@example.com');
        $socialUser->shouldReceive('getName')->never();
        $socialUser->shouldReceive('getNickname')->never();

        $driver = Mockery::mock();
        $driver->shouldReceive('user')->once()->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($driver);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user->fresh());
        $this->assertNotNull($user->fresh()->lgpd_consent_at);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
