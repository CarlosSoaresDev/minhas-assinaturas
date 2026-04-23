<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_035_home_retorna_status_200_para_visitante(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }
}
