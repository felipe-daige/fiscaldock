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

it('Termos de Uso contém identificação do controlador, foro e seções LGPD-essenciais', function () {
    $response = $this->get('/termos');

    $response->assertOk()
        ->assertSee('Termos de Uso', false)
        ->assertSee('F. DEVECCHI DAIGE E CIA LTDA', false)
        ->assertSee('63.112.970/0001-07', false)
        ->assertSee('Dourados', false)
        ->assertSee('Comarca de Dourados/MS', false)
        ->assertSee('contato@fiscaldock.com.br', false)
        ->assertSee('Trial', false)
        ->assertSee('Limitação de responsabilidade', false);
});
