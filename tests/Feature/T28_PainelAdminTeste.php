<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\AdminDashboard;
use App\Models\Category;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class T28_PainelAdminTeste extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create([
            'lgpd_consent_at' => now(),
            'alerts_enabled' => true,
            'alert_days_before' => 7,
        ]);
        $this->admin->assignRole('admin');
    }

    public function test_116_painel_admin_carrega_metricas_principais(): void
    {
        $olderUser = User::factory()->create([
            'name' => 'Usuario Antigo',
            'email' => 'antigo@example.com',
            'lgpd_consent_at' => now(),
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);
        $olderUser->assignRole('user');

        $recentUser = User::factory()->create([
            'name' => 'Usuario Recente',
            'email' => 'recente@example.com',
            'lgpd_consent_at' => now(),
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
        $recentUser->assignRole('user');

        $catStreaming = Category::create([
            'name' => 'Streaming',
            'slug' => 'streaming',
            'icon' => 'tv',
            'color' => '#ff0000',
            'is_system' => true,
            'privacy_token' => null,
        ]);

        $catGames = Category::create([
            'name' => 'Games',
            'slug' => 'games',
            'icon' => 'controller',
            'color' => '#00ff00',
            'is_system' => true,
            'privacy_token' => null,
        ]);

        Subscription::create([
            'privacy_token' => $recentUser->privacyToken->token,
            'category_id' => $catStreaming->id,
            'name' => 'Netflix',
            'billing_cycle' => 'monthly',
            'amount' => 39.90,
            'currency' => 'BRL',
            'start_date' => now()->subMonth(),
            'status' => 'active',
        ]);
        Subscription::create([
            'privacy_token' => $recentUser->privacyToken->token,
            'category_id' => $catStreaming->id,
            'name' => 'Disney+',
            'billing_cycle' => 'monthly',
            'amount' => 33.90,
            'currency' => 'BRL',
            'start_date' => now()->subMonth(),
            'status' => 'active',
        ]);
        Subscription::create([
            'privacy_token' => $olderUser->privacyToken->token,
            'category_id' => $catGames->id,
            'name' => 'Game Pass',
            'billing_cycle' => 'monthly',
            'amount' => 49.90,
            'currency' => 'BRL',
            'start_date' => now()->subMonth(),
            'status' => 'cancelled',
        ]);

        \DB::table('sessions')->insert([
            'id' => 'admin-session-1',
            'user_id' => $this->admin->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'payload',
            'last_activity' => now()->subMinutes(1)->getTimestamp(),
        ]);

        \DB::table('failed_jobs')->insert([
            'uuid' => 'failed-job-1',
            'connection' => 'sync',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'Exception',
            'failed_at' => now(),
        ]);
        \DB::table('failed_jobs')->insert([
            'uuid' => 'failed-job-2',
            'connection' => 'sync',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'Exception',
            'failed_at' => now(),
        ]);

        \Spatie\Activitylog\Models\Activity::create([
            'log_name' => 'default',
            'description' => 'critical error 1',
            'event' => 'created',
            'created_at' => now(),
        ]);
        \Spatie\Activitylog\Models\Activity::create([
            'log_name' => 'default',
            'description' => 'critical error 2',
            'event' => 'created',
            'created_at' => now(),
        ]);
        \Spatie\Activitylog\Models\Activity::create([
            'log_name' => 'default',
            'description' => 'critical error 3',
            'event' => 'created',
            'created_at' => now(),
        ]);

        Livewire::actingAs($this->admin)
            ->test(AdminDashboard::class)
            ->assertSet('totalUsers', 3)
            ->assertSet('totalServices', 2)
            ->assertSet('onlineUsers', 1)
            ->assertSet('systemHealth', 65)
            ->assertSet('popularCategories.0.name', 'Streaming')
            ->assertSet('popularCategories.0.count', 2)
            ->assertSee('Usuario Recente')
            ->assertSee('Usuario Antigo');
    }

    public function test_117_painel_admin_renderiza_dados_escapados_na_view(): void
    {
        $maliciousCategory = Category::create([
            'name' => '<img src=x onerror=alert(1)>',
            'slug' => 'xss-cat',
            'icon' => 'tv',
            'color' => '#123456',
            'is_system' => true,
            'privacy_token' => null,
        ]);

        $user = User::factory()->create([
            'name' => '<svg onload=alert(1)>',
            'email' => 'xss@example.com',
            'lgpd_consent_at' => now(),
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        Subscription::create([
            'privacy_token' => $user->privacyToken->token,
            'category_id' => $maliciousCategory->id,
            'name' => 'Service',
            'billing_cycle' => 'monthly',
            'amount' => 10,
            'currency' => 'BRL',
            'start_date' => now()->subMonth(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertOk();
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $response->getContent());
        $this->assertStringContainsString('&lt;svg onload=alert(1)&gt;', $response->getContent());
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $response->getContent());
        $this->assertStringNotContainsString('<svg onload=alert(1)>', $response->getContent());
    }
}
