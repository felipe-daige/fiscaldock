<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('GET /cookies responde 200 e mostra o cartão da Política de Cookies', function () {
    $response = $this->get('/cookies');

    $response->assertOk()
        ->assertSee('Política de Cookies', false)
        ->assertSee('FiscalDock', false);
});

it('Política de Cookies contém as 4 categorias e a tabela canônica', function () {
    $response = $this->get('/cookies');

    $response->assertOk()
        ->assertSee('Necessários', false)
        ->assertSee('Funcionais', false)
        ->assertSee('Análise', false)
        ->assertSee('Marketing', false)
        ->assertSee('fiscaldock_session', false)
        ->assertSee('XSRF-TOKEN', false)
        ->assertSee('fd-cookies-consent', false)
        ->assertSee('Configurar cookies', false);
});
