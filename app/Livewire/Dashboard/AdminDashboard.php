<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use App\Models\Subscription;
use Livewire\Component;

class AdminDashboard extends Component
{
    public int $totalUsers = 0;
    public int $totalServices = 0;
    public int $onlineUsers = 0;
    public int $systemHealth = 100;
    public array $popularCategories = [];
    public array $recentUsers = [];

    public function mount()
    {
        $this->loadMetrics();
    }

    public function loadMetrics()
    {
        $this->totalUsers = User::count();
        $this->totalServices = Subscription::where('status', 'active')->count();
        $this->systemHealth = $this->calculateHealth();
        
        // Contagem real de sessões ativas (usuários que interagiram nos últimos 5 minutos)
        $this->onlineUsers = \Illuminate\Support\Facades\DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
            ->whereNotNull('user_id')
            ->count();
            
        // Se der 0 mas o admin está logado, garante pelo menos 1
        if ($this->onlineUsers === 0) {
            $this->onlineUsers = 1;
        }

        // Categorias mais usadas (Top 4)
        $this->popularCategories = \App\Models\Category::withCount('subscriptions')
            ->orderBy('subscriptions_count', 'desc')
            ->take(4)
            ->get()
            ->map(fn($c) => [
                'name' => $c->name,
                'color' => $c->color,
                'icon' => $c->icon,
                'count' => $c->subscriptions_count
            ])
            ->toArray();

        $this->recentUsers = User::latest()
            ->take(5)
            ->get()
            ->map(fn($u) => [
                'name' => $u->name,
                'email' => $u->email,
                'created_at' => $u->created_at->format('d/m/Y'),
                'has_subscriptions' => $u->privacyToken
                    ? Subscription::where('privacy_token', $u->privacyToken->token)->count() > 0
                    : false,
            ])
            ->toArray();
    }

    private function calculateHealth(): int
    {
        $health = 100;
        
        try {
            // 1. Falhas em Jobs (Background tasks)
            $failedJobs = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
            if ($failedJobs > 0) {
                $health -= min(40, $failedJobs * 10);
            }

            // 2. Erros críticos registrados hoje
            $criticalErrors = \Spatie\Activitylog\Models\Activity::where('description', 'like', '%error%')
                ->where('created_at', '>=', now()->startOfDay())
                ->count();
            if ($criticalErrors > 0) {
                $health -= min(30, $criticalErrors * 5);
            }
        } catch (\Exception $e) {
            // Silencia erros se tabelas não existirem
        }

        return max(10, $health);
    }

    public function render()
    {
        return view('livewire.dashboard.admin-dashboard');
    }
}
