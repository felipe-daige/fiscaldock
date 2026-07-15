<?php

use App\Models\Cliente;
use App\Models\Participante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('mantém o mesmo shell estrutural nas listagens de clientes e participantes', function () {
    $user = User::factory()->trialAtivo()->create();

    foreach (['/app/clientes', '/app/participantes'] as $url) {
        $html = actingAs($user)->get($url)->assertOk()->getContent();

        expect($html)
            ->toContain('data-cockpit-layout="stack"')
            ->toContain('data-cockpit-identidade')
            ->toContain('data-cadastro-lista-layout')
            ->toContain('data-cockpit-acoes')
            ->toContain('data-cockpit-indicadores')
            ->toContain('data-mobile-filters')
            ->toContain('class="w-full min-w-[960px] table-fixed"')
            ->toContain('class="relative bg-white rounded border border-gray-300 max-w-md w-full p-6 z-10"');
    }
});

it('aplica o cockpit vertical aos formulários e perfis de clientes e participantes', function () {
    $user = User::factory()->trialAtivo()->create();
    $cliente = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '12345678000195',
        'razao_social' => 'Cliente Estrutural Ltda',
        'ativo' => true,
    ]);
    $participante = Participante::create([
        'user_id' => $user->id,
        'documento' => '11444777000161',
        'razao_social' => 'Participante Estrutural Ltda',
        'origem_tipo' => 'MANUAL',
    ]);

    $formularios = [
        '/app/cliente/novo',
        '/app/cliente/'.$cliente->id.'/editar',
        '/app/participante/novo',
        '/app/participante/'.$participante->id.'/editar',
    ];

    foreach ($formularios as $url) {
        $html = actingAs($user)->get($url)->assertOk()->getContent();

        expect($html)
            ->toContain('data-cockpit-layout="stack"')
            ->toContain('data-cockpit-identidade')
            ->toContain('data-cockpit-form-flow');
    }

    foreach (['/app/cliente/'.$cliente->id, '/app/participante/'.$participante->id] as $url) {
        $html = actingAs($user)->get($url)->assertOk()->getContent();

        expect($html)
            ->toContain('data-cockpit-layout="stack"')
            ->toContain('data-cockpit-identidade')
            ->toContain('data-cockpit-indicadores')
            ->toContain('data-cockpit-profile-flow')
            ->toContain('data-cockpit-dados')
            ->not->toContain('perfil-grid');
    }
});

it('mantém a mesma grade-base e a mesma ordem das colunas nas duas listagens', function () {
    $user = User::factory()->trialAtivo()->create();

    $casos = [
        ['/app/clientes', 'clientes-list-view', ['Cliente', 'Movimentação', 'Regime', 'Situação / Certidões', 'Participantes', 'Ações']],
        ['/app/participantes', 'participantes-list-view', ['Participante', 'Movimentação', 'Regime', 'Situação / Certidões', 'Origem', 'Ações']],
    ];

    foreach ($casos as [$url, $listViewId, $esperados]) {
        $html = actingAs($user)->get($url)->assertOk()->getContent();
        $inicio = strpos($html, 'id="'.$listViewId.'"');
        $fim = strpos($html, '</table>', $inicio);
        $tabela = substr($html, $inicio, $fim - $inicio);

        preg_match_all('/<th\b[^>]*>(.*?)<\/th>/s', $tabela, $matches);
        $cabecalhos = array_values(array_filter(array_map(
            fn (string $th) => trim(preg_replace('/\s+/', ' ', strip_tags($th))),
            $matches[1]
        )));

        expect($cabecalhos)->toBe($esperados);
    }
});
