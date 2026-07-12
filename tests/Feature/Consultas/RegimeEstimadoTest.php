<?php

use App\Models\Participante;
use App\Models\User;
use App\Services\Consultas\AtualizarFichaCadastralService;
use App\Services\Consultas\RegimeEstimadoResolver;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function dadosRegimeIndefinido(array $over = []): array
{
    return array_merge([
        'razao_social' => 'ACME LTDA',
        'situacao_cadastral' => 'ATIVA',
        'regime_tributario' => 'Não informado',
        'regime_tributario_nota' => 'foi optante do Simples Nacional até 16/04/2025',
        'natureza_juridica' => '206-2 - Sociedade Empresária Limitada',
        'data_exclusao_simples' => '2025-04-16',
        'endereco' => ['uf' => 'SP', 'municipio' => 'SAO PAULO'],
        'cnaes' => [['codigo' => 3314710, 'descricao' => 'Manutenção', 'principal' => true]],
    ], $over);
}

// ---------- heurística (núcleo puro) ----------

it('estima Lucro Presumido como default, com base na exclusão do Simples', function () {
    [$regime, $base] = app(RegimeEstimadoResolver::class)->estimar(
        naturezaJuridica: '206-2 - Sociedade Empresária Limitada',
        cnaePrincipal: '3314710',
        dataExclusaoSimples: '2025-04-16',
        vendas12m: 1_000_000.0,
    );

    expect($regime)->toBe('Lucro Presumido');
    expect($base)->toContain('16/04/2025');
});

it('estima Lucro Real para atividade financeira (CNAE 64/65)', function () {
    [$regime] = app(RegimeEstimadoResolver::class)->estimar(
        naturezaJuridica: '206-2 - Sociedade Empresária Limitada',
        cnaePrincipal: '6422100', // banco múltiplo
        dataExclusaoSimples: null,
        vendas12m: null,
    );

    expect($regime)->toBe('Lucro Real');
});

it('estima Lucro Real para Sociedade Anônima Aberta', function () {
    [$regime] = app(RegimeEstimadoResolver::class)->estimar(
        naturezaJuridica: '204-6 - Sociedade Anônima Aberta',
        cnaePrincipal: '3314710',
        dataExclusaoSimples: null,
        vendas12m: null,
    );

    expect($regime)->toBe('Lucro Real');
});

it('estima Lucro Real quando as vendas EFD 12m superam o teto do Presumido', function () {
    [$regime, $base] = app(RegimeEstimadoResolver::class)->estimar(
        naturezaJuridica: '206-2 - Sociedade Empresária Limitada',
        cnaePrincipal: '3314710',
        dataExclusaoSimples: null,
        vendas12m: 80_000_000.0,
    );

    expect($regime)->toBe('Lucro Real');
    expect($base)->toContain('teto do Lucro Presumido');
});

// ---------- aplicar() sobre o resultado do CadastroFonte ----------

it('marca origem estimado e nota quando o regime veio Não informado', function () {
    $user = User::factory()->create();
    $p = Participante::create(['user_id' => $user->id, 'documento' => '11111111000111']);

    $dados = app(RegimeEstimadoResolver::class)->aplicar(dadosRegimeIndefinido(), $user->id, 'participante', $p->id);

    expect($dados['regime_tributario'])->toBe('Lucro Presumido');
    expect($dados['regime_tributario_origem'])->toBe('estimado');
    expect($dados['regime_tributario_nota'])->toStartWith('estimado — ');
});

it('não toca regime real vindo da RFB', function () {
    $dados = app(RegimeEstimadoResolver::class)->aplicar(
        dadosRegimeIndefinido(['regime_tributario' => 'Lucro Real', 'regime_tributario_nota' => null]),
        1, 'participante', 1,
    );

    expect($dados['regime_tributario'])->toBe('Lucro Real');
    expect($dados)->not->toHaveKey('regime_tributario_origem');
});

// ---------- persistência na ficha (hierarquia de origem) ----------

it('grava regime estimado na ficha vazia, com origem e nota', function () {
    $user = User::factory()->create();
    $p = Participante::create(['user_id' => $user->id, 'documento' => '11111111000111']);

    $dados = app(RegimeEstimadoResolver::class)->aplicar(dadosRegimeIndefinido(), $user->id, 'participante', $p->id);
    app(AtualizarFichaCadastralService::class)->aplicar($p, $dados);
    $p->refresh();

    expect($p->regime_tributario)->toBe('Lucro Presumido');
    expect($p->regime_tributario_origem)->toBe('estimado');
    expect($p->regime_tributario_nota)->toStartWith('estimado — ');
});

it('estimado nunca sobrescreve regime real já persistido', function () {
    $user = User::factory()->create();
    $p = Participante::create([
        'user_id' => $user->id, 'documento' => '11111111000111',
        'regime_tributario' => 'Lucro Real', 'regime_tributario_origem' => null,
    ]);

    $dados = app(RegimeEstimadoResolver::class)->aplicar(dadosRegimeIndefinido(), $user->id, 'participante', $p->id);
    app(AtualizarFichaCadastralService::class)->aplicar($p, $dados);
    $p->refresh();

    expect($p->regime_tributario)->toBe('Lucro Real');
    expect($p->regime_tributario_origem)->toBeNull();
});

it('regime real da RFB sobrescreve estimado e limpa origem/nota', function () {
    $user = User::factory()->create();
    $p = Participante::create([
        'user_id' => $user->id, 'documento' => '11111111000111',
        'regime_tributario' => 'Lucro Presumido',
        'regime_tributario_origem' => 'estimado',
        'regime_tributario_nota' => 'estimado — a RFB não publica o regime deste CNPJ',
    ]);

    app(AtualizarFichaCadastralService::class)->aplicar($p, dadosRegimeIndefinido([
        'regime_tributario' => 'Lucro Real',
        'regime_tributario_nota' => null,
    ]));
    $p->refresh();

    expect($p->regime_tributario)->toBe('Lucro Real');
    expect($p->regime_tributario_origem)->toBeNull();
    expect($p->regime_tributario_nota)->toBeNull();
});

it('estimado novo substitui estimado antigo', function () {
    $user = User::factory()->create();
    $p = Participante::create([
        'user_id' => $user->id, 'documento' => '11111111000111',
        'regime_tributario' => 'Lucro Presumido',
        'regime_tributario_origem' => 'estimado',
    ]);

    $dados = app(RegimeEstimadoResolver::class)->aplicar(
        dadosRegimeIndefinido(['natureza_juridica' => '204-6 - Sociedade Anônima Aberta']),
        $user->id, 'participante', $p->id,
    );
    app(AtualizarFichaCadastralService::class)->aplicar($p, $dados);
    $p->refresh();

    expect($p->regime_tributario)->toBe('Lucro Real');
    expect($p->regime_tributario_origem)->toBe('estimado');
});
