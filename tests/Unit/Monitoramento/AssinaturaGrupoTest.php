<?php

use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\ParticipanteGrupo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function agPlano(): MonitoramentoPlano
{
    return MonitoramentoPlano::porCodigo('licitacao') ?? MonitoramentoPlano::firstOrFail();
}

function agGrupoComMembros(User $user, int $n = 2): ParticipanteGrupo
{
    $grupo = ParticipanteGrupo::create(['user_id' => $user->id, 'nome' => 'Fornecedores']);
    foreach (range(1, $n) as $i) {
        $p = Participante::create([
            'user_id' => $user->id,
            'documento' => sprintf('112223330%03d81', $i),
            'razao_social' => "Membro {$i}",
            'uf' => 'SP',
        ]);
        $grupo->participantes()->attach($p->id);
    }

    return $grupo;
}

it('assinatura de grupo: alvoTipo, membros e custo do ciclo N×plano', function () {
    $user = User::factory()->create();
    $grupo = agGrupoComMembros($user, 2);

    $ass = MonitoramentoAssinatura::create([
        'user_id' => $user->id,
        'grupo_id' => $grupo->id,
        'plano_id' => agPlano()->id,
        'status' => 'ativo',
        'frequencia_dias' => 30,
    ]);

    expect($ass->alvoTipo())->toBe('grupo');
    expect($ass->grupo->id)->toBe($grupo->id);
    expect($ass->membrosDoGrupo())->toHaveCount(2);
    expect($ass->custoCiclo())->toBe(round(2 * (float) agPlano()->custo_creditos, 2));
});

it('membros do grupo são DINÂMICOS: adicionado depois entra, removido sai', function () {
    $user = User::factory()->create();
    $grupo = agGrupoComMembros($user, 1);
    $ass = MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'grupo_id' => $grupo->id,
        'plano_id' => agPlano()->id, 'status' => 'ativo', 'frequencia_dias' => 30,
    ]);

    $novo = Participante::create([
        'user_id' => $user->id, 'documento' => '11444777000161',
        'razao_social' => 'Novo Membro', 'uf' => 'SP',
    ]);
    $grupo->participantes()->attach($novo->id);
    expect($ass->fresh()->membrosDoGrupo())->toHaveCount(2);

    $grupo->participantes()->detach($novo->id);
    expect($ass->fresh()->membrosDoGrupo())->toHaveCount(1);
});

it('custoCiclo de participante/cliente = custo do plano (comportamento atual)', function () {
    $user = User::factory()->create();
    $p = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000181',
        'razao_social' => 'Solo', 'uf' => 'SP',
    ]);
    $ass = MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'participante_id' => $p->id,
        'plano_id' => agPlano()->id, 'status' => 'ativo', 'frequencia_dias' => 30,
    ]);

    expect($ass->alvoTipo())->toBe('participante');
    expect($ass->custoCiclo())->toBe(round((float) agPlano()->custo_creditos, 2));
});
