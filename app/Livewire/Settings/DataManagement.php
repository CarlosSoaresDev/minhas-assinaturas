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

        try {
            $path = $this->csvFile->getRealPath();
            $content = file_get_contents($path);
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
            
            $tempStream = fopen('php://temp', 'r+');
            fwrite($tempStream, $content);
            rewind($tempStream);

            $firstLine = fgets($tempStream);
            rewind($tempStream);
            $delimiter = str_contains($firstLine, ';') ? ';' : ',';

            $importedCount = 0;
            $header = fgetcsv($tempStream, 0, $delimiter);
            
            $importService = app(\App\Services\SubscriptionImportService::class);

            while (($row = fgetcsv($tempStream, 0, $delimiter)) !== FALSE) {
                if (count($row) === 1 && $row[0] === null) continue;
                
                $result = $importService->importRow($token, $row, true);
                if ($result['status'] === 'imported') {
                    $importedCount++;
                }
            }
            fclose($tempStream);

            $this->importStatus = "{$importedCount} assinaturas importadas com sucesso!";
            app(\App\Services\CacheService::class)->invalidateUserCache($token);
            
            activity()->event('csv_import')->log("Usuário importou {$importedCount} assinaturas via CSV.");
        } catch (\Exception $e) {
            session()->flash('error', 'Erro ao importar CSV: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.settings.data-management');
    }
}
