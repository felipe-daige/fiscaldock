<?php

use App\Models\User;
use App\Notifications\ResumoSemanalNotification;
use App\Support\Mail\Blocos;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['mail.default' => 'array']);
});

/** @param array<string, mixed> $resumo */
function renderResumoSemanalVisual(array $resumo): array
{
    $user = User::factory()->create();
    $user->notifyNow(new ResumoSemanalNotification($resumo));

    $mensagem = app('mailer')->getSymfonyTransport()->messages()->last()->getOriginalMessage();

    return [
        'subject' => $mensagem->getSubject(),
        'html' => $mensagem->getHtmlBody(),
    ];
}

function resumoSemanalVisual(array $sobrescrever = []): array
{
    return array_replace([
        'periodo_inicio' => now()->subDays(7),
        'periodo_fim' => now(),
        'por_severidade' => ['alta' => 2, 'media' => 1, 'baixa' => 3],
        'destaques' => [
            [
                'id' => 1,
                'titulo' => 'Fornecedor irregular com notas escrituradas',
                'severidade' => 'alta',
                'valor_risco' => 5000.0,
            ],
            [
                'id' => 2,
                'titulo' => 'Certidão municipal próxima do vencimento',
                'severidade' => 'media',
                'valor_risco' => 0.0,
            ],
        ],
        'consultas' => 10,
        'clearance' => 5,
        'importacoes' => 2,
    ], $sobrescrever);
}

it('renderiza o resumo com hierarquia executiva e componentes padronizados', function () {
    $render = renderResumoSemanalVisual(resumoSemanalVisual());

    expect($render['subject'])
        ->toContain('Resumo fiscal: 6 alertas novos')
        ->and($render['html'])
        ->toContain('Sua semana fiscal, em um olhar')
        ->toContain('Ação prioritária')
        ->toContain('>6</span>')
        ->toContain('alertas novos')
        ->toContain('Exposição mapeada')
        ->toContain('R$ 5.000,00')
        ->toContain('Mapa de atenção')
        ->toContain('Agir agora')
        ->toContain('Acompanhar')
        ->toContain('Informativo')
        ->toContain('Risco alto · prioridade 01')
        ->toContain('Primeiro movimento recomendado')
        ->toContain('Atividade processada')
        ->toContain('Revisar prioridades na central')
        ->toContain('Você controla este resumo.');
});

it('renderiza um estado positivo completo quando houve atividade sem alertas', function () {
    $render = renderResumoSemanalVisual(resumoSemanalVisual([
        'por_severidade' => ['alta' => 0, 'media' => 0, 'baixa' => 0],
        'destaques' => [],
    ]));

    expect($render['subject'])
        ->toContain('sem novos alertas')
        ->and($render['html'])
        ->toContain('Semana em ordem')
        ->toContain('>0</span>')
        ->toContain('alertas novos')
        ->toContain('Nenhuma ação corretiva nova')
        ->toContain('Atividade processada')
        ->toContain('Abrir painel fiscal')
        ->not->toContain('Mapa de atenção')
        ->not->toContain('Prioridades para revisar');
});

it('mantem as tres severidades na mesma escala visual', function () {
    $html = (string) Blocos::severidades(['alta' => 4, 'media' => 2, 'baixa' => 1]);

    expect(substr_count($html, 'font-size: 27px'))->toBe(3)
        ->and(substr_count($html, 'width="33.33%"'))->toBe(3)
        ->and($html)->toContain('Agir agora', 'Acompanhar', 'Informativo');
});

it('escapa o titulo dinamico dos alertas na lista priorizada', function () {
    $html = (string) Blocos::listaAlertas([
        [
            'titulo' => '<img src=x onerror=alert(1)>',
            'severidade' => 'alta',
            'valor_risco' => 10.0,
        ],
    ], true);

    expect($html)
        ->toContain('&lt;img src=x onerror=alert(1)&gt;')
        ->not->toContain('<img src=x onerror=alert(1)>');
});
