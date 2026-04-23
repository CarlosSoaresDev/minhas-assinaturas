<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\AlertService;
use App\Notifications\ItemExpiringNotification;
use Illuminate\Support\Facades\Log;

class SendAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(AlertService $alertService): void
    {
        Log::info('Iniciando rotina de verificação de alertas de vencimento...');
        
        // Timeframes padrão do sistema
        $defaultTimeframes = [3, 1, 0];
        $totalAlertsSent = 0;

        // Pegamos todas as assinaturas ativas para processamento dinâmico por usuário
        $subscriptions = \App\Models\Subscription::where('status', 'active')
            ->whereNotNull('next_billing_date')
            ->get();

        foreach ($subscriptions as $subscription) {
            $user = $alertService->getUserByPrivacyToken($subscription->privacy_token);
            
            // Se o usuário não existe ou desativou alertas, ignoramos
            if (!$user || !$user->alerts_enabled) {
                continue;
            }

            // Calculamos os dias restantes
            $daysUntil = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($subscription->next_billing_date)->startOfDay(), false);
            
            // Definimos quais gatilhos este usuário deve disparar
            $userTimeframes = array_unique(array_merge($defaultTimeframes, [$user->alert_days_before]));

            foreach ($userTimeframes as $daysBefore) {
                if ($daysUntil == $daysBefore) {
                    if (!$alertService->wasAlertAlreadySent($subscription, $daysBefore)) {
                        // Envia Notificação
                        $user->notify(new ItemExpiringNotification(
                            $subscription->name,
                            $daysBefore,
                            $subscription->amount,
                            $subscription->id,
                            $subscription->service_url
                        ));

                        // Marca como enviado
                        $alertService->markAlertSent($subscription, $daysBefore);
                        $totalAlertsSent++;
                    }
                }
            }
        }

        Log::info("Rotina finalizada. {$totalAlertsSent} novos alertas gerados no sistema.");
        
        // Generic Activity Log for Admin Auditing
        activity()
            ->event('system_job')
            ->log("Job SendAlertsJob executado. {$totalAlertsSent} notificações geradas.");
    }
}
