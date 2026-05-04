<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use App\Services\Bi\BiConsultasEfdService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function consultasEfdCliente(User $user, string $documento = '00000000000191'): Cliente
{
    return Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => $documento,
        'razao_social' => 'Empresa Base',
        'nome' => 'Empresa Base',
        'is_empresa_propria' => true,
        'ativo' => true,
    ]);
}

function consultasEfdPlano(): MonitoramentoPlano
{
    return MonitoramentoPlano::firstOrCreate([
        'codigo' => 'validacao',
    ], [
        'nome' => 'Validacao',
        'descricao' => 'Plano de validacao',
        'consultas_incluidas' => ['dados_cadastrais'],
        'etapas' => [['numero' => 1, 'label' => 'Preparando consulta']],
        'custo_creditos' => 5,
        'is_gratuito' => false,
        'is_active' => true,
        'ordem' => 1,
    ]);
}

function consultasEfdImportacao(User $user, Cliente $cliente): EfdImportacao
{
    return EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'status' => 'concluido',
    ]);
}

function consultasEfdParticipante(User $user, Cliente $cliente, string $documento, string $razao, array $extra = []): Participante
{
    return Participante::create(array_merge([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'documento' => $documento,
        'razao_social' => $razao,
        'origem_tipo' => 'MANUAL',
    ], $extra));
}

function consultasEfdLote(User $user, MonitoramentoPlano $plano): ConsultaLote
{
    return ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => $plano->id,
        'status' => 'finalizado',
        'total_participantes' => 1,
        'creditos_cobrados' => 5,
        'tab_id' => (string) fake()->uuid(),
        'processado_em' => now(),
    ]);
}

function consultasEfdResultado(ConsultaLote $lote, Participante $participante, array $dados, ?\Illuminate\Support\Carbon $consultadoEm = null): ConsultaResultado
{
    return ConsultaResultado::create([
        'consulta_lote_id' => $lote->id,
        'participante_id' => $participante->id,
        'resultado_dados' => $dados,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'consultado_em' => $consultadoEm ?? now(),
    ]);
}

function consultasEfdNota(User $user, Cliente $cliente, EfdImportacao $importacao, Participante $participante, string $tipoOperacao, float $valor, string $data = '2026-05-01'): EfdNota
{
    return EfdNota::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'participante_id' => $participante->id,
        'importacao_id' => $importacao->id,
        'chave_acesso' => str_pad((string) fake()->unique()->numberBetween(1, 999999999), 44, '0', STR_PAD_LEFT),
        'modelo' => '55',
        'numero' => fake()->numberBetween(1, 9999),
        'serie' => '1',
        'data_emissao' => $data,
        'tipo_operacao' => $tipoOperacao,
        'valor_total' => $valor,
        'valor_desconto' => 0,
        'metadados' => [],
    ]);
}

beforeEach(function () {
    $this->service = app(BiConsultasEfdService::class);
});

it('lista apenas participantes consultados com movimentacao EFD', function () {
    $user = User::factory()->create();
    $cliente = consultasEfdCliente($user);
    $plano = consultasEfdPlano();
    $importacao = consultasEfdImportacao($user, $cliente);

    $comNotas = consultasEfdParticipante($user, $cliente, '11111111000191', 'Fornecedor Com Notas');
    $semNotas = consultasEfdParticipante($user, $cliente, '22222222000191', 'Fornecedor Sem Notas');

    consultasEfdResultado(consultasEfdLote($user, $plano), $comNotas, [
        'regime_tributario' => 'Lucro Presumido',
        'situacao_cadastral' => 'ATIVA',
    ]);
    consultasEfdResultado(consultasEfdLote($user, $plano), $semNotas, [
        'regime_tributario' => 'Simples Nacional',
        'situacao_cadastral' => 'ATIVA',
        'simples_nacional' => true,
    ]);

    consultasEfdNota($user, $cliente, $importacao, $comNotas, 'entrada', 1000);
    consultasEfdNota($user, $cliente, $importacao, $comNotas, 'saida', 250);

    $painel = $this->service->painel($user->id);

    expect($painel['participantes'])->toHaveCount(1)
        ->and($painel['participantes'][0]['razao_social'])->toBe('Fornecedor Com Notas')
        ->and($painel['kpis']['participantes_consultados'])->toBe(2)
        ->and($painel['kpis']['participantes_com_movimentacao'])->toBe(1)
        ->and($painel['kpis']['participantes_sem_movimentacao'])->toBe(1)
        ->and((float) $painel['participantes'][0]['valor_total_efd'])->toBe(1250.0)
        ->and((float) $painel['participantes'][0]['valor_entradas'])->toBe(1000.0)
        ->and((float) $painel['participantes'][0]['valor_saidas'])->toBe(250.0);
});

it('aplica filtros por cliente e periodo', function () {
    $user = User::factory()->create();
    $clienteA = consultasEfdCliente($user, '00000000000191');
    $clienteB = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '99999999000191',
        'razao_social' => 'Filial',
        'nome' => 'Filial',
        'is_empresa_propria' => false,
        'ativo' => true,
    ]);
    $plano = consultasEfdPlano();
    $importacaoA = consultasEfdImportacao($user, $clienteA);
    $importacaoB = consultasEfdImportacao($user, $clienteB);
    $participante = consultasEfdParticipante($user, $clienteA, '33333333000191', 'Fornecedor Filtrado');

    consultasEfdResultado(consultasEfdLote($user, $plano), $participante, [
        'regime_tributario' => 'Lucro Real',
        'situacao_cadastral' => 'ATIVA',
    ]);

    consultasEfdNota($user, $clienteA, $importacaoA, $participante, 'entrada', 100, '2026-04-15');
    consultasEfdNota($user, $clienteB, $importacaoB, $participante, 'entrada', 900, '2026-05-15');

    $painelCliente = $this->service->painel($user->id, ['cliente_id' => $clienteB->id]);
    $painelPeriodo = $this->service->painel($user->id, ['data_inicio' => '2026-05-01', 'data_fim' => '2026-05-31']);

    expect($painelCliente['participantes'])->toHaveCount(1)
        ->and((float) $painelCliente['participantes'][0]['valor_total_efd'])->toBe(900.0)
        ->and((float) $painelPeriodo['participantes'][0]['valor_total_efd'])->toBe(900.0);
});

it('isola dados por user_id', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();
    $plano = consultasEfdPlano();

    $clienteAlice = consultasEfdCliente($alice, '10101010000191');
    $impAlice = consultasEfdImportacao($alice, $clienteAlice);
    $partAlice = consultasEfdParticipante($alice, $clienteAlice, '44444444000191', 'Participante Alice');
    consultasEfdResultado(consultasEfdLote($alice, $plano), $partAlice, [
        'regime_tributario' => 'Lucro Presumido',
        'situacao_cadastral' => 'ATIVA',
    ]);
    consultasEfdNota($alice, $clienteAlice, $impAlice, $partAlice, 'entrada', 500);

    $clienteBob = consultasEfdCliente($bob, '20202020000191');
    $impBob = consultasEfdImportacao($bob, $clienteBob);
    $partBob = consultasEfdParticipante($bob, $clienteBob, '55555555000191', 'Participante Bob');
    consultasEfdResultado(consultasEfdLote($bob, $plano), $partBob, [
        'regime_tributario' => 'Simples Nacional',
        'situacao_cadastral' => 'ATIVA',
        'simples_nacional' => true,
    ]);
    consultasEfdNota($bob, $clienteBob, $impBob, $partBob, 'entrada', 900);

    $painelAlice = $this->service->painel($alice->id);

    expect($painelAlice['participantes'])->toHaveCount(1)
        ->and($painelAlice['participantes'][0]['razao_social'])->toBe('Participante Alice')
        ->and((float) $painelAlice['participantes'][0]['valor_total_efd'])->toBe(500.0);
});

it('sinaliza participante com consulta antiga (>=90 dias)', function () {
    $user = User::factory()->create();
    $cliente = consultasEfdCliente($user);
    $plano = consultasEfdPlano();
    $importacao = consultasEfdImportacao($user, $cliente);
    $participante = consultasEfdParticipante($user, $cliente, '88888888000191', 'Fornecedor Antigo');

    consultasEfdResultado(
        consultasEfdLote($user, $plano),
        $participante,
        ['regime_tributario' => 'Lucro Presumido', 'situacao_cadastral' => 'ATIVA'],
        now()->subDays(120),
    );
    consultasEfdNota($user, $cliente, $importacao, $participante, 'entrada', 100);

    $painel = $this->service->painel($user->id);

    $labels = collect($painel['participantes'][0]['parecer_resumo'])->pluck('label');

    expect($labels->contains('Consulta antiga'))->toBeTrue();
});

it('não sinaliza consulta antiga quando dentro da janela de 90 dias', function () {
    $user = User::factory()->create();
    $cliente = consultasEfdCliente($user);
    $plano = consultasEfdPlano();
    $importacao = consultasEfdImportacao($user, $cliente);
    $participante = consultasEfdParticipante($user, $cliente, '99999999000191', 'Fornecedor Recente');

    consultasEfdResultado(
        consultasEfdLote($user, $plano),
        $participante,
        ['regime_tributario' => 'Lucro Presumido', 'situacao_cadastral' => 'ATIVA'],
        now()->subDays(30),
    );
    consultasEfdNota($user, $cliente, $importacao, $participante, 'entrada', 100);

    $painel = $this->service->painel($user->id);

    $labels = collect($painel['participantes'][0]['parecer_resumo'])->pluck('label');

    expect($labels->contains('Consulta antiga'))->toBeFalse();
});

it('sinaliza concentração quando participante representa >=30% do total visível', function () {
    $user = User::factory()->create();
    $cliente = consultasEfdCliente($user);
    $plano = consultasEfdPlano();
    $importacao = consultasEfdImportacao($user, $cliente);

    $top = consultasEfdParticipante($user, $cliente, '11111111000110', 'Top Fornecedor');
    $medio = consultasEfdParticipante($user, $cliente, '22222222000220', 'Medio Fornecedor');
    $pequeno = consultasEfdParticipante($user, $cliente, '33333333000330', 'Pequeno Fornecedor');

    foreach ([$top, $medio, $pequeno] as $p) {
        consultasEfdResultado(consultasEfdLote($user, $plano), $p, [
            'regime_tributario' => 'Lucro Presumido',
            'situacao_cadastral' => 'ATIVA',
        ]);
    }

    consultasEfdNota($user, $cliente, $importacao, $top, 'entrada', 1000);
    consultasEfdNota($user, $cliente, $importacao, $medio, 'entrada', 200);
    consultasEfdNota($user, $cliente, $importacao, $pequeno, 'entrada', 100);

    $painel = $this->service->painel($user->id);
    $linhas = collect($painel['participantes'])->keyBy('razao_social');

    $topLinha = $linhas->get('Top Fornecedor');
    $medioLinha = $linhas->get('Medio Fornecedor');

    expect($topLinha['concentracao_percentual'])->toEqualWithDelta(76.92, 0.01)
        ->and($medioLinha['concentracao_percentual'])->toEqualWithDelta(15.38, 0.01);

    $topLabels = collect($topLinha['parecer_resumo'])->pluck('label');
    $medioLabels = collect($medioLinha['parecer_resumo'])->pluck('label');

    expect($topLabels->contains(fn (string $l) => str_starts_with($l, 'Concentração')))->toBeTrue()
        ->and($medioLabels->contains(fn (string $l) => str_starts_with($l, 'Concentração')))->toBeFalse();
});

it('não sinaliza concentração abaixo do threshold quando carteira é equilibrada', function () {
    $user = User::factory()->create();
    $cliente = consultasEfdCliente($user);
    $plano = consultasEfdPlano();
    $importacao = consultasEfdImportacao($user, $cliente);

    $a = consultasEfdParticipante($user, $cliente, '44444444000440', 'A');
    $b = consultasEfdParticipante($user, $cliente, '55555555000550', 'B');
    $c = consultasEfdParticipante($user, $cliente, '66666666000660', 'C');
    $d = consultasEfdParticipante($user, $cliente, '77777777000770', 'D');

    foreach ([$a, $b, $c, $d] as $p) {
        consultasEfdResultado(consultasEfdLote($user, $plano), $p, [
            'regime_tributario' => 'Lucro Presumido',
            'situacao_cadastral' => 'ATIVA',
        ]);
        consultasEfdNota($user, $cliente, $importacao, $p, 'entrada', 250);
    }

    $painel = $this->service->painel($user->id);

    foreach ($painel['participantes'] as $linha) {
        $labels = collect($linha['parecer_resumo'])->pluck('label');
        expect($labels->contains(fn (string $l) => str_starts_with($l, 'Concentração')))->toBeFalse()
            ->and($linha['concentracao_percentual'])->toEqualWithDelta(25.0, 0.01);
    }
});

it('mantém concentracao_percentual em zero quando o painel não tem movimentação', function () {
    $user = User::factory()->create();
    $cliente = consultasEfdCliente($user);
    $plano = consultasEfdPlano();
    $participante = consultasEfdParticipante($user, $cliente, '88880000088000', 'Sem Movimentação');

    consultasEfdResultado(consultasEfdLote($user, $plano), $participante, [
        'regime_tributario' => 'Lucro Presumido',
        'situacao_cadastral' => 'ATIVA',
    ]);

    $painel = $this->service->painel($user->id);

    expect($painel['participantes'])->toBeEmpty();
});

it('associa consulta e EFD pelo documento mesmo com participante_id diferente', function () {
    $user = User::factory()->create();
    $cliente = consultasEfdCliente($user);
    $plano = consultasEfdPlano();
    $importacao = consultasEfdImportacao($user, $cliente);

    $participanteConsulta = consultasEfdParticipante(
        $user,
        $cliente,
        '77777777000191',
        'Empresa Canonica',
        ['origem_tipo' => 'MANUAL']
    );

    $participanteEfd = consultasEfdParticipante(
        $user,
        $cliente,
        '77.777.777/0001-91',
        'Empresa Canonica',
        ['origem_tipo' => 'SPED EFD ICMS/IPI']
    );

    consultasEfdResultado(consultasEfdLote($user, $plano), $participanteConsulta, [
        'regime_tributario' => 'Lucro Presumido',
        'situacao_cadastral' => 'ATIVA',
    ]);

    consultasEfdNota($user, $cliente, $importacao, $participanteEfd, 'entrada', 300);
    consultasEfdNota($user, $cliente, $importacao, $participanteEfd, 'saida', 200);

    $painel = $this->service->painel($user->id);

    expect($painel['participantes'])->toHaveCount(1)
        ->and($painel['participantes'][0]['documento'])->toBe('77.777.777/0001-91')
        ->and($painel['participantes'][0]['participante_id'])->toBe($participanteEfd->id)
        ->and($painel['participantes'][0]['participante_consulta_id'])->toBe($participanteConsulta->id)
        ->and($painel['participantes'][0]['regime_tributario'])->toBe('Lucro Presumido')
        ->and((float) $painel['participantes'][0]['valor_total_efd'])->toBe(500.0)
        ->and($painel['kpis']['participantes_consultados'])->toBe(1)
        ->and($painel['kpis']['participantes_com_movimentacao'])->toBe(1);
});
