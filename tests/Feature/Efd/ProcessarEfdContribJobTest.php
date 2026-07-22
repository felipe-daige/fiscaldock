<?php

use App\Jobs\ProcessarEfdImportacaoJob;
use App\Models\EfdImportacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * E2E do motor Laravel PIS/COFINS (F5): roda o Job inteiro (mesmo esqueleto do fiscal, só
 * troca o driver) contra um SPED EFD Contribuições sintético e prova que A100/A170 (NFS-e),
 * C100/C170 (reuso), F600 (retenção) e bloco M (apuração) caem certos — incluindo a
 * resolução de participante ANTES do insert (NFS-e sem chave depende disso).
 */
function spedContribSintetico(): string
{
    // C100 precisa de chave de 44. NFS-e (A100) não tem chave.
    $chave = str_pad('35240622333444000195550010000001231', 44, '0');

    return implode("\n", [
        '|0000|006|0|01022026|28022026|EMPRESA TESTE|11222333000181|MG|1|3106200|0|0|',
        '|0110|2|1|1|2|', // regime cumulativo
        '|0150|FOR7|Fornecedor Servico A|01058|22333444000195||123456||rua x|10||centro|',
        '|0150|FOR8|Fornecedor Servico B|01058|33444555000156||654321||rua y|20||centro|',
        // |0200|COD_ITEM|DESCR|COD_BARRA|COD_ANT|UNID_INV|TIPO_ITEM|COD_NCM|EX_IPI|COD_GEN|COD_LST|ALIQ_ICMS|
        '|0200|SERV01|Consultoria contabil|||UN|09|84713012|||||',
        // NFS-e de FOR7 (saída), com ISS
        '|A100|1|0|FOR7|00|1|0|500||01022026|01022026|1000,00|0|0|900,00|5,85|900,00|27,00|0|0|410,50|',
        '|A170|1|SERV01|Consultoria|1000,00|0|00|0|01|900,00|0,65|5,85|01|900,00|3,00|27,00|3.1.1|CC1|',
        // NFS-e MESMO número/série de OUTRO emitente (FOR8) — não pode colidir
        '|A100|1|0|FOR8|00|1|0|500||05022026|05022026|2000,00|0|0|1800,00|11,70|1800,00|54,00|0|0|821,00|',
        '|A170|1|SERV01|Consultoria|2000,00|0|00|0|01|1800,00|0,65|11,70|01|1800,00|3,00|54,00|3.1.1|CC1|',
        // NF-e mercadoria (reuso do C100/C170 fiscal)
        '|C100|0|0|FOR7|55|00|1|123|'.$chave.'|01022026|01022026|500,00|0|0|',
        '|C170|1|MERC01|Mercadoria|2|UN|500,00|0|0|000|5102|',
        // Retenção
        '|F600|01|15022026|10000,00|485,00|5952|1|22333444000195|32,50|150,00|0|',
        // Apuração PIS/COFINS
        '|M200|100,00|0|0|100,00|0|0|100,00|0|0|0|0|7166,99|',
        '|M600|500,00|0|0|500,00|0|0|500,00|0|0|0|0|33078,20|',
        '|9999|15|',
    ]);
}

function criarImportacaoContrib(): array
{
    $user = User::factory()->create();
    $clienteId = DB::table('clientes')->insertGetId([
        'user_id' => $user->id,
        'razao_social' => 'EMPRESA TESTE',
        'documento' => '11222333000181',
        'is_empresa_propria' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $imp = EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $clienteId,
        'tipo_efd' => 'EFD PIS/COFINS',
        'filename' => 'contrib.txt',
        'arquivo_base64' => json_encode(spedContribSintetico()),
        'status' => 'processando',
        'iniciado_em' => now()->subMinutes(1),
    ]);

    return [$user, $clienteId, $imp];
}

it('persiste NFS-e + mercadoria + retenção + apuração PIS/COFINS', function () {
    [$user, $clienteId, $imp] = criarImportacaoContrib();

    ProcessarEfdImportacaoJob::dispatchSync($imp->id, 'tab-p');

    $notas = DB::table('efd_notas')->where('importacao_id', $imp->id);

    // 2 NFS-e (modelo 00) + 1 NF-e (modelo 55), todas origem contribuicoes
    expect($notas->count())->toBe(3);
    expect((clone $notas)->where('modelo', '00')->count())->toBe(2);
    expect((clone $notas)->where('modelo', '55')->count())->toBe(1);
    expect((clone $notas)->where('origem_arquivo', 'contribuicoes')->count())->toBe(3);

    // Itens: 2 A170 (serviço) + 1 C170 (mercadoria)
    expect(DB::table('efd_notas_itens')->where('user_id', $user->id)->count())->toBe(3);

    // Participantes (2 fornecedores) + catálogo (1)
    expect(DB::table('participantes')->where('importacao_efd_id', $imp->id)->count())->toBe(2);
    expect(DB::table('efd_catalogo_itens')->where('importacao_id', $imp->id)->count())->toBe(1);

    // Retenção F600
    expect(DB::table('efd_retencoes_fonte')->where('importacao_id', $imp->id)->count())->toBe(1);

    // Apuração: totais a recolher do M200/M600
    $apur = DB::table('efd_apuracoes_contribuicoes')->where('importacao_id', $imp->id)->first();
    expect($apur)->not->toBeNull();
    expect((float) $apur->pis_total_recolher)->toBe(7166.99);
    expect((float) $apur->cofins_total_recolher)->toBe(33078.20);
    expect($apur->cod_inc_tributaria)->toBe('2'); // regime cumulativo do 0110
});

it('resolve participante_id da NFS-e ANTES do insert (COD_PART → 0150)', function () {
    [$user, $clienteId, $imp] = criarImportacaoContrib();

    ProcessarEfdImportacaoJob::dispatchSync($imp->id, 'tab-p');

    // A NFS-e de FOR7 aponta pro participante 22333444000195
    $pFor7 = DB::table('participantes')->where('user_id', $user->id)->where('documento', '22333444000195')->value('id');
    $nfseFor7 = DB::table('efd_notas')
        ->where('importacao_id', $imp->id)->where('modelo', '00')->where('valor_total', 1000)
        ->first();

    expect($nfseFor7->participante_id)->toBe((int) $pFor7);

    // Item A170 ligado à NFS-e correta (linkagem por numero|serie|modelo|cod_part, sem chave)
    expect(DB::table('efd_notas_itens')->where('efd_nota_id', $nfseFor7->id)->count())->toBe(1);
});

it('duas NFS-e de mesmo número/série mas emitentes diferentes coexistem (não colidem)', function () {
    [$user, $clienteId, $imp] = criarImportacaoContrib();

    ProcessarEfdImportacaoJob::dispatchSync($imp->id, 'tab-p');

    // Ambas número 500, série 1, modelo 00 — distinguidas por participante_id no índice nfse.
    $nfse = DB::table('efd_notas')->where('importacao_id', $imp->id)->where('modelo', '00')->where('numero', 500)->get();

    expect($nfse)->toHaveCount(2);
    expect($nfse->pluck('participante_id')->unique()->filter())->toHaveCount(2);
});

it('finaliza concluído com resumo de serviços e mercadorias', function () {
    [$user, $clienteId, $imp] = criarImportacaoContrib();

    ProcessarEfdImportacaoJob::dispatchSync($imp->id, 'tab-p');

    $imp->refresh();
    expect($imp->status)->toBe('concluido');

    $resumo = $imp->resumo_final;
    expect($resumo['blocos']['notas_servicos']['total_notas'])->toBe(2);
    expect($resumo['blocos']['notas_mercadorias']['total_notas'])->toBe(1);
    expect($resumo['integridade']['ok'])->toBeTrue(); // C100 do arquivo está no banco
});

it('é idempotente: reprocessar não duplica (notas, retenção, apuração)', function () {
    [$user, $clienteId, $imp] = criarImportacaoContrib();

    ProcessarEfdImportacaoJob::dispatchSync($imp->id, 'tab-p');
    ProcessarEfdImportacaoJob::dispatchSync($imp->id, 'tab-p');

    expect(DB::table('efd_notas')->where('importacao_id', $imp->id)->count())->toBe(3);
    expect(DB::table('efd_notas_itens')->where('user_id', $user->id)->count())->toBe(3);
    expect(DB::table('efd_retencoes_fonte')->where('importacao_id', $imp->id)->count())->toBe(1);
    expect(DB::table('efd_apuracoes_contribuicoes')->where('importacao_id', $imp->id)->count())->toBe(1);
});
