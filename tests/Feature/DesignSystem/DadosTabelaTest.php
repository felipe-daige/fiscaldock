<?php

use Illuminate\Support\Facades\Blade;

test('dados tabela preserva contrato fixo e preenche ausencias com traco', function () {
    $html = Blade::render(
        '<x-dados-tabela :campos="$campos" />',
        ['campos' => [
            ['label' => 'CNPJ / CPF', 'valor' => '12345678000190', 'mono' => true],
            ['label' => 'Inscrição estadual', 'valor' => null, 'mono' => true],
            ['label' => 'Endereço', 'valor' => 'Rua Fiscal, 100', 'full' => true],
        ]],
    );

    expect($html)
        ->toContain('data-dados-tabela')
        ->toContain('CNPJ / CPF')
        ->toContain('12345678000190')
        ->toContain('Inscrição estadual')
        ->toContain('—')
        ->toContain('font-mono tabular-nums')
        ->toContain('col-span-2')
        ->toContain('h-14')
        ->toContain('h-16');

    expect(substr_count($html, 'data-dado-celula'))->toBe(3);
});

test('card de parte usa cabecalho corpo e rodape com alturas padronizadas', function () {
    $html = Blade::render(<<<'BLADE'
        <x-parte-operacao-card
            titulo="Cliente"
            nome="EMPRESA DE TESTE LTDA"
            href="/app/cliente/10"
            situacao="ATIVA"
            situacao-hex="#047857"
            papel="Empresa própria"
            :campos="[['label' => 'CNPJ / CPF', 'valor' => '123']]"
        />
    BLADE);

    expect($html)
        ->toContain('data-parte-operacao-card')
        ->toContain('data-dados-tabela')
        ->toContain('h-full')
        ->toContain('h-10')
        ->toContain('line-clamp-2')
        ->toContain('EMPRESA DE TESTE LTDA')
        ->toContain('Empresa própria')
        ->toContain('background-color: #047857')
        ->toContain('Abrir cadastro completo');
});

test('card preserva a estrutura quando o documento ainda nao tem perfil cadastral', function () {
    $html = Blade::render(<<<'BLADE'
        <x-parte-operacao-card
            titulo="Participante"
            nome="PARTE IDENTIFICADA NO XML"
            situacao=""
            :campos="[['label' => 'CNPJ / CPF', 'valor' => null]]"
        />
    BLADE);

    expect($html)
        ->toContain('data-parte-operacao-card')
        ->toContain('PARTE IDENTIFICADA NO XML')
        ->toContain('Situação não informada')
        ->toContain('Perfil cadastral não disponível')
        ->not->toContain('href=""');
});
