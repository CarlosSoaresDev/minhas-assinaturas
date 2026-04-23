<?php

namespace Tests\Feature\Privacy;

use App\Models\User;
use App\Models\PrivacyToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PrivacyTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_042_privacy_token_e_criado_em_formato_uuid(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->privacyToken);
        $this->assertTrue(Str::isUuid($user->privacyToken->token));
    }

    public function test_043_privacy_token_e_unico_por_usuario(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->assertNotEquals($user1->privacyToken->token, $user2->privacyToken->token);
    }
}
