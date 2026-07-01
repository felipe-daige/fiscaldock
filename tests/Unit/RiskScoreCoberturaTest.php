<?php

use App\Services\RiskScoreService;

/**
 * Limiar de cobertura do score: só é conclusivo com CND Federal + ≥2 certidões avaliadas.
 * Abaixo disso → 'inconclusivo' (evita "Baixo Risco" só com cadastro).
 */
beforeEach(function () {
    $this->svc = new RiskScoreService;
});

it('cobertura insuficiente quando só cadastral foi avaliado', function () {
    $scores = ['cadastral' => 0, 'cnd_federal' => null, 'cnd_estadual' => null, 'fgts' => null, 'trabalhista' => null];
    expect($this->svc->coberturaSuficiente($scores))->toBeFalse()
        ->and($this->svc->classificarComCobertura($scores))->toBe('inconclusivo');
});

it('cobertura insuficiente sem CND Federal, mesmo com 2 outras certidões', function () {
    $scores = ['cadastral' => 0, 'cnd_federal' => null, 'cnd_estadual' => 0, 'fgts' => 0, 'trabalhista' => null];
    expect($this->svc->coberturaSuficiente($scores))->toBeFalse()
        ->and($this->svc->classificarComCobertura($scores))->toBe('inconclusivo');
});

it('cobertura insuficiente com só a CND Federal (1 certidão)', function () {
    $scores = ['cadastral' => 0, 'cnd_federal' => 0, 'cnd_estadual' => null, 'fgts' => null, 'trabalhista' => null];
    expect($this->svc->coberturaSuficiente($scores))->toBeFalse()
        ->and($this->svc->classificarComCobertura($scores))->toBe('inconclusivo');
});

it('cobertura suficiente com CND Federal + 1 outra certidão → classificação numérica', function () {
    $scores = ['cadastral' => 0, 'cnd_federal' => 0, 'cnd_estadual' => 0, 'fgts' => null, 'trabalhista' => null];
    expect($this->svc->coberturaSuficiente($scores))->toBeTrue()
        ->and($this->svc->classificarComCobertura($scores))->toBe('baixo');
});

it('classifica risco alto quando federal irregular + estadual avaliadas', function () {
    // total renormalizado sobre federal(0.20)+estadual(0.15) = 70 → 'alto' (cadastral null p/ não diluir)
    $scores = ['cadastral' => null, 'cnd_federal' => 70, 'cnd_estadual' => 70, 'fgts' => null, 'trabalhista' => null];
    expect($this->svc->coberturaSuficiente($scores))->toBeTrue()
        ->and($this->svc->classificarComCobertura($scores))->toBe('alto');
});

it('nao_avaliado quando nada foi avaliado', function () {
    $scores = ['cadastral' => null, 'cnd_federal' => null, 'cnd_estadual' => null, 'fgts' => null, 'trabalhista' => null];
    expect($this->svc->classificarComCobertura($scores))->toBe('nao_avaliado');
});

it('label e cor do inconclusivo são explícitos e cinza', function () {
    expect($this->svc->getLabelClassificacao('inconclusivo'))->toBe('Risco Não Conclusivo')
        ->and($this->svc->getCorClassificacao('inconclusivo'))->toBe('gray');
});
