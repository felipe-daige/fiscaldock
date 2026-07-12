<?php

use function Pest\Laravel\get;

it('apresenta a jornada completa da plataforma sem a copy legada de seis produtos', function () {
    get('/solucoes')
        ->assertOk()
        ->assertSee('Do arquivo bruto à', false)
        ->assertSee('EFD ICMS/IPI e PIS/COFINS')
        ->assertSee('XML de NF-e em massa')
        ->assertSee('Resumo Fiscal por competência')
        ->assertSee('Quatro camadas para decidir')
        ->assertSee('Score Fiscal')
        ->assertSee('Clearance de documentos')
        ->assertSee('Alertas, dossiês e exportações', false)
        ->assertDontSee('Seis produtos. Um só radar fiscal.', false);
});

it('explica reforma e crédito tributário com transparência', function () {
    get('/solucoes')
        ->assertOk()
        ->assertSee('Preparado para migrar sem perder o fio do crédito.', false)
        ->assertSee('crédito potencial, crédito aproveitável e valor estimado em risco', false)
        ->assertSee('A consulta CNPJ alimenta o eixo de crédito IBS/CBS.', false)
        ->assertSee('Preserva origem real ou estimada do regime', false)
        ->assertSee('não reduzem o valor estimado em R$ sem evidência oficial de recolhimento', false)
        ->assertSee('Volume de entradas × alíquota × fator do regime', false)
        ->assertSee('2026')
        ->assertSee('2033+')
        ->assertSee('não apuração oficial nem garantia de aproveitamento', false);
});

it('usa o saldo de trial configurado no CTA da página', function () {
    config()->set('trial.saldo_reais', 37.50);
    config()->set('trial.validade_dias', 45);

    get('/solucoes')
        ->assertOk()
        ->assertSee("R$\u{A0}37,50 grátis")
        ->assertSee('por 45 dias');
});
