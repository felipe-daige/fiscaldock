<?php

use App\Models\Cliente;
use App\Models\EfdCatalogoItem;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\EfdNotaItem;
use App\Models\Participante;
use App\Models\User;
use App\Services\Efd\ExcluirImportacaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function excluirImpNovaImportacao(User $user, Cliente $cliente, array $attrs = []): EfdImportacao
{
    return EfdImportacao::create(array_merge([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'cnpj' => '12345678000199',
        'periodo_inicio' => '2026-01-01',
        'periodo_fim' => '2026-01-31',
        'filename' => 'arq.txt',
        'status' => 'concluido',
        'creditos_cobrados' => 5,
    ], $attrs));
}

function excluirImpNovaNota(User $user, Cliente $cliente, EfdImportacao $imp, ?Participante $p = null, array $attrs = []): EfdNota
{
    return EfdNota::create(array_merge([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $imp->id,
        'participante_id' => $p?->id,
        'modelo' => '55',
        'numero' => (string) random_int(1, 999999),
        'serie' => '1',
        'origem_arquivo' => 'fiscal',
        'tipo_operacao' => 'saida',
        'valor_total' => 100,
    ], $attrs));
}

it('preview conta derivados e classifica participantes orfaos x compartilhados', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create(['user_id' => $user->id, 'razao_social' => 'Acme', 'documento' => '12345678000199']);

    $imp = excluirImpNovaImportacao($user, $cliente);
    $outraImp = excluirImpNovaImportacao($user, $cliente, ['periodo_inicio' => '2026-02-01', 'periodo_fim' => '2026-02-28']);

    $pOrfao = Participante::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_efd_id' => $imp->id, 'razao_social' => 'Orfao', 'documento' => '11111111000111']);
    $pCompart = Participante::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_efd_id' => $imp->id, 'razao_social' => 'Compart', 'documento' => '22222222000122']);

    $nota = excluirImpNovaNota($user, $cliente, $imp, $pOrfao);
    EfdNotaItem::create(['efd_nota_id' => $nota->id, 'user_id' => $user->id, 'numero_item' => 1, 'codigo_item' => 'X', 'descricao' => 'item', 'valor_total' => 0]);
    excluirImpNovaNota($user, $cliente, $outraImp, $pCompart);

    EfdCatalogoItem::create(['user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_id' => $imp->id, 'cod_item' => 'X', 'descr_item' => 'item', 'tipo_item' => '00']);

    $preview = app(ExcluirImportacaoService::class)->preview($imp->fresh());

    expect($preview['notas'])->toBe(1)
        ->and($preview['itens'])->toBe(1)
        ->and($preview['catalogo'])->toBe(1)
        ->and($preview['participantes']['candidatos'])->toBe(2)
        ->and($preview['participantes']['orfaos'])->toBe(1)
        ->and($preview['participantes']['compartilhados'])->toBe(1);
});
