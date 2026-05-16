<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rodapé público mostra os três links LGPD (Termos · Privacidade · Cookies)', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('href="' . route('termos') . '"', false)
        ->assertSee('href="' . route('privacidade') . '"', false)
        ->assertSee('href="' . route('cookies') . '"', false);
});
