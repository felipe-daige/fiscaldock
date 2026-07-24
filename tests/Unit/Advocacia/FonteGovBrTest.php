<?php

use App\Services\Consultas\Fontes\Advocacia\SigefParcelasFonte;
use App\Support\CertidaoBadge;

beforeEach(function () {
    config()->set('consultas.infosimples_ativo', true);
    config()->set('consultas.providers.infosimples.token', 'test-token');
    config()->set('advocacia.fontes_publicas_liberadas', ['sigef_parcelas']);
});

test('SIGEF fica INDISPONÍVEL sem credencial GOV.BR (não cobra 608 de login)', function () {
    config()->set('consultas.govbr', ['login_cpf' => null, 'login_senha' => null]);
    expect((new SigefParcelasFonte)->pronta())->toBeFalse();

    config()->set('consultas.govbr', ['login_cpf' => '08403652178', 'login_senha' => 'x']);
    expect((new SigefParcelasFonte)->pronta())->toBeTrue();
});

test('SIGEF params levam documento do alvo + login do solicitante', function () {
    config()->set('consultas.govbr', ['login_cpf' => '111', 'login_senha' => 'senha']);
    $p = (new SigefParcelasFonte)->params(['tipo_pessoa' => 'PF', 'documento' => '52998224725', 'cpf' => '52998224725']);

    expect($p['cpf'])->toBe('52998224725')
        ->and($p['login_cpf'])->toBe('111')
        ->and($p['login_senha'])->toBe('senha');
});

test('SIGEF: 612 sem parcelas = Negativa (nada consta, não falha)', function () {
    $n = (new SigefParcelasFonte)->normalizar(['errors' => ['Não foram encontradas parcelas.']], 'nao_encontrado');
    expect($n['sigef_parcelas']['status'])->toBe('Negativa')
        ->and($n['sigef_parcelas']['nada_consta'])->toBeTrue();
});

test('SIGEF: 620 de autorização vira AUTORIZACAO_PENDENTE com url + badge âmbar', function () {
    $raw = ['errors' => ['É necessário autorizar a primeira vez manualmente o gov.br para ter acesso '
        .'ao serviço que você solicitou (https://sigef.incra.gov.br/consultar/parcelas - faça login)']];
    $b = (new SigefParcelasFonte)->normalizar($raw, 'erro_participante')['sigef_parcelas'];

    expect($b['status'])->toBe('AUTORIZACAO_PENDENTE')
        ->and($b['url_autorizacao'])->toBe('https://sigef.incra.gov.br/consultar/parcelas')
        ->and(CertidaoBadge::classificar($b['status'])['label'])->toBe('Autorização pendente');
});

test('SIGEF: 620 de dado inválido (sem "autorizar") NÃO vira autorização pendente', function () {
    $b = (new SigefParcelasFonte)->normalizar(['errors' => ['Dados não conferem.']], 'erro_participante');
    // Sem o padrão autorizar+gov.br, cai no fallback da base (nada persiste / não é pendente).
    expect($b['sigef_parcelas']['status'] ?? null)->not->toBe('AUTORIZACAO_PENDENTE');
});
