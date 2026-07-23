<?php

use App\Jobs\ProcessarEfdImportacaoJob;
use App\Models\EfdImportacao;
use App\Models\User;
use App\Services\Efd\Handlers\Handler0150;
use App\Services\Efd\Sped\SpedParser;
use App\Services\EfdAuditoriaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * Regressões dos fixes P0 do code-review do motor EFD (2026-07-22).
 */

// ── F1 / F13: encoding canônico de arquivo_base64 ───────────────────────────
it('conteudoSped: base64 (canônico), JSON-legado, cru e Latin-1 fazem round-trip', function () {
    $sped = "|0000|020|0|01012026|31012026|EMPRESA|11222333000181|MG|1|3106200|0|A|0|0|\n|9999|2|";
    $imp = new EfdImportacao;

    // Canônico (novo): base64. E base64 é UTF-8-safe — Latin-1 não quebra (json_encode quebraria).
    $latin1 = mb_convert_encoding('|0000|AÇÃO SÃO PAULO|', 'ISO-8859-1', 'UTF-8');
    $imp->arquivo_base64 = EfdImportacao::encodeConteudoSped($latin1);
    expect($imp->conteudoSped())->toBe($latin1);
    expect(base64_decode($imp->arquivo_base64, true))->toBe($latin1); // download path (base64_decode direto)

    // Legado transição: json_encode(string).
    $imp->arquivo_base64 = json_encode($sped);
    expect($imp->conteudoSped())->toBe($sped);

    // Legado cru (fixtures antigos).
    $imp->arquivo_base64 = $sped;
    expect($imp->conteudoSped())->toBe($sped);

    // Ausente.
    $imp->arquivo_base64 = null;
    expect($imp->conteudoSped())->toBe('');
});

it('encodeConteudoSped não falha em bytes Latin-1 (json_encode falharia)', function () {
    $latin1 = mb_convert_encoding('SÃO JOÃO AÇÚCAR', 'ISO-8859-1', 'UTF-8');
    expect(json_encode($latin1))->toBeFalse();                          // a raiz do bug
    expect(EfdImportacao::encodeConteudoSped($latin1))->not->toBe('');  // o fix
});

// ── F3: NULL em coluna NOT NULL não derruba o import ────────────────────────
it('Handler0150 com documento malformado (13 dígitos) não injeta NULL em tipo_documento', function () {
    // CNPJ de 13 dígitos (ERP perdeu o zero à esquerda): tipoDocumento() devolveria null.
    $linha = '|0150|FOR9|Fornecedor X|01058|1222333000195||123||rua|1||centro|';
    $row = (new Handler0150)->mapear(iterator_to_array((new SpedParser)->stream($linha))[0], null);

    expect($row['documento'])->toBe('1222333000195');
    expect($row['tipo_documento'])->toBe('PJ'); // coalesce pro DEFAULT, nunca null
});

// ── F11: integridade enxerga A100 (NFS-e) ───────────────────────────────────
it('integridade conta A100 e falha quando a NFS-e some do banco', function () {
    $user = User::factory()->create();
    $cli = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'razao_social' => 'X', 'documento' => '11222333000181',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    // SPED com 1 A100 que tem chave (código de verificação), NÃO descartável.
    $sped = "|0000|006|0|||01022026|28022026|X|11222333000181|MG|1|||\n".
        "|A100|1|0|FOR7|00|1|0|500|COD9CHK01|01022026|01022026|1000,00|\n|9999|3|";
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cli, 'tipo_efd' => 'EFD PIS/COFINS',
        'filename' => 'c.txt', 'arquivo_base64' => EfdImportacao::encodeConteudoSped($sped),
        'status' => 'processando', 'iniciado_em' => now(),
    ]);

    // Banco vazio (a NFS-e "sumiu") → integridade deve acusar.
    $res = app(EfdAuditoriaService::class)->integridade($imp);
    expect($res['esperadas'])->toBe(1);
    expect($res['faltando'])->toBe(1);
    expect($res['ok'])->toBeFalse();
});

// ── F15: reimportação do mesmo item atualiza importacao_id do catálogo ──────
it('upsert do catálogo atualiza importacao_id (aba Catálogo do 2º mês não fica vazia)', function () {
    $user = User::factory()->create();
    $cli = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'razao_social' => 'X', 'documento' => '11222333000181',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $sped = fn (string $periodo) => EfdImportacao::encodeConteudoSped(
        "|0000|016|0|{$periodo}|X|11222333000181|MG|1|3106200|0|A|0|0|\n".
        "|0200|SERV01|Consultoria|||UN|09|84713012|||||\n|9999|2|"
    );

    $imp1 = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cli, 'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'jan.txt',
        'arquivo_base64' => $sped('01012026|31012026'), 'status' => 'processando', 'iniciado_em' => now(),
    ]);
    ProcessarEfdImportacaoJob::dispatchSync($imp1->id, null);

    $imp2 = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cli, 'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'fev.txt',
        'arquivo_base64' => $sped('01022026|28022026'), 'status' => 'processando', 'iniciado_em' => now(),
    ]);
    ProcessarEfdImportacaoJob::dispatchSync($imp2->id, null);

    // 1 item (dedup por cliente+cod_item), agora atribuído à 2ª importação.
    expect(DB::table('efd_catalogo_itens')->where('cliente_id', $cli)->count())->toBe(1);
    expect(DB::table('efd_catalogo_itens')->where('importacao_id', $imp2->id)->count())->toBe(1);
    expect(DB::table('efd_catalogo_itens')->where('importacao_id', $imp1->id)->count())->toBe(0);
});

// ── F4: filho de nota deduplicada (importação anterior) ainda linka ─────────
it('C170 de nota já persistida em importação anterior linka ao id existente', function () {
    $user = User::factory()->create();
    $cli = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'razao_social' => 'X', 'documento' => '11222333000181',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $chave = str_pad('35260611222333000181550010000001231', 44, '0');

    // Importação 1: escritura a nota (saída) SEM itens.
    $imp1 = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cli, 'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'jan.txt',
        'arquivo_base64' => EfdImportacao::encodeConteudoSped(
            "|0000|016|0|01012026|31012026|X|11222333000181|MG|1|3106200|0|A|0|0|\n".
            "|C100|1|1|CLI1|55|00|1|123|{$chave}|10012026|10012026|500,00|\n".
            "|C190|00|5102|18,00|500,00|500,00|90,00|0|0|0|0|\n|9999|4|"
        ),
        'status' => 'processando', 'iniciado_em' => now(),
    ]);
    ProcessarEfdImportacaoJob::dispatchSync($imp1->id, null);
    $notaId = DB::table('efd_notas')->where('importacao_id', $imp1->id)->value('id');
    expect(DB::table('efd_notas_itens')->where('efd_nota_id', $notaId)->count())->toBe(0);

    // Importação 2 (outro período): MESMA chave como entrada, agora COM 1 item.
    $imp2 = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cli, 'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'fev.txt',
        'arquivo_base64' => EfdImportacao::encodeConteudoSped(
            "|0000|016|0|01022026|28022026|X|11222333000181|MG|1|3106200|0|A|0|0|\n".
            "|0150|FOR1|Fornecedor|01058|22333444000195||9||rua|1||c|\n".
            "|C100|0|0|FOR1|55|00|1|123|{$chave}|10012026|10012026|500,00|\n".
            "|C170|1|MERC01|Mercadoria|2|UN|500,00|0|0|000|5102|\n".
            "|C190|00|1102|18,00|500,00|500,00|90,00|0|0|0|0|\n|9999|6|"
        ),
        'status' => 'processando', 'iniciado_em' => now(),
    ]);
    ProcessarEfdImportacaoJob::dispatchSync($imp2->id, null);

    // A nota NÃO foi reinserida (dedup), mas o C170 da 2ª importação linkou ao id existente.
    expect(DB::table('efd_notas')->where('chave_acesso', $chave)->count())->toBe(1);
    expect(DB::table('efd_notas_itens')->where('efd_nota_id', $notaId)->count())->toBe(1);
});
