<?php

namespace Tests\Feature;

use App\Livewire\Subscriptions\Index as SubIndex;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class T32_ImportacaoCsvCasosExtremosTeste extends TestCase
{
    use RefreshDatabase;

    public function test_140_ponto_e_virgula_dentro_de_campo_com_aspas_analisado_corretamente()
    {
        $user = User::factory()->create();
        $token = $user->privacyToken?->token;

        $csv = "Nome;URL;Valor;Moeda;Ciclo;Intervalo_Custom;Periodo_Custom;Categoria;Início;Vencimento;Status;Auto_Renew;Notas\n" .
               '"Complex;Name";https://c.test;10,00;BRL;monthly;;;;;active;Sim;Note\n';

        $file = UploadedFile::fake()->createWithContent('c.csv', $csv);

        Livewire::actingAs($user)
            ->test(SubIndex::class)
            ->set('csvFile', $file)
            ->call('prepareImport')
            ->assertSet('showImportModal', true);

        Livewire::actingAs($user)
            ->test(SubIndex::class)
            ->call('confirmImport');

        $this->assertDatabaseHas('subscriptions', ['privacy_token' => $token, 'name' => 'Complex;Name']);
    }

    public function test_141_campo_com_aspas_e_barra_n_literal_mantem_literal_e_importa()
    {
        $user = User::factory()->create();
        $token = $user->privacyToken?->token;

        // Name contains literal backslash-n inside quotes
        $csv = "Nome;Valor\n" .
               '"Line\\nBreak";5,00\n';

        $file = UploadedFile::fake()->createWithContent('d.csv', $csv);

        Livewire::actingAs($user)
            ->test(SubIndex::class)
            ->set('csvFile', $file)
            ->call('prepareImport')
            ->assertSet('showImportModal', true);

        Livewire::actingAs($user)
            ->test(SubIndex::class)
            ->call('confirmImport');

        $this->assertDatabaseHas('subscriptions', ['privacy_token' => $token, 'name' => 'Line\\nBreak']);
    }

    public function test_142_delimitadores_mistos_cabecalho_com_virgula_e_linhas_com_ponto_e_virgula_processado_sem_excecao()
    {
        $user = User::factory()->create();
        $token = $user->privacyToken?->token;

        // Header uses comma, rows use semicolon
        $csv = "Nome,Valor\n" .
               "Mixed;Delimiter;12,00\n";

        $file = UploadedFile::fake()->createWithContent('mix.csv', $csv);

        Livewire::actingAs($user)
            ->test(SubIndex::class)
            ->set('csvFile', $file)
            ->call('prepareImport')
            ->assertSet('showImportModal', true);

        Livewire::actingAs($user)
            ->test(SubIndex::class)
            ->call('confirmImport');

        // Should create a record; name may contain the semicolon and extra piece because header used comma
        $this->assertTrue(Subscription::where('privacy_token', $token)
            ->where('name', 'like', 'Mixed;Delimiter%')
            ->exists());
    }

    public function test_143_celula_com_formula_excel_no_nome_e_valor_importada_como_literal_ou_zero()
    {
        $user = User::factory()->create();
        $token = $user->privacyToken?->token;

        $csv = "Nome;Valor\n" .
               "=CMD|" . "'/C echo hi'!A0;=SUM(1,1)\n"; // name starting with '=' and a formula value

        $file = UploadedFile::fake()->createWithContent('f.csv', $csv);

        Livewire::actingAs($user)
            ->test(SubIndex::class)
            ->set('csvFile', $file)
            ->call('prepareImport');

        Livewire::actingAs($user)
            ->test(SubIndex::class)
            ->call('confirmImport');

        // Nome deve ser neutralizado com apóstrofo no início
        $sub = Subscription::where('privacy_token', $token)->where('name', "'=CMD|'/C echo hi'!A0")->first();
        $this->assertNotNull($sub);
        $this->assertEquals(0.0, (float) $sub->amount);
    }

    public function test_144_importacao_url_invalida_ou_insegura_e_armazenada_como_nula()
    {
        $user = User::factory()->create();
        $token = $user->privacyToken?->token;

        $csv = "Nome;URL;Valor;Moeda;Ciclo;Intervalo_Custom;Periodo_Custom;Categoria;Início;Vencimento;Status;Auto_Renew;Notas\n" .
               "SvcJs;javascript:alert(1);9,99;BRL;monthly;;;;;active;Sim;nota\n" .
               "SvcFtp;ftp://example.test/a;9,99;BRL;monthly;;;;;active;Sim;nota\n";

        $file = UploadedFile::fake()->createWithContent('unsafe-url.csv', $csv);

        Livewire::actingAs($user)
            ->test(SubIndex::class)
            ->set('csvFile', $file)
            ->call('prepareImport');

        Livewire::actingAs($user)
            ->test(SubIndex::class)
            ->call('confirmImport');

        $this->assertDatabaseHas('subscriptions', [
            'privacy_token' => $token,
            'name' => 'SvcJs',
            'service_url' => null,
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'privacy_token' => $token,
            'name' => 'SvcFtp',
            'service_url' => null,
        ]);
    }

    public function test_145_importacao_url_https_valida_e_preservada()
    {
        $user = User::factory()->create();
        $token = $user->privacyToken?->token;

        $csv = "Nome;URL;Valor;Moeda;Ciclo;Intervalo_Custom;Periodo_Custom;Categoria;Início;Vencimento;Status;Auto_Renew;Notas\n" .
               "SvcHttps;https://example.com/path?q=1;14,90;BRL;monthly;;;;;active;Sim;ok\n";

        $file = UploadedFile::fake()->createWithContent('ok-url.csv', $csv);

        Livewire::actingAs($user)
            ->test(SubIndex::class)
            ->set('csvFile', $file)
            ->call('prepareImport');

        Livewire::actingAs($user)
            ->test(SubIndex::class)
            ->call('confirmImport');

        $this->assertDatabaseHas('subscriptions', [
            'privacy_token' => $token,
            'name' => 'SvcHttps',
            'service_url' => 'https://example.com/path?q=1',
        ]);
    }
}
