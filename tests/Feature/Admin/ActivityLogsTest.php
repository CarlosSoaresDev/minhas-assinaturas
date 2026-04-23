<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogsTest extends TestCase
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

    public function test_admin_can_access_activity_logs_page()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.logs'))
            ->assertStatus(200);
    }

    public function test_pagination_does_not_add_page_parameter_to_url()
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
}
