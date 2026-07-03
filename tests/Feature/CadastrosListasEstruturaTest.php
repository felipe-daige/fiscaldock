<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('mantém o mesmo shell estrutural nas listagens de clientes e participantes', function () {
    $user = User::factory()->trialAtivo()->create();

    foreach (['/app/clientes', '/app/participantes'] as $url) {
        $html = actingAs($user)->get($url)->assertOk()->getContent();

        expect($html)
            ->toContain('class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8"')
            ->toContain('class="space-y-6"')
            ->toContain('class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"')
            ->toContain('class="grid w-full grid-cols-2 gap-2 sm:w-auto sm:flex sm:items-center sm:justify-end"')
            ->toContain('class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"')
            ->toContain('class="w-full min-w-[960px] table-fixed"')
            ->toContain('class="relative bg-white rounded border border-gray-300 max-w-md w-full p-6 z-10"');
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
