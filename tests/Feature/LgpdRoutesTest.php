<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('GET /cookies responde 200 e mostra o cartão da Política de Cookies', function () {
    $response = $this->get('/cookies');

    $response->assertOk()
        ->assertSee('Política de Cookies', false)
        ->assertSee('FiscalDock', false);
});
