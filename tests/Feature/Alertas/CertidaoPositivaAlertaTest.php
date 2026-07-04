<?php

use App\Models\Alerta;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\ParticipanteScore;
use App\Models\User;
use App\Services\AlertaCentralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * Alerta `certidao_positiva`: dispara quando um fornecedor/cliente tem certidão de
 * regularidade POSITIVA (subscore de certidão > 0 em participante_scores). Complementa
 * `fornecedor_irregular`, que só olha situação cadastral e ignora certidões.
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->cliente = DB::table('clientes')->insertGetId([
        'user_id' => $this->user->id, 'razao_social' => 'MINHA EMPRESA', 'documento' => '00000000000100',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $this->svc = app(AlertaCentralService::class);

    $this->mkParticipante = fn (string $razao, string $doc) => DB::table('participantes')->insertGetId([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente, 'razao_social' => $razao, 'documento' => $doc,
        'origem_tipo' => 'MANUAL', 'created_at' => now(), 'updated_at' => now(),
    ]);
});

it('cria alerta certidao_positiva com severidade alta para CND Estadual positiva', function () {
    $pid = ($this->mkParticipante)('BRENCO ENERGIA', '08070566001769');
    ParticipanteScore::create([
        'user_id' => $this->user->id,
        'participante_id' => $pid,
        'score_cadastral' => 0,
        'score_cnd_federal' => 0,
        'score_cnd_estadual' => 70,
        'score_fgts' => 0,
        'score_trabalhista' => 0,
        'score_total' => 15,
        'classificacao' => 'alto',
        'dados_consultados' => ['cnd_estadual' => ['status' => 'Positiva']],
    ]);

    $this->svc->recalcular($this->user->id);

    $alerta = Alerta::where('user_id', $this->user->id)->where('tipo', 'certidao_positiva')->first();

    expect($alerta)->not->toBeNull()
        ->and($alerta->severidade)->toBe('alta')
        ->and($alerta->participante_id)->toBe($pid)
        // cliente_id = cliente do participante → aparece no filtro de alertas por cliente
        // (score de participante não guarda cliente_id; sem isto o filtro excluía o alerta).
        ->and($alerta->cliente_id)->toBe($this->cliente)
        ->and($alerta->categoria)->toBe('compliance')
        ->and($alerta->descricao)->toContain('CND Estadual');
});

it('severidade = maior gravidade quando há certidões de níveis diferentes (federal alta > fgts média)', function () {
    $pid = ($this->mkParticipante)('FORNECEDOR MISTO', '11111111000111');
    ParticipanteScore::create([
        'user_id' => $this->user->id,
        'participante_id' => $pid,
        'score_cnd_federal' => 70,   // irregular → alta
        'score_cnd_estadual' => 0,
        'score_fgts' => 50,          // irregular → média
        'score_trabalhista' => 0,
        'classificacao' => 'alto',
        'dados_consultados' => ['cnd_federal' => ['status' => 'Positiva'], 'crf_fgts' => ['status' => 'IRREGULAR']],
    ]);

    $this->svc->recalcular($this->user->id);

    $alerta = Alerta::where('user_id', $this->user->id)->where('tipo', 'certidao_positiva')->first();

    expect($alerta->severidade)->toBe('alta')
        ->and($alerta->total_afetados)->toBe(2)
        ->and($alerta->descricao)->toContain('CND Federal')
        ->and($alerta->descricao)->toContain('FGTS/CRF');
});

it('não cria alerta quando todas as certidões são regulares', function () {
    $pid = ($this->mkParticipante)('FORNECEDOR OK', '22222222000122');
    ParticipanteScore::create([
        'user_id' => $this->user->id,
        'participante_id' => $pid,
        'score_cnd_federal' => 0,
        'score_cnd_estadual' => 0,
        'score_fgts' => 0,
        'score_trabalhista' => 0,
        'classificacao' => 'baixo',
        'dados_consultados' => ['cnd_estadual' => ['status' => 'Negativa']],
    ]);

    $this->svc->recalcular($this->user->id);

    expect(Alerta::where('user_id', $this->user->id)->where('tipo', 'certidao_positiva')->count())->toBe(0);
});

it('valor comprado usa só fiscal+entrada, com janelas total e 12 meses', function () {
    $pid = ($this->mkParticipante)('FORNECEDOR COM NOTAS', '44444444000144');
    ParticipanteScore::create([
        'user_id' => $this->user->id,
        'participante_id' => $pid,
        'score_cnd_estadual' => 70,
        'classificacao' => 'alto',
        'dados_consultados' => ['cnd_estadual' => ['status' => 'Positiva']],
    ]);

    $imp = EfdImportacao::create([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente, 'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'i.txt', 'status' => 'concluido', 'iniciado_em' => now(),
    ]);

    $mk = fn (array $a) => EfdNota::create(array_merge([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente, 'participante_id' => $pid,
        'importacao_id' => $imp->id, 'numero' => random_int(1, 99999), 'serie' => '1',
        'valor_desconto' => 0, 'cancelada' => false, 'modelo' => '55', 'valor_total' => 0,
    ], $a));

    // Conta em 12m + 5 anos + total: fiscal, entrada, recente.
    $mk(['tipo_operacao' => 'entrada', 'origem_arquivo' => 'fiscal', 'data_emissao' => now()->subMonths(2)->toDateString(), 'valor_total' => 1000, 'chave_acesso' => str_pad('1', 44, '0', STR_PAD_LEFT)]);
    // Conta em 5 anos + total (não em 12m): fiscal, entrada, há 18 meses.
    $mk(['tipo_operacao' => 'entrada', 'origem_arquivo' => 'fiscal', 'data_emissao' => now()->subMonths(18)->toDateString(), 'valor_total' => 2000, 'chave_acesso' => str_pad('2', 44, '0', STR_PAD_LEFT)]);
    // Conta só no total (fora dos 5 anos): fiscal, entrada, há 7 anos.
    $mk(['tipo_operacao' => 'entrada', 'origem_arquivo' => 'fiscal', 'data_emissao' => now()->subYears(7)->toDateString(), 'valor_total' => 4000, 'chave_acesso' => str_pad('5', 44, '0', STR_PAD_LEFT)]);
    // Excluída: saída (não é compra).
    $mk(['tipo_operacao' => 'saida', 'origem_arquivo' => 'fiscal', 'data_emissao' => now()->subMonths(1)->toDateString(), 'valor_total' => 500, 'chave_acesso' => str_pad('3', 44, '0', STR_PAD_LEFT)]);
    // Excluída: origem PIS/COFINS (evita dupla contagem com fiscal).
    $mk(['tipo_operacao' => 'entrada', 'origem_arquivo' => 'contribuicoes', 'data_emissao' => now()->subMonths(1)->toDateString(), 'valor_total' => 800, 'chave_acesso' => str_pad('4', 44, '0', STR_PAD_LEFT)]);

    $this->svc->recalcular($this->user->id);

    $alerta = Alerta::where('tipo', 'certidao_positiva')->where('participante_id', $pid)->first();

    expect((float) $alerta->detalhes['valor_total'])->toBe(7000.0)   // 1000 + 2000 + 4000
        ->and((float) $alerta->detalhes['valor_12m'])->toBe(1000.0)  // só a recente
        ->and((float) $alerta->detalhes['valor_5anos'])->toBe(3000.0) // recente + 18 meses, não a de 7 anos
        ->and($alerta->descricao)->toContain('R$ 1.000,00')          // 12m
        ->and($alerta->descricao)->toContain('R$ 3.000,00')          // 5 anos (glosável)
        ->and($alerta->descricao)->toContain('R$ 7.000,00');         // total
});

it('resolve o alerta quando a certidão deixa de ser positiva numa reconsulta', function () {
    $pid = ($this->mkParticipante)('FORNECEDOR REGULARIZOU', '33333333000133');
    $score = ParticipanteScore::create([
        'user_id' => $this->user->id,
        'participante_id' => $pid,
        'score_cnd_estadual' => 70,
        'classificacao' => 'alto',
        'dados_consultados' => ['cnd_estadual' => ['status' => 'Positiva']],
    ]);

    $this->svc->recalcular($this->user->id);
    expect(Alerta::where('tipo', 'certidao_positiva')->where('status', 'ativo')->count())->toBe(1);

    // Regularizou: subscore volta a 0 → detector não acha → auto-resolve.
    $score->update(['score_cnd_estadual' => 0, 'classificacao' => 'baixo']);
    $this->svc->recalcular($this->user->id);

    $alerta = Alerta::where('tipo', 'certidao_positiva')->first();
    expect($alerta->status)->toBe('resolvido');
});
