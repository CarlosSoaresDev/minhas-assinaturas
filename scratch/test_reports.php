<?php

use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::whereHas('privacyToken')->first();
if (!$user) {
    echo "Nenhum usuário com token encontrado.\n";
    exit;
}

$token = $user->privacyToken->token;
$reportService = app(ReportService::class);

$start = Carbon::now()->subYears(2)->startOfYear();
$end = Carbon::now()->addYears(2)->endOfYear();

$aggregations = ['monthly', 'quarterly', 'semiannual', 'yearly'];

foreach ($aggregations as $agg) {
    echo "\n--- TESTANDO AGREGAÇÃO: $agg ---\n";
    try {
        $data = $reportService->getSpendingHistory($token, $start, $end, $agg);
        echo "Labels: " . implode(', ', $data['labels']) . "\n";
        echo "Today Index: " . $data['todayIndex'] . "\n";
        foreach ($data['datasets'] as $ds) {
            echo "Currency: {$ds['currency']} | Values Count: " . count($ds['values']) . " | First 3 values: " . implode(', ', array_slice($ds['values'], 0, 3)) . "\n";
        }
    } catch (\Exception $e) {
        echo "ERRO: " . $e->getMessage() . "\n";
    }
}
