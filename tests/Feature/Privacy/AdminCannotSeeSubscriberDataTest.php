<?php

namespace Tests\Feature\Privacy;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminCannotSeeSubscriberDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_036_admin_nao_pode_acessar_dados_de_assinaturas_de_outro_usuario(): void
    {
        $this->markTestSkipped('Aguardando implementação dos CRUDs de assinaturas.');
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $user = User::factory()->create();

        $sub = Subscription::create([
            'id' => Str::uuid()->toString(),
            'privacy_token' => $user->privacyToken->token,
            'name' => 'Secret User Sub',
            'billing_cycle' => 'monthly',
            'amount' => 19.90,
            'start_date' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('subscriptions.show', $sub));
        
        // Only the EXPLICIT owner via privacy_token is allowed. Admin token != User token.
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }
}
