<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class T11_ConfirmacaoSenhaTeste extends TestCase
{
    use RefreshDatabase;

    public function test_029_tela_de_confirmacao_de_senha_pode_ser_renderizada(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('password.confirm'));

        $response->assertOk();
    }

    public function test_030_confirmacao_de_senha_normaliza_url_intended_sem_subdiretorio(): void
    {
        $appUrl = 'https://carlossoares.dev/projetos/minhas-assinaturas';
        config(['app.url' => $appUrl]);

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['url.intended' => 'https://carlossoares.dev/settings/two-factor'])
            ->post(route('password.confirm.store'), [
                'password' => 'password',
            ]);

        $response->assertRedirect($appUrl.'/settings/two-factor');
    }
}
