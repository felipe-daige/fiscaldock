<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(fn () => $this->seed(\Database\Seeders\MonitoramentoPlanoSeeder::class));

function clienteComConsulta(User $user, string $documento, string $razao, ?string $cndStatus, ?int $diasAtras = 0): Cliente
{
    $cliente = Cliente::create([
        'user_id' => $user->id,
        'documento' => $documento,
        'razao_social' => $razao,
        'ativo' => true,
    ]);

    if ($cndStatus !== null) {
        $participante = Participante::create([
            'user_id' => $user->id,
            'documento' => $documento,
            'razao_social' => $razao,
        ]);
        $plano = MonitoramentoPlano::porCodigo('gratuito') ?? MonitoramentoPlano::firstOrFail();
        $lote = ConsultaLote::create([
            'user_id' => $user->id, 'plano_id' => $plano->id, 'status' => ConsultaLote::STATUS_FINALIZADO,
            'total_participantes' => 1, 'creditos_cobrados' => 0, 'tab_id' => 'tab-'.uniqid(), 'processado_em' => now(),
        ]);
        ConsultaResultado::create([
            'consulta_lote_id' => $lote->id,
            'participante_id' => $participante->id,
            'status' => ConsultaResultado::STATUS_SUCESSO,
            'resultado_dados' => ['cnd_federal' => ['status' => $cndStatus]],
            'consultado_em' => now()->subDays($diasAtras),
        ]);
    }

    return $cliente;
}

it('filtra clientes por regularidade = irregular', function () {
    $user = User::factory()->create();
    clienteComConsulta($user, '11444777000161', 'ALPHA IRREGULAR', 'Positiva');
    clienteComConsulta($user, '22333444000155', 'BETA REGULAR', 'Negativa');

    actingAs($user)->get('/app/clientes?regularidade=irregular')
        ->assertOk()
        ->assertSee('ALPHA IRREGULAR')
        ->assertDontSee('BETA REGULAR');
});

it('filtra clientes por regularidade = nao_consultado', function () {
    $user = User::factory()->create();
    clienteComConsulta($user, '11444777000161', 'CONSULTADO SA', 'Negativa');
    clienteComConsulta($user, '22333444000155', 'NUNCA SA', null);

    actingAs($user)->get('/app/clientes?regularidade=nao_consultado')
        ->assertOk()
        ->assertSee('NUNCA SA')
        ->assertDontSee('CONSULTADO SA');
});

it('filtra clientes por status_consulta = desatualizada', function () {
    $user = User::factory()->create();
    clienteComConsulta($user, '11444777000161', 'VELHO SA', 'Negativa', 40);
    clienteComConsulta($user, '22333444000155', 'NOVO SA', 'Negativa', 3);

    actingAs($user)->get('/app/clientes?status_consulta=desatualizada')
        ->assertOk()
        ->assertSee('VELHO SA')
        ->assertDontSee('NOVO SA');
});

it('ignora regularidade invalida sem quebrar', function () {
    $user = User::factory()->create();
    clienteComConsulta($user, '11444777000161', 'QUALQUER SA', 'Negativa');

    actingAs($user)->get('/app/clientes?regularidade=hackzor')
        ->assertOk()
        ->assertSee('QUALQUER SA');
});
