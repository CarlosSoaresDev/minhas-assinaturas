<?php

namespace Tests\Feature\Privacy;

use App\Models\User;
use App\Models\Category;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DataIsolationTest extends TestCase
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

    public function test_038_usuario_nao_lista_assinaturas_de_outro_usuario(): void
    {
        $this->markTestSkipped('Aguardando implementação dos CRUDs de assinaturas.');
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create subscription for user 2
        Subscription::create([
            'id' => Str::uuid()->toString(),
            'privacy_token' => $user2->privacyToken->token,
            'name' => 'Secret Subscription',
            'billing_cycle' => 'monthly',
            'amount' => 19.90,
            'start_date' => now(),
        ]);

        $response = $this->actingAs($user1)->getJson(route('subscriptions.index')); 
        
        $response->assertStatus(200);
        $response->assertJsonMissing(['name' => 'Secret Subscription']);
    }

    public function test_039_usuario_nao_visualiza_detalhe_de_assinatura_de_outro_usuario(): void
    {
        $this->markTestSkipped('Aguardando implementação dos CRUDs de assinaturas.');
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $sub = Subscription::create([
            'id' => Str::uuid()->toString(),
            'privacy_token' => $user2->privacyToken->token,
            'name' => 'Secret Subscription',
            'billing_cycle' => 'monthly',
            'amount' => 19.90,
            'start_date' => now(),
        ]);

        $response = $this->actingAs($user1)->get(route('subscriptions.show', $sub));
        
        // Deve ser 403 ou 404 (403 se falhar na Policy, 404 se falhar no Scope global)
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    public function test_040_usuario_nao_atualiza_assinatura_de_outro_usuario(): void
    {
        $this->markTestSkipped('Aguardando implementação dos CRUDs de assinaturas.');
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $sub = Subscription::create([
            'id' => Str::uuid()->toString(),
            'privacy_token' => $user2->privacyToken->token,
            'name' => 'Secret Subscription',
            'billing_cycle' => 'monthly',
            'amount' => 19.90,
            'start_date' => now(),
        ]);

        $response = $this->actingAs($user1)->put(route('subscriptions.update', $sub), [
            'name' => 'Hacked Subscription',
        ]);
        
        $this->assertTrue(in_array($response->status(), [403, 404]));
        $this->assertDatabaseMissing('subscriptions', ['name' => 'Hacked Subscription']);
    }

    public function test_041_usuario_nao_exclui_assinatura_de_outro_usuario(): void
    {
        $this->markTestSkipped('Aguardando implementação dos CRUDs de assinaturas.');
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $sub = Subscription::create([
            'id' => Str::uuid()->toString(),
            'privacy_token' => $user2->privacyToken->token,
            'name' => 'Secret Subscription',
            'billing_cycle' => 'monthly',
            'amount' => 19.90,
            'start_date' => now(),
        ]);

        $response = $this->actingAs($user1)->delete(route('subscriptions.destroy', $sub));
        
        $this->assertTrue(in_array($response->status(), [403, 404]));
        $this->assertDatabaseHas('subscriptions', ['id' => $sub->id]);
    }
}
