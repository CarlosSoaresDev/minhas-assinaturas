<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class T22_AdminNaoVeDadosAssinanteTeste extends TestCase
{
    use RefreshDatabase;

    public function test_135_admin_nao_pode_acessar_dados_de_assinaturas_de_outro_usuario(): void
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create();

        $sub = Subscription::create([
            'privacy_token' => $user->privacyToken->token,
            'name' => 'Secret User Sub',
            'billing_cycle' => 'monthly',
            'amount' => 19.90,
            'start_date' => now(),
        ]);

        $this->assertFalse(Gate::forUser($admin)->allows('view', $sub));
    }
}
