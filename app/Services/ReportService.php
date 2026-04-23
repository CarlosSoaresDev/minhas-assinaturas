<?php

namespace App\Services;

use App\Models\Subscription;
use App\Services\CacheService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    public function __construct(private CacheService $cacheService) {}

    public function syncSubscriptions(string $privacyToken): void
    {
        $subs = Subscription::byPrivacyToken($privacyToken)
            ->where('status', 'active')
            ->where('next_billing_date', '<', now()->startOfDay())
            ->get();

        if ($subs->isEmpty()) return;

        $hasChanges = false;
        $now = now()->startOfDay();

        foreach ($subs as $sub) {
            if ($sub->auto_renew) {
                // Renova projetando para o futuro
                $current = \Carbon\Carbon::parse($sub->next_billing_date);
                $safetyCounter = 0;
                
                while ($current < $now && $safetyCounter < 500) {
                    $current = match($sub->billing_cycle) {
                        'monthly' => $current->addMonth(),
                        'quarterly' => $current->addMonths(3),
                        'semiannual' => $current->addMonths(6),
                        'yearly' => $current->addYear(),
                        'custom' => $current->add($sub->custom_cycle_interval ?? 1, $sub->custom_cycle_period ?? 'months'),
                        default => $current->addMonth(),
                    };
                    $safetyCounter++;
                }
                
                $sub->update(['next_billing_date' => $current]);
                $hasChanges = true;
            } else {
                // Cancela pois venceu e não tem renovação
                $sub->update(['status' => 'cancelled']);
                $hasChanges = true;
            }
        }

        if ($hasChanges) {
            $this->cacheService->invalidateUserCache($privacyToken);
        }
    }

    public function monthlyTotal(string $privacyToken): array
    {
        return $this->cacheService->getUserCache($privacyToken, 'monthly_totals_v2', function () use ($privacyToken) {
            $subs = Subscription::byPrivacyToken($privacyToken)->get();
                
            $totals = [];
            foreach ($subs as $sub) {
                // Ignora se estiver cancelada no passado
                if ($sub->cancelled_at && \Carbon\Carbon::parse($sub->cancelled_at)->isPast()) continue;

                $currency = $sub->currency ?? 'BRL';
                if (!isset($totals[$currency])) $totals[$currency] = 0;

                $monthlyValue = match($sub->billing_cycle) {
                    'monthly' => (float) $sub->amount,
                    'quarterly' => (float) $sub->amount / 3,
                    'semiannual' => (float) $sub->amount / 6,
                    'yearly' => (float) $sub->amount / 12,
                    'custom' => $this->calculateCustomMonthlyValue($sub),
                    default => 0.0,
                };
                
                if (is_finite($monthlyValue)) {
                    $totals[$currency] += $monthlyValue;
                }
            }
            return $totals;
        }, 1800);
    }

    private function calculateCustomMonthlyValue(Subscription $sub): float
    {
        if (!$sub->custom_cycle_interval || !$sub->custom_cycle_period) return 0.0;
        
        $val = match($sub->custom_cycle_period) {
            'days' => ((float) $sub->amount / (float) $sub->custom_cycle_interval) * 30,
            'months' => (float) $sub->amount / (float) $sub->custom_cycle_interval,
            'years' => (float) $sub->amount / ((float) $sub->custom_cycle_interval * 12),
            default => 0.0,
        };

        return is_finite($val) ? $val : 0.0;
    }

    public function annualProjection(string $privacyToken): array
    {
        $monthlyTotals = $this->monthlyTotal($privacyToken);
        $projections = [];
        foreach ($monthlyTotals as $currency => $total) {
            $val = $total * 12;
            $projections[$currency] = is_finite($val) ? $val : 0.0;
        }
        return $projections;
    }

    public function spendingByCategory(string $privacyToken, ?string $startDate = null, ?string $endDate = null): array
    {
        $cacheKey = 'by_category_' . ($startDate ?? 'all') . '_' . ($endDate ?? 'all');
        
        return $this->cacheService->getUserCache($privacyToken, $cacheKey, function () use ($privacyToken, $startDate, $endDate) {
            $start = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfMonth();
            $end = $endDate ? Carbon::parse($endDate)->endOfDay() : now()->endOfMonth();

            $subs = Subscription::byPrivacyToken($privacyToken)
                ->with('category')
                ->get();
                
            $result = [];
            foreach ($subs as $sub) {
                $catName = $sub->category ? $sub->category->name : 'Sem Categoria';
                $catColor = $sub->category ? $sub->category->color : '#6c757d';
                
                // Calcula quanto foi gasto especificamente neste intervalo
                $totalInPeriod = 0;
                $current = clone $sub->start_date;
                
                // Se a assinatura foi cancelada, respeitar a data de cancelamento
                $limitDate = ($sub->status === 'cancelled' && $sub->cancelled_at) 
                    ? $sub->cancelled_at 
                    : $end->copy()->addYears(1); // Limite de segurança para projeção

                // Projeta as cobranças e soma as que caem no intervalo
                while ($current <= $end && $current <= $limitDate) {
                    if ($current >= $start) {
                        $totalInPeriod += (float) $sub->amount;
                    }
                    
                    // Avança para a próxima cobrança
                    $current = match($sub->billing_cycle) {
                        'monthly' => $current->addMonth(),
                        'quarterly' => $current->addMonths(3),
                        'semiannual' => $current->addMonths(6),
                        'yearly' => $current->addYear(),
                        'custom' => $current->add($sub->custom_cycle_interval ?? 1, $sub->custom_cycle_period ?? 'months'),
                        default => $current->addCentury(), // Para o loop
                    };

                    if ($sub->billing_cycle === 'custom' && (!$sub->custom_cycle_interval || !$sub->custom_cycle_period)) break;
                }

                if ($totalInPeriod > 0) {
                    if (!isset($result[$catName])) {
                        $result[$catName] = ['name' => $catName, 'color' => $catColor, 'amount' => 0.0];
                    }
                    $result[$catName]['amount'] += $totalInPeriod;
                }
            }
            
            // Ordena por maior gasto
            usort($result, fn($a, $b) => $b['amount'] <=> $a['amount']);
            
            return array_values($result);
        });
    }

    /**
     * Retorna array de assinaturas expirando em breve.
     * Armazena como array puro no cache para evitar problemas de serialização.
     */
    public function expiringSoon(string $privacyToken, int $days = 30): Collection
    {
        $cacheKey = 'expiring_soon_v4_' . $days;
        
        $data = $this->cacheService->getUserCache($privacyToken, $cacheKey, function () use ($privacyToken, $days) {
            $subs = Subscription::byPrivacyToken($privacyToken)
                ->where('status', 'active')
                ->whereNotNull('next_billing_date')
                ->get();
            
            $now = now()->startOfDay();
            $limit = now()->addDays($days)->endOfDay();
            $upcoming = [];

            foreach ($subs as $sub) {
                $nextDate = \Carbon\Carbon::parse($sub->next_billing_date);
                
                if ($nextDate < $now) {
                    $current = clone $nextDate;
                    $safetyCounter = 0;
                    while ($current < $now && $safetyCounter < 500) {
                        $current = match($sub->billing_cycle) {
                            'monthly' => $current->addMonth(),
                            'quarterly' => $current->addMonths(3),
                            'semiannual' => $current->addMonths(6),
                            'yearly' => $current->addYear(),
                            'custom' => $current->add($sub->custom_cycle_interval ?? 1, $sub->custom_cycle_period ?? 'months'),
                            default => $current->addMonth(),
                        };
                        $safetyCounter++;
                    }
                    $nextDate = $current;
                }

                if ($nextDate >= $now && $nextDate <= $limit) {
                    $upcoming[] = [
                        'id' => $sub->id,
                        'name' => $sub->name,
                        'amount' => $sub->amount,
                        'next_billing_date' => $nextDate->toISOString(),
                    ];
                }
            }

            usort($upcoming, fn($a, $b) => $a['next_billing_date'] <=> $b['next_billing_date']);

            return $upcoming; // Retorna array para o cache
        }, 600);

        return collect($data); // Converte para Collection ao sair do cache
    }

    /**
     * Calcula o histórico de gastos com base no período de vigência das assinaturas.
     */
    public function getSpendingHistory(string $privacyToken, \Carbon\CarbonInterface $startDate, \Carbon\CarbonInterface $endDate, string $aggregation = 'monthly'): array
    {
        $cacheKey = "spending_history_{$aggregation}_{$startDate->format('Ymd')}_{$endDate->format('Ymd')}";
        
        return $this->cacheService->getUserCache($privacyToken, $cacheKey, function () use ($privacyToken, $startDate, $endDate, $aggregation) {
            $subs = Subscription::byPrivacyToken($privacyToken)->get();
            $currencies = $subs->pluck('currency')->unique()->filter()->values();
            if ($currencies->isEmpty()) $currencies = collect(['BRL']);

            $history = [];
            $periods = [];
            
            $current = \Carbon\Carbon::parse($startDate)->startOfMonth();
            $limit = \Carbon\Carbon::parse($endDate)->endOfMonth();
            $today = now()->startOfMonth();

            while ($current <= $limit) {
                $periodKey = $this->getPeriodKey($current, $aggregation);
                
                if (!in_array($periodKey, $periods)) {
                    $periods[] = $periodKey;
                }

                foreach ($currencies as $currency) {
                    if (!isset($history[$currency][$periodKey])) {
                        $history[$currency][$periodKey] = 0.0;
                    }
                }

                foreach ($subs as $sub) {
                    $subStart = $sub->start_date ? \Carbon\Carbon::parse($sub->start_date)->startOfMonth() : null;
                    $subEnd = $sub->cancelled_at ? \Carbon\Carbon::parse($sub->cancelled_at)->endOfMonth() : ($sub->end_date ? \Carbon\Carbon::parse($sub->end_date)->endOfMonth() : null);

                    if ($subStart && $current < $subStart) continue;
                    if ($subEnd && $current > $subEnd) continue;

                    $currency = $sub->currency ?? 'BRL';
                    $val = (float) $this->calculateMonthlyValue($sub);
                    $history[$currency][$periodKey] += is_finite($val) ? $val : 0.0;
                }

                $current = $current->addMonth();
            }

            $datasets = [];
            foreach ($currencies as $currency) {
                $values = [];
                foreach ($periods as $period) {
                    $val = (float)($history[$currency][$period] ?? 0);
                    $values[] = round($val, 2);
                }
                $datasets[] = [
                    'currency' => $currency,
                    'values' => $values
                ];
            }

            // Calcula o index do hoje baseado na lista final de períodos
            $todayKey = $this->getPeriodKey(now(), $aggregation);
            $todayIndex = array_search($todayKey, $periods);

            return [
                'labels' => $periods,
                'datasets' => $datasets,
                'todayIndex' => $todayIndex !== false ? (int)$todayIndex : -1
            ];
        });
    }

    private function getPeriodKey(\Carbon\CarbonInterface $date, string $aggregation): string
    {
        $key = match($aggregation) {
            'monthly' => $date->translatedFormat('M/y'),
            'quarterly' => 'Q' . ceil($date->month / 3) . ' ' . $date->year,
            'semiannual' => 'S' . ceil($date->month / 6) . ' ' . $date->year,
            'yearly' => (string) $date->year,
            default => $date->translatedFormat('M/y'),
        };

        // Remove pontos (ex: abr. -> abr) que variam entre SOs e podem quebrar o match
        return mb_strtolower(str_replace('.', '', $key));
    }

    private function calculateMonthlyValue(Subscription $sub): float
    {
        return match($sub->billing_cycle) {
            'monthly' => (float) $sub->amount,
            'quarterly' => (float) $sub->amount / 3,
            'semiannual' => (float) $sub->amount / 6,
            'yearly' => (float) $sub->amount / 12,
            'custom' => $this->calculateCustomMonthlyValue($sub),
            default => (float) $sub->amount,
        };
    }
}
