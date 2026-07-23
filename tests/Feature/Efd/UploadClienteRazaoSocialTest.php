<?php

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/**
 * Ao auto-criar/reusar o cliente a partir do CNPJ do SPED, o upload deve nomeá-lo com a
 * razão social e a UF do registro 0000 — senão o cliente nasce só com o documento (bug
 * visto na importação real #414: "UTIDA FOODS" ficou razao_social=NULL). Vale pros dois
 * motores; testa via n8n (baseline) por ser o caminho de upload padrão.
 */
beforeEach(function () {
    config([
        'services.webhook.importacao_efd_fiscal_url' => 'https://n8n.example.com/icms',
        'services.webhook.importacao_efd_contribuicoes_url' => 'https://n8n.example.com/contrib',
        'efd.motor' => 'n8n',
        'efd.motor_fiscal' => null,
        'efd.motor_contrib' => null,
    ]);
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    Http::fake(['*' => Http::response(['ok' => true], 200)]);
});

// 0000 fiscal: |0000|COD_VER|COD_FIN|DT_INI|DT_FIN|NOME|CNPJ|CPF|UF|IE|COD_MUN|...
function spedComRazao(string $cnpj = '09305162000293', string $nome = 'UTIDA FOODS COMERCIO DE ALIMENTOS LTDA', string $uf = 'MS'): string
{
    return "|0000|020|0|01012026|31012026|{$nome}|{$cnpj}||{$uf}|289578434|5003702|990141600||A|1|\r\n".
        "|C190|00|5102|18,00|100,00|100,00|18,00|0|0|0|0|\r\n".
        '|9999|3|';
}

function uploadFiscal(string $sped): \Illuminate\Testing\TestResponse
{
    return test()->postJson('/app/importacao/efd/importar-txt', [
        'tipo_efd' => 'EFD ICMS/IPI',
        'arquivo' => UploadedFile::fake()->createWithContent('sped.txt', $sped),
    ]);
}

it('auto-cria o cliente com toda a identidade do 0000 (razão, UF, IE, município, IM)', function () {
    uploadFiscal(spedComRazao())->assertOk();

    $cli = Cliente::where('user_id', $this->user->id)->where('documento', '09305162000293')->first();
    expect($cli)->not->toBeNull();
    expect($cli->razao_social)->toBe('UTIDA FOODS COMERCIO DE ALIMENTOS LTDA');
    expect($cli->uf)->toBe('MS');
    expect($cli->inscricao_estadual)->toBe('289578434');    // IE (c[10])
    expect($cli->codigo_municipal)->toBe('5003702');         // COD_MUN IBGE (c[11])
    expect($cli->inscricao_municipal)->toBe('990141600');    // IM (c[12])
});

it('backfill: cliente pré-existente sem razão social é preenchido pelo SPED', function () {
    $cli = Cliente::create([
        'user_id' => $this->user->id, 'tipo_pessoa' => 'PJ',
        'documento' => '09305162000293', 'is_empresa_propria' => false, 'ativo' => true,
    ]);
    expect($cli->razao_social)->toBeNull();

    uploadFiscal(spedComRazao())->assertOk();

    expect($cli->fresh()->razao_social)->toBe('UTIDA FOODS COMERCIO DE ALIMENTOS LTDA');
    expect($cli->fresh()->uf)->toBe('MS');
});

it('não sobrescreve razão social já cadastrada (consulta CNPJ é fonte mais forte)', function () {
    $cli = Cliente::create([
        'user_id' => $this->user->id, 'tipo_pessoa' => 'PJ',
        'documento' => '09305162000293', 'razao_social' => 'RAZAO OFICIAL RFB LTDA',
        'uf' => 'MS', 'is_empresa_propria' => false, 'ativo' => true,
    ]);

    uploadFiscal(spedComRazao(nome: 'nome divergente do sped'))->assertOk();

    expect($cli->fresh()->razao_social)->toBe('RAZAO OFICIAL RFB LTDA'); // preservada
});

it('detector extrai identidade do 0000 fiscal (IE, município IBGE, IM, SUFRAMA)', function () {
    $fiscal = app(\App\Services\SpedDetectorService::class)->extrairCabecalho(spedComRazao());

    expect($fiscal['razao_social'])->toBe('UTIDA FOODS COMERCIO DE ALIMENTOS LTDA');
    expect($fiscal['uf'])->toBe('MS');
    expect($fiscal['cnpj'])->toBe('09305162000293');
    expect($fiscal['inscricao_estadual'])->toBe('289578434');
    expect($fiscal['codigo_municipal'])->toBe('5003702');
    expect($fiscal['inscricao_municipal'])->toBe('990141600');
});

it('detector no PIS/COFINS: razão/UF/município, mas SEM IE nem IM (não existem no 0000 contrib)', function () {
    // |0000|COD_VER|TIPO_ESCRIT|IND_SIT|NUM_REC|DT_INI|DT_FIN|NOME|CNPJ|UF|COD_MUN|SUFRAMA|...
    $contrib = app(\App\Services\SpedDetectorService::class)->extrairCabecalho(
        "|0000|006|0|||01022026|28022026|EMPRESA CONTRIB LTDA|11222333000181|SP|3550308||||\r\n".
        "|M200|1|\r\n|9999|3|"
    );

    expect($contrib['tipo'])->toBe('EFD PIS/COFINS');
    expect($contrib['razao_social'])->toBe('EMPRESA CONTRIB LTDA');
    expect($contrib['uf'])->toBe('SP');
    expect($contrib['cnpj'])->toBe('11222333000181');
    expect($contrib['codigo_municipal'])->toBe('3550308');
    expect($contrib['inscricao_estadual'])->toBeNull();   // não existe no 0000 contrib
    expect($contrib['inscricao_municipal'])->toBeNull();
});

it('backfill preenche também IE/município/IM quando faltam', function () {
    $cli = Cliente::create([
        'user_id' => $this->user->id, 'tipo_pessoa' => 'PJ',
        'documento' => '09305162000293', 'razao_social' => 'JA TINHA RAZAO',
        'is_empresa_propria' => false, 'ativo' => true,
    ]);

    uploadFiscal(spedComRazao())->assertOk();

    $cli->refresh();
    expect($cli->razao_social)->toBe('JA TINHA RAZAO');       // preservada
    expect($cli->inscricao_estadual)->toBe('289578434');       // preenchida (estava vazia)
    expect($cli->codigo_municipal)->toBe('5003702');
    expect($cli->inscricao_municipal)->toBe('990141600');
});
