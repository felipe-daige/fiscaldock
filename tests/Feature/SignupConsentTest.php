<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function signupPayload(array $overrides = []): array
{
    return array_merge([
        'nome' => 'Maria',
        'sobrenome' => 'Silva',
        'email' => 'maria.silva+'.uniqid().'@example.com',
        'telefone' => '67999990000',
        'senha' => 'senha-forte-123',
        'senha_confirmation' => 'senha-forte-123',
        'empresa' => 'Contábil Exemplo',
        'cargo' => 'Contadora',
        'documento' => '11144477735',
        'faturamento' => 'ate_120k',
        'desafio_principal' => 'documentos_espalhados',
        'termos_aceitos' => '1',
        'privacidade_aceita' => '1',
    ], $overrides);
}

it('signup falha sem aceite dos Termos de Uso', function () {
    $response = $this->postJson('/criar-conta', signupPayload(['termos_aceitos' => null]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['termos_aceitos']);
    expect(User::count())->toBe(0);
});

it('signup falha sem aceite da Política de Privacidade', function () {
    $response = $this->postJson('/criar-conta', signupPayload(['privacidade_aceita' => null]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['privacidade_aceita']);
    expect(User::count())->toBe(0);
});

it('signup com ambos os consentimentos cria usuário e grava terms_accepted_at', function () {
    $response = $this->postJson('/criar-conta', signupPayload());

    $response->assertStatus(200);
    $user = User::firstWhere('name', 'Maria');
    expect($user)->not->toBeNull();
    expect($user->terms_accepted_at)->not->toBeNull();
});

it('view /criar-conta mostra os dois checkboxes obrigatórios separados', function () {
    $response = $this->get('/criar-conta');

    $response->assertOk()
        ->assertSee('name="termos_aceitos"', false)
        ->assertSee('name="privacidade_aceita"', false)
        ->assertSee('Sobre seus dados (LGPD)', false);
});
