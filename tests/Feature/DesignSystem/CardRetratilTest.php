<?php

use Illuminate\Support\Facades\Blade;

// Componente DS do card retrátil de certidões/fontes (padrão /app/participante):
// colapsado por padrão, badge visível no header, toggle onclick inline (cache-robusto).

test('card retratil renderiza header com titulo, badge e corpo oculto', function () {
    $html = Blade::render(<<<'BLADE'
        <x-card-retratil titulo="CND Federal" acento="#047857">
            <x-slot:badges>
                <span class="badge-teste">OK</span>
            </x-slot:badges>
            <p>Corpo da certidão</p>
        </x-card-retratil>
    BLADE);

    expect($html)
        ->toContain('CND Federal')
        ->toContain('badge-teste')
        ->toContain('Corpo da certidão')
        ->toContain('aria-expanded="false"')
        ->toContain('detalhe-chevron')
        ->toContain('Ver tudo')
        ->toContain('border-left: 3px solid #047857');

    // Corpo começa colapsado (hidden) e o toggle é onclick inline, sem depender de JS-file.
    expect($html)->toMatch('/<div id="[^"]+" class="hidden/');
    expect($html)->toContain('onclick=');
});

test('card retratil aceita subheader e funciona sem acento', function () {
    $html = Blade::render(<<<'BLADE'
        <x-card-retratil titulo="Dados cadastrais">
            <x-slot:subheader>
                <span>Razão Social Ltda</span>
            </x-slot:subheader>
            <dl>Itens</dl>
        </x-card-retratil>
    BLADE);

    expect($html)
        ->toContain('Dados cadastrais')
        ->toContain('Razão Social Ltda')
        ->not->toContain('border-left: 3px solid');
});

test('cards retrateis na mesma pagina tem ids unicos', function () {
    $html = Blade::render(<<<'BLADE'
        <x-card-retratil titulo="A"><p>1</p></x-card-retratil>
        <x-card-retratil titulo="B"><p>2</p></x-card-retratil>
    BLADE);

    preg_match_all('/aria-controls="([^"]+)"/', $html, $m);
    expect($m[1])->toHaveCount(2);
    expect($m[1][0])->not->toBe($m[1][1]);
});
