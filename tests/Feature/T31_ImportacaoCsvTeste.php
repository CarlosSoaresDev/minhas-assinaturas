<?php

namespace Tests\Feature;

use App\Livewire\Subscriptions\Index as SubIndex;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class T31_ImportacaoCsvTeste extends TestCase
{
    use RefreshDatabase;

    public function test_138_preparar_importacao_com_bom_e_ponto_e_virgula_cria_registros_e_invalida_cache()
    {
        $user = User::factory()->create();
        $token = $user->privacyToken?->token;

        // existing subscription (duplicate)
        Subscription::create([
            'privacy_token' => $token,
            'name' => 'Netflix',
            'billing_cycle' => 'monthly',
            'amount' => 39.90,
            'currency' => 'BRL',
            'start_date' => now(),
            'status' => 'active',
        ]);

        // CSV with BOM and semicolon delimiter; header + two rows (one duplicate, one new)
        $csv = chr(0xEF) . chr(0xBB) . chr(0xBF) .
            "Nome;URL;Valor;Moeda;Ciclo;Intervalo_Custom;Periodo_Custom;Categoria;Início;Vencimento;Status;Auto_Renew;Notas\n" .
            "Netflix;https://netflix.test;39,90;BRL;monthly;;;;;active;Sim;\n" .
            "NewService;https://new.test;12,50;BRL;monthly;;;;;active;Sim;Observacao\n";

        $file = UploadedFile::fake()->createWithContent('import.csv', $csv);

        Livewire::actingAs($user)
            ->test(SubIndex::class)
            ->set('csvFile', $file)
            ->call('prepareImport')
            ->assertSet('showImportModal', true);

        // session temp data exists
        $this->assertTrue(session()->has('temp_import_data_' . $user->id));

        // Confirm import
        Livewire::actingAs($user)
            ->test(SubIndex::class)
            ->call('confirmImport');

        // New service imported
        $this->assertDatabaseHas('subscriptions', ['privacy_token' => $token, 'name' => 'NewService']);
        // Duplicate should not be duplicated (only one Netflix)
        $this->assertEquals(1, Subscription::where('privacy_token', $token)->where('name', 'Netflix')->count());

        // Cache version incremented to 2
        $this->assertSame(2, (int) Cache::get("user_{$token}_cache_version"));
    }

    public function test_139_importacao_rejeita_arquivo_grande_e_trata_valores_de_formula()
    {
        $user = User::factory()->create();
        $token = $user->privacyToken?->token;

        // Create CSV > 1MB
        $big = str_repeat('a', 1100 * 1024); // ~1.1MB
        $fileBig = UploadedFile::fake()->createWithContent('big.csv', $big);

        $component = Livewire::actingAs($user)->test(SubIndex::class)->set('csvFile', $fileBig)->call('prepareImport');

        // Validation should fail; Livewire exposes errors via ->errors()
        $errors = $component->errors();
        $this->assertNotEmpty($errors);

        // Now test formula value stored as numeric zero
        $csv2 = "Nome,Valor\n";
        $csv2 .= "FormulaService,=SUM(1,1)\n"; // non-numeric -> cast to 0
        $file2 = UploadedFile::fake()->createWithContent('f.csv', $csv2);

        Livewire::actingAs($user)
            ->test(SubIndex::class)
            ->set('csvFile', $file2)
            ->call('prepareImport');

        Livewire::actingAs($user)
            ->test(SubIndex::class)
            ->call('confirmImport');

        $this->assertDatabaseHas('subscriptions', ['privacy_token' => $token, 'name' => 'FormulaService']);
        $this->assertEquals(0.0, (float) Subscription::where('privacy_token', $token)->where('name', 'FormulaService')->first()->amount);
    }
}
