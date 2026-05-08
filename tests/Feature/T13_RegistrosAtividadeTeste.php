<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class T13_RegistrosAtividadeTeste extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar papel de admin (Spatie)
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_031_admin_pode_acessar_pagina_de_logs_de_atividade()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.logs'))
            ->assertStatus(200);
    }

    public function test_032_paginacao_nao_adiciona_parametro_page_na_url()
    {
        // Criar logs suficientes para ter paginação manualmente com datas diferentes
        $now = now();
        for ($i = 1; $i <= 20; $i++) {
            Activity::create([
                'log_name' => 'default',
                'description' => "Log Test $i",
                'event' => 'created',
                'created_at' => $now->copy()->addMinutes($i),
            ]);
        }

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\ActivityLogs::class)
            ->set('perPage', 5)
            // Na página 1, devemos ver o Log 20 (último criado)
            ->assertSee('Log Test 20')
            // Ir para a página 2
            ->call('gotoPage', 2)
            // Agora devemos ver o Log 15, mas não o 20
            ->assertSee('Log Test 15')
            ->assertDontSee('Log Test 20')
            // E a prova final: a URL NÃO deve estar no HTML
            ->assertDontSeeHtml('?page=2');
    }

    public function test_033_busca_com_payload_sql_injection_eh_tratada_como_texto_literal()
    {
        Activity::create([
            'log_name' => 'default',
            'description' => 'Normal log entry',
            'event' => 'created',
            'created_at' => now(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\ActivityLogs::class)
            ->set('search', "' OR 1=1 --")
            ->assertDontSee('Normal log entry');
    }

    public function test_034_descricoes_html_sao_escapadas_na_listagem_de_logs()
    {
        $maliciousDescription = '<' . 'img src=x onerror=alert(1)' . '>';

        Activity::create([
            'log_name' => 'default',
            'description' => $maliciousDescription,
            'event' => 'created',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.logs'));

        $response->assertOk();
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $response->getContent());
        $this->assertStringNotContainsString($maliciousDescription, $response->getContent());
    }

    public function test_035_pagina_forcada_alem_do_limite_retorna_estado_vazio_sem_quebrar()
    {
        for ($i = 1; $i <= 2; $i++) {
            Activity::create([
                'log_name' => 'default',
                'description' => "Boundary log {$i}",
                'event' => 'created',
                'created_at' => now()->copy()->addMinutes($i),
            ]);
        }

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\ActivityLogs::class)
            ->set('perPage', 1)
            ->call('gotoPage', 999)
            ->assertSee('Nenhum log encontrado.');
    }
}
