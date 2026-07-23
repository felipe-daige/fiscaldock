<?php

use App\Jobs\ProcessarEfdImportacaoJob;
use App\Models\EfdImportacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * Streaming em 3 passadas (P2): prova que a linkagem pai→filho funciona ATRAVÉS do
 * limite de chunk (CHUNK=1000) — notas e itens são inseridos em blocos, e o mapa de
 * linkagem relido liga corretamente mesmo os filhos cujo pai caiu num chunk anterior.
 */
it('liga itens/consolidados de >1000 notas (cruza o limite de chunk) sem perder nenhum', function () {
    $user = User::factory()->create();
    $cli = DB::table('clientes')->insertGetId([
        'user_id' => $user->id, 'razao_social' => 'BIG', 'documento' => '11222333000181',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);

    // 1100 NF-e (> CHUNK 1000), cada uma com 1 C170 + 1 C190. Chaves distintas por concat
    // (sem overflow de int). Total: 1100 notas, 1100 itens, 1100 consolidados.
    $n = 1100;
    $lin = ['|0000|016|0|01012026|31012026|BIG|11222333000181|MG|1|3106200|0|A|0|0|'];
    for ($i = 1; $i <= $n; $i++) {
        $chave = '3526'.str_pad((string) $i, 40, '0', STR_PAD_LEFT);
        $lin[] = "|C100|1|1|CLI1|55|00|1|{$i}|{$chave}|10012026|10012026|100,00|";
        $lin[] = '|C170|1|ITEM1|Produto|1|UN|100,00|0|0|000|5102|';
        $lin[] = '|C190|00|5102|18,00|100,00|100,00|18,00|0|0|0|0|';
    }
    $lin[] = '|9999|'.(count($lin) + 1).'|';

    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cli, 'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'big.txt',
        'arquivo_base64' => EfdImportacao::encodeConteudoSped(implode("\n", $lin)),
        'status' => 'processando', 'iniciado_em' => now(),
    ]);

    ProcessarEfdImportacaoJob::dispatchSync($imp->id, null);

    // Nenhum filho perdido na fronteira do chunk.
    expect(DB::table('efd_notas')->where('importacao_id', $imp->id)->count())->toBe($n);
    expect(DB::table('efd_notas_itens')->where('user_id', $user->id)->count())->toBe($n);
    expect(DB::table('efd_notas_consolidados')->where('user_id', $user->id)->count())->toBe($n);

    $imp->refresh();
    expect($imp->status)->toBe('concluido');
    expect($imp->resumo_final['integridade']['ok'])->toBeTrue();
});
