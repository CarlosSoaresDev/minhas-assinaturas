<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Response;

class DataManagement extends Component
{
    use WithFileUploads;

    public $csvFile;
    public $importStatus = '';

    public function downloadLgpdData()
    {
        $token = auth()->user()->privacyToken?->token;
        if (!$token) {
            session()->flash('error', 'Token de privacidade não encontrado.');
            return;
        }

        $user = auth()->user();
        $subscriptions = Subscription::byPrivacyToken($token)->with('category')->get();

        $data = [
            'personal_info' => [
                'name' => $user->name,
                'email' => $user->email,
                'registered_at' => $user->created_at->toIso8601String(),
                'lgpd_consent_at' => $user->lgpd_consent_at?->toIso8601String(),
            ],
            'subscriptions' => $subscriptions->map(function($sub) {
                return [
                    'name' => $sub->name,
                    'amount' => $sub->amount,
                    'currency' => $sub->currency,
                    'cycle' => $sub->billing_cycle,
                    'category' => $sub->category->name ?? 'Sem categoria',
                    'start_date' => $sub->start_date->toDateString(),
                    'next_billing_date' => $sub->next_billing_date?->toDateString(),
                    'status' => $sub->status,
                    'auto_renew' => $sub->auto_renew,
                ];
            })->toArray()
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        // Log auditing for LGPD
        activity()->event('lgpd_export')->log('Usuário exportou seus dados pessoais (LGPD).');

        return response()->streamDownload(function () use ($json) {
            echo $json;
        }, 'Meus_Dados_SignManager.json', [
            'Content-Type' => 'application/json',
        ]);
    }

    public function importCsv()
    {
        $this->validate([
            'csvFile' => 'required|mimes:csv,txt|max:1024', // Max 1MB
        ]);

        $token = auth()->user()->privacyToken?->token;
        if (!$token) return;

        $path = $this->csvFile->getRealPath();
        $data = array_map('str_getcsv', file($path));
        
        $importedCount = 0;
        $header = array_shift($data);

        // Simple CSV assumption: Nome, Valor, Ciclo (monthly, yearly)
        foreach ($data as $row) {
            if (count($row) >= 2) {
                Subscription::create([
                    'privacy_token' => $token,
                    'name' => $row[0],
                    'amount' => (float) str_replace(',', '.', $row[1] ?? 0),
                    'billing_cycle' => $row[2] ?? 'monthly',
                    'start_date' => now(),
                    'next_billing_date' => now()->addMonth(),
                    'status' => 'active',
                ]);
                $importedCount++;
            }
        }

        $this->importStatus = "{$importedCount} assinaturas importadas com sucesso!";
        app(\App\Services\CacheService::class)->invalidateUserCache($token);
        
        activity()->event('csv_import')->log("Usuário importou {$importedCount} assinaturas via CSV.");
    }

    public function requestAccountDeletion()
    {
        // Fase 5: Exclusão de conta (Soft Delete)
        $user = auth()->user();

        // Evita remover o último administrador e bloquear o sistema.
        if ($user->hasRole('admin') && User::role('admin')->count() <= 1) {
            session()->flash('error', 'Não é possível excluir o último administrador do sistema. Crie outro administrador antes de excluir esta conta.');
            return;
        }
        
        // Dispara auditoria antes de excluir
        activity()->event('account_deletion')->log('Usuário solicitou exclusão permanente da conta.');

        // O Soft Delete já foi configurado no model.
        // Ao invés de deletar e deslogar aqui na mão, apenas invocamos delete
        $user->delete();

        // O redirecionamento e logout real deveriam ser feitos com auth()->logout(), 
        // mas para simplificar o livewire, redirecionamos forçadamente.
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/');
    }

    public function render()
    {
        return view('livewire.settings.data-management');
    }
}
