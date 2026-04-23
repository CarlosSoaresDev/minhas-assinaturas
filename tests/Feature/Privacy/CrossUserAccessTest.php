<?php

namespace Tests\Feature\Privacy;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossUserAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_037_visitante_e_redirecionado_para_login_ao_acessar_dashboard_privado(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    // A test to ensure users cannot view another's dashboard or subscriptions
    // will be fully implemented when subscriptions exist.
    // For now, testing basic Auth boundaries.
}
