<?php

use App\Http\Middleware\EnsureEmpresaPropriaExists;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Bypass do middleware que cria empresa propria — evita writes em tabela clientes
    // com dados nulos de factories; os endpoints testados nao dependem desse middleware
    $this->withoutMiddleware(EnsureEmpresaPropriaExists::class);
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('faturamento endpoint returns json structure', function () {
    $response = $this->getJson('/app/bi/faturamento');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'faturamento_mensal',
            'top_clientes',
            'faturamento_por_uf',
        ]);
});

test('efd endpoint returns json structure', function () {
    $response = $this->getJson('/app/bi/efd');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'kpis' => [
                'total_entradas_valor',
                'total_entradas_notas',
                'total_saidas_valor',
                'total_saidas_notas',
                'saldo_liquido',
                'participantes_ativos',
                'notas_em_risco',
            ],
            'fluxo_mensal',
            'volume_blocos',
            'top_fornecedores',
            'top_clientes',
            'tributos_por_tipo',
        ]);
});

test('participantes endpoint returns json structure', function () {
    $response = $this->getJson('/app/bi/participantes');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'fornecedores',
            'clientes',
            'concentracao' => [
                'fornecedores',
                'clientes',
            ],
        ]);
});

test('riscos endpoint returns json structure', function () {
    $response = $this->getJson('/app/bi/riscos');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'score_carteira',
            'fornecedores_irregulares',
            'notas_em_risco',
            'mudancas_regime',
            'gap_importacoes',
        ]);
});

test('tributario efd endpoint returns json structure', function () {
    $response = $this->getJson('/app/bi/tributario-efd');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'consolidado',
            'mensal',
            'aliquota',
            'por_regime',
        ]);
});

test('bi page renders for authenticated user', function () {
    $response = $this->get('/app/bi/dashboard');

    $response->assertStatus(200);
    $response->assertSee('BI Fiscal');
    $response->assertSee('bi.js');
    $response->assertSee('apexcharts.min.js', false);
});

test('bi page loads bi.js with cache-busting version querystring', function () {
    $response = $this->get('/app/bi/dashboard');

    $response->assertStatus(200);
    // Decisão de arquitetura (2026-07-04): MANTÉM o cache-busting `?v=filemtime` — JS velho em
    // cache após deploy é pior que o double-load, e o padrão é usado em ~10 outras views. O SPA
    // (resources/js/spa.js) deduplica <script src> pelo src já presente no <head>, então a
    // navegação normal não re-executa o bi.js. Remover o `?v=` NÃO resolveria o double-load do
    // load inicial (o script da página inicial não fica no <head> para deduplicar) e ainda
    // sacrificaria o cache-busting — por isso o pin antigo "sem ?v=" foi descartado.
    // asset() gera URL absoluta (com host) → validamos o caminho + cache-busting, não o prefixo.
    $response->assertSee('js/bi.js?v=', false);
});
