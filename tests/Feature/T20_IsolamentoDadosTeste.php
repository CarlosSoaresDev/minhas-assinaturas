<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class T20_IsolamentoDadosTeste extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure categories exist
        Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'is_system' => true,
        ]);
    }

    public function test_130_usuario_nao_lista_assinaturas_de_outro_usuario(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create subscription for user 2
        Subscription::create([
            'privacy_token' => $user2->privacyToken->token,
            'name' => 'Secret Subscription',
            'billing_cycle' => 'monthly',
            'amount' => 19.90,
            'start_date' => now(),
        ]);

        $response = $this->actingAs($user1)->get(route('front.subscriptions.index'));

        $response->assertOk();
        $response->assertDontSee('Secret Subscription');
    }

    public function test_131_usuario_nao_visualiza_detalhe_de_assinatura_de_outro_usuario(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $sub = Subscription::create([
            'privacy_token' => $user2->privacyToken->token,
            'name' => 'Secret Subscription',
            'billing_cycle' => 'monthly',
            'amount' => 19.90,
            'start_date' => now(),
        ]);

        $this->assertFalse(Gate::forUser($user1)->allows('view', $sub));
    }

    public function test_132_usuario_nao_atualiza_assinatura_de_outro_usuario(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $sub = Subscription::create([
            'privacy_token' => $user2->privacyToken->token,
            'name' => 'Secret Subscription',
            'billing_cycle' => 'monthly',
            'amount' => 19.90,
            'start_date' => now(),
        ]);

        $this->assertFalse(Gate::forUser($user1)->allows('update', $sub));
    }

    public function test_133_usuario_nao_exclui_assinatura_de_outro_usuario(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $sub = Subscription::create([
            'privacy_token' => $user2->privacyToken->token,
            'name' => 'Secret Subscription',
            'billing_cycle' => 'monthly',
            'amount' => 19.90,
            'start_date' => now(),
        ]);

        $this->assertFalse(Gate::forUser($user1)->allows('delete', $sub));
        $this->assertDatabaseHas('subscriptions', ['id' => $sub->id]);
    }
}
