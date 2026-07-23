<?php

use App\Jobs\ProcessarEfdImportacaoJob;
use App\Models\EfdImportacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * Guarda de tipo do motor (defesa em profundidade): mesmo que o upload rotule errado, o
 * Job escolhe o driver pelo CONTEÚDO discriminado — nunca dropar A/F/M ou C/D em silêncio
 * por rodar o driver errado (classe do bug UTIDA).
 */
function importacaoComRotulo(string $rotulo, string $sped): array
{
    $user = User::factory()->create();
    $clienteId = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'razao_social' => 'EMPRESA', 'documento' => '11222333000181',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $clienteId, 'tipo_efd' => $rotulo,
        'filename' => 'x.txt', 'arquivo_base64' => json_encode($sped),
        'status' => 'processando', 'iniciado_em' => now()->subMinute(),
    ]);

    return [$user, $imp];
}

function spedFiscalMinimo(): string
{
    $chave = str_pad('35240611222333000181550010000001231', 44, '0');

    return implode("\n", [
        '|0000|016|0|01022026|28022026|EMPRESA|11222333000181|MG|1|3106200|0|A|0|0|',
        '|0150|FOR1|Fornecedor|01058|22333444000195||123||rua|1||centro|',
        '|C100|0|0|FOR1|55|00|1|123|'.$chave.'|01022026|01022026|500,00|0|0|',
        '|C170|1|MERC01|Mercadoria|2|UN|500,00|0|0|000|5102|',
        '|C190|00|5102|18,00|500,00|500,00|90,00|0|0|0|0|',
        '|E100|01022026|28022026|',
        '|E110|90,00|0|0|0|0|0|0|0|0|90,00|0|90,00|0|0|',
        '|9999|8|',
    ]);
}

function spedContribMinimo(): string
{
    return implode("\n", [
        '|0000|006|0|01022026|28022026|EMPRESA|11222333000181|MG|1|3106200|0|0|',
        '|0110|2|1|1|2|',
        '|0150|FOR7|Servico|01058|22333444000195||123||rua|1||centro|',
        '|A100|1|0|FOR7|00|1|0|500||01022026|01022026|1000,00|0|0|900,00|5,85|900,00|27,00|0|0|410,50|',
        '|A170|1|SERV01|Consultoria|1000,00|0|00|0|01|900,00|0,65|5,85|01|900,00|3,00|27,00|3.1.1|CC1|',
        '|M200|100,00|0|0|100,00|0|0|100,00|0|0|0|0|7166,99|',
        '|M600|500,00|0|0|500,00|0|0|500,00|0|0|0|0|33078,20|',
        '|9999|8|',
    ]);
}

it('EFD fiscal rotulado como PIS/COFINS: motor redireciona e processa como fiscal', function () {
    // Rótulo ERRADO de propósito. O conteúdo é ICMS/IPI (C190/E100).
    [$user, $imp] = importacaoComRotulo('EFD PIS/COFINS', spedFiscalMinimo());

    ProcessarEfdImportacaoJob::dispatchSync($imp->id, null);

    $imp->refresh();
    expect($imp->tipo_efd)->toBe('EFD ICMS/IPI');            // corrigido pelo conteúdo
    expect($imp->status)->toBe('concluido');

    // Processou como fiscal: nota origem 'fiscal', consolidado C190, apuração ICMS.
    $nota = DB::table('efd_notas')->where('importacao_id', $imp->id)->first();
    expect($nota->origem_arquivo)->toBe('fiscal');
    expect(DB::table('efd_notas_consolidados')->where('user_id', $user->id)->count())->toBe(1);
    expect(DB::table('efd_apuracoes_icms')->where('importacao_id', $imp->id)->count())->toBe(1);
    // NÃO caiu no fluxo contrib.
    expect(DB::table('efd_apuracoes_contribuicoes')->where('importacao_id', $imp->id)->count())->toBe(0);
});

it('EFD PIS/COFINS rotulado como ICMS/IPI: motor redireciona e processa como contrib', function () {
    [$user, $imp] = importacaoComRotulo('EFD ICMS/IPI', spedContribMinimo());

    ProcessarEfdImportacaoJob::dispatchSync($imp->id, null);

    $imp->refresh();
    expect($imp->tipo_efd)->toBe('EFD PIS/COFINS');          // corrigido
    expect($imp->status)->toBe('concluido');

    $nota = DB::table('efd_notas')->where('importacao_id', $imp->id)->where('modelo', '00')->first();
    expect($nota->origem_arquivo)->toBe('contribuicoes');
    expect(DB::table('efd_apuracoes_contribuicoes')->where('importacao_id', $imp->id)->count())->toBe(1);
    expect(DB::table('efd_apuracoes_icms')->where('importacao_id', $imp->id)->count())->toBe(0);
});

it('rótulo correto: não altera tipo, processa normal', function () {
    [$user, $imp] = importacaoComRotulo('EFD ICMS/IPI', spedFiscalMinimo());

    ProcessarEfdImportacaoJob::dispatchSync($imp->id, null);

    $imp->refresh();
    expect($imp->tipo_efd)->toBe('EFD ICMS/IPI');
    expect($imp->status)->toBe('concluido');
});
