<?php

namespace App\Livewire\Dashboard;

use App\Services\ReportService;
use Illuminate\Support\Collection;
use Livewire\Component;

class UserDashboard extends Component
{
    public array $monthlyTotal = [];
    public array $annualProjection = [];
    public array $expiringSoonData = [];
    public array $categoryData = [];
    
    // Gráfico de Histórico
    public $startDate;
    public $endDate;
    public $aggregation = 'monthly';
    public array $spendingHistory = [];
    public int $todayIndex = -1;

    public function mount()
    {
        $this->startDate = now()->subMonths(11)->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->loadData();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['startDate', 'endDate', 'aggregation'])) {
            // Se mudou o filtro, limpamos o cache para garantir dados frescos e recálculo do todayIndex
            $token = auth()->user()->privacyToken?->token;
            if ($token) {
                app(\App\Services\CacheService::class)->invalidateUserCache($token);
            }
            $this->loadData();
        }
    }

    public function refreshData(\App\Services\CacheService $cacheService)
    {
        $user = auth()->user();
        $privacyToken = $user->privacyToken;
        
        if ($privacyToken) {
            $cacheService->invalidateUserCache($privacyToken->token);
        }

        $this->loadData();
    }

    public function loadData()
    {
        try {
            $reportService = app(\App\Services\ReportService::class);
            $user = auth()->user();
            $privacyToken = $user->privacyToken;

            if (!$privacyToken) {
                return;
            }

            $token = $privacyToken->token;
            $reportService->syncSubscriptions($token);

            $this->monthlyTotal = $reportService->monthlyTotal($token);
            $this->annualProjection = $reportService->annualProjection($token);
            $this->categoryData = $reportService->spendingByCategory($token, $this->startDate, $this->endDate);
            
            // Histórico de Gastos
            $historyData = $reportService->getSpendingHistory(
                $token, 
                \Carbon\Carbon::parse($this->startDate), 
                \Carbon\Carbon::parse($this->endDate),
                $this->aggregation
            );
            $this->spendingHistory = $historyData;
            $this->todayIndex = (int)($historyData['todayIndex'] ?? -1);

            $expiring = $reportService->expiringSoon($privacyToken->token, 30);
            $this->expiringSoonData = $expiring instanceof \Illuminate\Support\Collection
                ? $expiring->map(fn($item) => [
                    'name' => $item['name'] ?? '',
                    'amount' => $item['amount'] ?? 0,
                    'next_billing_date' => isset($item['next_billing_date']) ? \Carbon\Carbon::parse($item['next_billing_date'])->format('d/m/Y') : '-',
                ])->toArray()
                : [];
                
            $this->dispatch('update-chart', $this->categoryData);
            $this->dispatch('update-spending-chart', $this->spendingHistory);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro no Dashboard: " . $e->getMessage());
            $this->spendingHistory = ['labels' => [], 'datasets' => [], 'todayIndex' => -1];
            session()->flash('error', 'Erro ao carregar dados: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.dashboard.user-dashboard');
    }
}
