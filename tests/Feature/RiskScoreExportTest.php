<?php

use App\Models\Cliente;
use App\Models\Participante;
use App\Models\ParticipanteScore;
use App\Models\User;
use App\Services\Risk\Export\RiskScoreReportBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->forceFill([
        'trial_used' => true,
        'trial_started_at' => now(),
        'trial_expires_at' => now()->addDays(30),
        'trial_credits_remaining' => 50,
    ])->save();

    $this->clienteA = Cliente::create([
        'user_id' => $this->user->id,
        'documento' => '10000000000100',
        'razao_social' => 'CLIENTE ALFA',
    ]);
    $this->clienteB = Cliente::create([
        'user_id' => $this->user->id,
        'documento' => '20000000000200',
        'razao_social' => 'CLIENTE BETA',
    ]);

    $this->alto = Participante::create([
        'user_id' => $this->user->id,
        'cliente_id' => $this->clienteA->id,
        'documento' => '11222333000181',
        'razao_social' => 'FORNECEDOR ALTO RISCO',
        'nome_fantasia' => 'ALTO',
        'uf' => 'SP',
    ]);
    ParticipanteScore::create([
        'user_id' => $this->user->id,
        'participante_id' => $this->alto->id,
        'score_total' => 55,
        'score_cadastral' => 0,
        'score_cnd_federal' => 70,
        'score_cnd_estadual' => 0,
        'score_fgts' => 0,
        'score_credito_reforma' => 100,
        'classificacao' => 'alto',
        'ultima_consulta_em' => now(),
    ]);

    $this->baixo = Participante::create([
        'user_id' => $this->user->id,
        'cliente_id' => $this->clienteB->id,
        'documento' => '22333444000192',
        'razao_social' => 'FORNECEDOR BAIXO RISCO',
        'uf' => 'RJ',
    ]);
    ParticipanteScore::create([
        'user_id' => $this->user->id,
        'participante_id' => $this->baixo->id,
        'score_total' => 0,
        'score_cadastral' => 0,
        'score_cnd_federal' => 0,
        'classificacao' => 'baixo',
        'ultima_consulta_em' => now()->subDay(),
    ]);

    $this->pendente = Participante::create([
        'user_id' => $this->user->id,
        'cliente_id' => $this->clienteA->id,
        'documento' => '33444555000103',
        'razao_social' => 'FORNECEDOR PENDENTE',
        'uf' => 'MG',
    ]);

    Participante::create([
        'user_id' => $this->user->id,
        'documento' => '12345678901',
        'razao_social' => 'PESSOA CPF FORA',
    ]);
    Participante::create([
        'user_id' => $this->user->id,
        'documento' => '44555666000114',
        'razao_social' => 'DUPLICATA PROPRIO FORA',
        'origem_tipo' => 'PROPRIO',
    ]);

    $outro = User::factory()->create();
    Participante::create([
        'user_id' => $outro->id,
        'documento' => '55666777000125',
        'razao_social' => 'OUTRO USUARIO FORA',
    ]);
});

function riskExportBody($response): string
{
    $base = $response->baseResponse;
    if ($base instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
        return (string) file_get_contents($base->getFile()->getPathname());
    }
    if ($base instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
        return $response->streamedContent();
    }

    return (string) $response->getContent();
}

it('monta o recorte completo sem paginação e sem vazar CPF, PROPRIO ou outro usuário', function () {
    $relatorio = app(RiskScoreReportBuilder::class)->montar($this->user->id, []);
    $nomes = collect($relatorio['registros'])->pluck('razao_social');

    expect($nomes)->toContain('FORNECEDOR ALTO RISCO', 'FORNECEDOR BAIXO RISCO', 'FORNECEDOR PENDENTE')
        ->not->toContain('PESSOA CPF FORA', 'DUPLICATA PROPRIO FORA', 'OUTRO USUARIO FORA')
        ->and($relatorio['kpis']['avaliados'])->toBe(2)
        ->and($relatorio['kpis']['nao_consultados'])->toBe(3); // pendente + 2 clientes sem score
});

it('respeita cliente, classificação e busca e mantém não consultados no filtro de risco', function () {
    $relatorio = app(RiskScoreReportBuilder::class)->montar($this->user->id, [
        'cliente_id' => $this->clienteA->id,
        'classificacao' => 'alto',
        'busca' => 'FORNECEDOR',
    ]);
    $nomes = collect($relatorio['registros'])->pluck('razao_social');

    expect($nomes)->toContain('FORNECEDOR ALTO RISCO', 'FORNECEDOR PENDENTE')
        ->not->toContain('FORNECEDOR BAIXO RISCO', 'CLIENTE ALFA')
        ->and($relatorio['kpis']['avaliados'])->toBe(1)
        ->and($relatorio['kpis']['nao_consultados'])->toBe(1);
});

it('renderiza o botão único com os três formatos e overlay', function () {
    actingAs($this->user)->withHeader('X-Requested-With', 'XMLHttpRequest')->get('/app/score-fiscal')
        ->assertOk()
        ->assertSee('data-export-menu="modal-exportar-score"', false)
        ->assertSee('data-export-option="pdf"', false)
        ->assertSee('data-export-option="xlsx"', false)
        ->assertSee('data-export-option="csv"', false)
        ->assertSee('download-overlay-score', false)
        ->assertSee('papel A4 retrato', false);
});

it('metodologia() expõe pesos, penalidades, faixas e pisos reais do service', function () {
    $svc = app(\App\Services\RiskScoreService::class);
    $met = $svc->metodologia();

    // Pesos/labels espelham o service (fonte única — nada hardcodado na view)
    $porLabel = collect($met['categorias'])->keyBy('label');
    expect($porLabel['CND Federal']['peso_pct'])->toBe(20)
        ->and($porLabel['CND Federal']['penalidade'])->toBe(70)
        ->and($porLabel['Situação Cadastral']['peso_pct'])->toBe(15)
        ->and($porLabel['Situação Cadastral']['penalidade'])->toBeNull()
        ->and($porLabel['FGTS/CRF']['penalidade'])->toBe(50)
        ->and($porLabel->has('CNDT (Trabalhista)'))->toBeFalse();

    // Faixas na ordem crescente de risco
    expect(collect($met['faixas'])->pluck('faixa')->all())->toBe(['0–20', '21–50', '51–80', '81–100']);

    // Pisos: cadastral crítico/alto + os 4 de GRAVIDADE_CERTIDAO
    expect($met['pisos'])->toHaveCount(2 + count(\App\Services\RiskScoreService::GRAVIDADE_CERTIDAO))
        ->and(collect($met['pisos'])->firstWhere('fonte', 'CND Federal positiva (irregular)')['piso'])
        ->toBe($svc->getLabelClassificacao('alto'));
});

it('PDF do score traz a seção de metodologia (view renderizada)', function () {
    $relatorio = app(RiskScoreReportBuilder::class)->montar($this->user->id, []);
    $html = view('reports.risk-score', ['relatorio' => $relatorio])->render();

    expect($html)->toContain('Como o risco é classificado')
        ->and($html)->toContain('Fontes, pesos e penalidades')
        ->and($html)->toContain('Faixas do score total')
        ->and($html)->toContain('Piso por irregularidade conhecida')
        ->and($html)->toContain('INAPTA/BAIXADA/NULA')
        ->and($html)->toContain('Cobertura mínima');
});

it('baixa PDF A4 retrato com os dados do recorte', function () {
    $response = actingAs($this->user)->get('/app/score-fiscal/exportar-pdf?cliente_id='.$this->clienteA->id);

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf')
        ->and(substr(riskExportBody($response), 0, 4))->toBe('%PDF');
});

it('baixa XLSX com planilha válida', function () {
    $response = actingAs($this->user)->get('/app/score-fiscal/exportar-xlsx');

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('spreadsheetml')
        ->and(substr(riskExportBody($response), 0, 2))->toBe('PK');
});

it('baixa CSV canônico com todos os campos e seta o cookie do overlay', function () {
    $response = actingAs($this->user)->get('/app/score-fiscal/exportar-csv?download_token=score123');

    $response->assertOk()->assertCookie('bi_download', 'score123');
    $csv = riskExportBody($response);

    expect($response->headers->get('content-type'))->toContain('text/csv')
        ->and($csv)->toStartWith("\xEF\xBB\xBF")
        ->and($csv)->toContain('Score total')
        ->and($csv)->toContain('FORNECEDOR ALTO RISCO')
        ->and($csv)->toContain('FORNECEDOR PENDENTE')
        ->and($csv)->not->toContain('OUTRO USUARIO FORA');
});
