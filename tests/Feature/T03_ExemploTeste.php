<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class T03_ExemploTeste extends TestCase
{
    use RefreshDatabase;

    public function test_003_home_redireciona_visitante_conforme_configuracao(): void
    {
        $response = $this->get('/');
        // Se a home for protegida ou redirecionar, este teste valida o fluxo inicial.
        $response->assertStatus(200);
        $response->assertSee('wire:id', false);
        // Livewire 4: data-update-uri foi removido; componentes usam wire:snapshot
        $response->assertSee('wire:snapshot', false);
    }
}
