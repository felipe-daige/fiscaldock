<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

it('cria a tabela subscription_plans com as colunas comerciais', function () {
    expect(Schema::hasTable('subscription_plans'))->toBeTrue();
    expect(Schema::hasColumns('subscription_plans', [
        'codigo', 'nome', 'preco_mensal_centavos', 'preco_anual_centavos',
        'creditos_inclusos', 'faixa_slug', 'limite_clientes', 'limite_cnpjs_monitorados',
        'frequencia_padrao_dias', 'profundidade_auto_monitor', 'assentos_inclusos', 'preco_assento_extra_centavos',
        'rollover_cap_multiplicador', 'capabilities', 'is_active', 'ordem',
    ]))->toBeTrue();
});

it('cria a tabela account_subscriptions com o cap de consumo', function () {
    expect(Schema::hasTable('account_subscriptions'))->toBeTrue();
    expect(Schema::hasColumns('account_subscriptions', [
        'user_id', 'subscription_plan_id', 'status', 'ciclo', 'iniciada_em',
        'renova_em', 'creditos_inclusos_saldo', 'limite_consumo_automatico', 'assentos_extras',
    ]))->toBeTrue();
});
