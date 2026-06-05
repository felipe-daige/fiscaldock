<?php

use App\Jobs\ProcessarConsultaJob;
use App\Models\ConsultaResultado;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('consulta cadastro, persiste e posta progresso', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();

    Http::fake(['minhareceita.org/*' => Http::response([
        'razao_social' => 'EMPRESA X', 'descricao_situacao_cadastral' => 'ATIVA',
        'situacao_cadastral' => 2, 'uf' => 'MS', 'municipio' => 'CAMPO GRANDE',
        'cep' => '79005350', 'logradouro' => 'SALGADO FILHO', 'numero' => '2616', 'bairro' => 'JD AMERICA',
        'qsa' => [], 'cnaes_secundarios' => [], 'opcao_pelo_simples' => false, 'opcao_pelo_mei' => false,
    ], 200)]);

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId,
        participanteId: $participanteId,
        userId: $userId,
        tabId: 'tab-test',
        chavesFontes: ['cadastro'],
        alvo: ['cnpj' => '00000000000191'],
        etapas: ['Preparando consulta', 'Dados cadastrais'],
    );

    $r = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($r->resultado_dados['razao_social'])->toBe('EMPRESA X');
    expect($r->resultado_dados['situacao_cadastral'])->toBe('ATIVA');
    expect($r->status)->toBe('sucesso');

    $cache = Cache::get("progresso:{$userId}:tab-test");
    expect($cache)->not->toBeNull();
    expect($cache['total_etapas'])->toBe(2);
});
