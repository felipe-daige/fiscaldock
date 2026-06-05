<?php

use App\Models\ConsultaResultado;
use App\Services\Consultas\Dto\ResultadoFonte;
use App\Services\Consultas\Persistencia\PersistenciaCnpj;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('faz upsert e merge do output da fonte em resultado_dados', function () {
    [$loteId, $participanteId] = montarLoteParticipante();

    $p = new PersistenciaCnpj();
    $p->gravar($loteId, $participanteId, new ResultadoFonte('cadastro', ['razao_social' => 'X', 'consultas_realizadas' => ['situacao_cadastral']], 'sucesso'));
    $p->gravar($loteId, $participanteId, new ResultadoFonte('cnd_federal', ['cnd_federal' => ['status' => 'REGULAR'], 'consultas_realizadas' => ['cnd_federal']], 'sucesso'));

    $r = ConsultaResultado::where('consulta_lote_id', $loteId)->where('participante_id', $participanteId)->firstOrFail();
    expect($r->resultado_dados['razao_social'])->toBe('X');
    expect($r->resultado_dados['cnd_federal']['status'])->toBe('REGULAR');
    expect($r->resultado_dados['consultas_realizadas'])->toContain('situacao_cadastral')->toContain('cnd_federal');
    expect($r->status)->toBe('sucesso');
    expect(ConsultaResultado::count())->toBe(1);
});
