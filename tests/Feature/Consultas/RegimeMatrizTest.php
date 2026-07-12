<?php

use App\Jobs\ProcessarConsultaJob;
use App\Models\ConsultaResultado;
use App\Models\Participante;
use App\Services\Consultas\AtualizarFichaCadastralService;
use App\Services\Consultas\Fontes\CadastroFonte;
use Illuminate\Support\Facades\Http;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('completa o regime da filial com o da matriz (RFB só publica na matriz)', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();

    Http::fake([
        // Filial: cadastro ok, mas sem regime publicado (cenário Raizen/Adecoagro).
        'minhareceita.org/07903169001768' => Http::response([
            'razao_social' => 'ADECOAGRO FILIAL', 'descricao_situacao_cadastral' => 'ATIVA',
            'situacao_cadastral' => 2, 'identificador_matriz_filial' => 2,
            'uf' => 'MS', 'municipio' => 'ANGELICA',
            'qsa' => [], 'cnaes_secundarios' => [],
            'opcao_pelo_simples' => false, 'opcao_pelo_mei' => false,
            'regime_tributario' => [],
        ], 200),
        // Matriz: regime publicado.
        'minhareceita.org/07903169000109' => Http::response([
            'razao_social' => 'ADECOAGRO MATRIZ', 'identificador_matriz_filial' => 1,
            'opcao_pelo_simples' => false, 'opcao_pelo_mei' => false,
            'regime_tributario' => [
                ['ano' => 2022, 'forma_de_tributacao' => 'LUCRO REAL'],
                ['ano' => 2023, 'forma_de_tributacao' => 'LUCRO REAL'],
            ],
        ], 200),
    ]);

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['situacao_cadastral', 'dados_cadastrais'],
        alvo: ['cnpj' => '07903169001768'],
        etapas: ['Preparando consulta', 'Dados cadastrais'],
    );

    $r = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($r->resultado_dados['regime_tributario'])->toBe('Lucro Real');
    expect($r->resultado_dados['regime_tributario_origem'])->toBe('matriz');
    expect($r->resultado_dados['regime_tributario_nota'])->toBe('regime da matriz (RFB)');
    expect($r->resultado_dados['regime_tributario_historico'])->toHaveCount(2);
});

it('nao chama a matriz quando a filial ja tem regime (Simples)', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();

    Http::fake([
        'minhareceita.org/07903169001768' => Http::response([
            'razao_social' => 'FILIAL SIMPLES', 'descricao_situacao_cadastral' => 'ATIVA',
            'situacao_cadastral' => 2, 'identificador_matriz_filial' => 2,
            'qsa' => [], 'cnaes_secundarios' => [],
            'opcao_pelo_simples' => true, 'opcao_pelo_mei' => false,
        ], 200),
    ]);

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['situacao_cadastral', 'dados_cadastrais'],
        alvo: ['cnpj' => '07903169001768'],
        etapas: ['Preparando consulta', 'Dados cadastrais'],
    );

    $r = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($r->resultado_dados['regime_tributario'])->toBe('Simples Nacional');
    Http::assertSentCount(1);
});

it('estima o regime quando a matriz tambem nao tem regime publicado', function () {
    [$loteId, $participanteId, $userId] = montarLoteParticipante();

    Http::fake([
        'minhareceita.org/*' => Http::response([
            'razao_social' => 'SEM REGIME', 'descricao_situacao_cadastral' => 'ATIVA',
            'situacao_cadastral' => 2, 'qsa' => [], 'cnaes_secundarios' => [],
            'opcao_pelo_simples' => false, 'opcao_pelo_mei' => false,
            'regime_tributario' => [],
        ], 200),
    ]);

    ProcessarConsultaJob::dispatchSync(
        loteId: $loteId, alvoTipo: 'participante', alvoId: $participanteId, userId: $userId, tabId: 'tab-test',
        consultasIncluidas: ['situacao_cadastral', 'dados_cadastrais'],
        alvo: ['cnpj' => '07903169001768'],
        etapas: ['Preparando consulta', 'Dados cadastrais'],
    );

    // RFB não publica (nem na matriz) → RegimeEstimadoResolver assume com origem marcada.
    $r = ConsultaResultado::where('consulta_lote_id', $loteId)->first();
    expect($r->resultado_dados['regime_tributario'])->toBe('Lucro Presumido');
    expect($r->resultado_dados['regime_tributario_origem'])->toBe('estimado');
    expect($r->resultado_dados['regime_tributario_nota'])->toStartWith('estimado — ');
});

it('gera nota "foi optante do Simples" quando o regime atual e Nao informado', function () {
    $dados = app(CadastroFonte::class)->normalizar([
        'razao_social' => 'EX SIMPLES',
        'opcao_pelo_simples' => false, 'opcao_pelo_mei' => false,
        'data_exclusao_do_simples' => '2026-02-28',
        'qsa' => [], 'cnaes_secundarios' => [],
    ]);

    expect($dados['regime_tributario'])->toBe('Não informado');
    expect($dados['regime_tributario_nota'])->toBe('foi optante do Simples Nacional até 28/02/2026');
});

it('persiste a nota do regime na ficha e limpa nota antiga quando o regime vem direto', function () {
    $user = \App\Models\User::factory()->create();
    $p = Participante::create([
        'user_id' => $user->id, 'documento' => '07903169001768', 'razao_social' => 'FILIAL',
        'regime_tributario' => 'Não informado',
        'regime_tributario_nota' => 'foi optante do Simples Nacional até 31/12/2023',
    ]);

    $svc = app(AtualizarFichaCadastralService::class);

    // Consulta com regime da matriz → regime + nota atualizados.
    $svc->aplicar($p, [
        'razao_social' => 'FILIAL',
        'situacao_cadastral' => 'ATIVA',
        'regime_tributario' => 'Lucro Real',
        'regime_tributario_nota' => 'regime da matriz (RFB)',
    ]);
    expect($p->fresh()->regime_tributario)->toBe('Lucro Real');
    expect($p->fresh()->regime_tributario_nota)->toBe('regime da matriz (RFB)');

    // Consulta posterior com regime direto (sem nota) → nota antiga limpa.
    $svc->aplicar($p->fresh(), [
        'razao_social' => 'FILIAL',
        'situacao_cadastral' => 'ATIVA',
        'regime_tributario' => 'Lucro Presumido',
    ]);
    expect($p->fresh()->regime_tributario)->toBe('Lucro Presumido');
    expect($p->fresh()->regime_tributario_nota)->toBeNull();
});
