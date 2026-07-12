<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\User;
use App\Notifications\AlertaDigestNotification;
use App\Notifications\AlertaImediatoNotification;
use App\Services\AlertaCentralService;
use Illuminate\Support\Facades\Notification;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
    $this->service = app(AlertaCentralService::class);
});

/** Cria um participante irregular COM nota EFD (o que o detector exige). */
function participanteIrregularComNota(User $user, Cliente $cliente, EfdImportacao $imp, string $doc, string $razao): Participante
{
    $p = Participante::create([
        'user_id' => $user->id,
        'documento' => $doc,
        'razao_social' => $razao,
        'situacao_cadastral' => 'CANCELADA',
    ]);

    EfdNota::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'importacao_id' => $imp->id,
        'participante_id' => $p->id,
        'numero' => random_int(1000, 999999),
        'serie' => '1',
        'modelo' => '55',
        'data_emissao' => '2026-01-15',
        'tipo_operacao' => 'entrada',
        'valor_total' => 500.00,
    ]);

    return $p;
}

function cenarioBase(): array
{
    $user = User::factory()->create(['alertas_operacionais' => true, 'alertas_monitoramento' => true]);
    $cliente = Cliente::create([
        'user_id' => $user->id, 'documento' => '11111111000191',
        'razao_social' => 'Minha Empresa', 'nome' => 'Minha Empresa', 'is_empresa_propria' => true,
    ]);
    // periodo_inicio/fim cobrindo a janela de 12 meses mata o detector de gap de
    // importação (senão ele injeta um alerta media que contamina a contagem).
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id,
        'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
        'periodo_inicio' => now()->subMonths(11)->startOfMonth()->toDateString(),
        'periodo_fim' => now()->startOfMonth()->toDateString(),
    ]);

    return [$user, $cliente, $imp];
}

/** Participante ATIVA com consulta vencida + nota → gera SÓ consulta_vencida (1 alerta media). */
function participanteConsultaVencida(User $user, Cliente $cliente, EfdImportacao $imp): Participante
{
    $p = Participante::create([
        'user_id' => $user->id,
        'documento' => '55443322000122',
        'razao_social' => 'Ativa Atrasada',
        'situacao_cadastral' => 'ATIVA',
        'ultima_consulta_em' => now()->subMonths(6),
    ]);

    EfdNota::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'importacao_id' => $imp->id,
        'participante_id' => $p->id, 'numero' => random_int(1000, 999999), 'serie' => '1',
        'modelo' => '55', 'data_emissao' => '2026-01-15', 'tipo_operacao' => 'entrada', 'valor_total' => 500.00,
    ]);

    return $p;
}

it('recalcular com VÁRIOS alertas alta manda 1 digest, não N imediatos', function () {
    [$user, $cliente, $imp] = cenarioBase();

    participanteIrregularComNota($user, $cliente, $imp, '99887766000155', 'Irregular Um');
    participanteIrregularComNota($user, $cliente, $imp, '88776655000144', 'Irregular Dois');
    participanteIrregularComNota($user, $cliente, $imp, '77665544000133', 'Irregular Tres');

    $this->service->recalcular($user->id);

    Notification::assertSentToTimes($user, AlertaDigestNotification::class, 1);
    Notification::assertNotSentTo($user, AlertaImediatoNotification::class);
});

it('recalcular com UM único alerta manda o imediato rico, não digest', function () {
    [$user, $cliente, $imp] = cenarioBase();

    // ATIVA com consulta vencida = exatamente 1 alerta (media), sem os 2 do irregular.
    participanteConsultaVencida($user, $cliente, $imp);

    $this->service->recalcular($user->id);

    Notification::assertSentToTimes($user, AlertaImediatoNotification::class, 1);
    Notification::assertNotSentTo($user, AlertaDigestNotification::class);
});

it('re-executar recalcular não reenvia (notificado_em já marcado)', function () {
    [$user, $cliente, $imp] = cenarioBase();

    participanteIrregularComNota($user, $cliente, $imp, '99887766000155', 'Irregular Um');
    participanteIrregularComNota($user, $cliente, $imp, '88776655000144', 'Irregular Dois');

    $this->service->recalcular($user->id);
    $this->service->recalcular($user->id); // 2ª varredura: nada novo

    Notification::assertSentToTimes($user, AlertaDigestNotification::class, 1);
});

it('toggle operacional desligado não manda digest', function () {
    [$user, $cliente, $imp] = cenarioBase();
    $user->update(['alertas_operacionais' => false]);

    participanteIrregularComNota($user, $cliente, $imp, '99887766000155', 'Irregular Um');
    participanteIrregularComNota($user, $cliente, $imp, '88776655000144', 'Irregular Dois');

    $this->service->recalcular($user->id);

    Notification::assertNothingSentTo($user);
});

it('monitoramento 1-a-1 (fora do recalcular) continua imediato', function () {
    $user = User::factory()->create(['alertas_monitoramento' => true]);

    $this->service->registrarAlertaMonitoramento([
        'user_id' => $user->id,
        'tipo' => 'cnpj_situacao_irregular',
        'severidade' => 'alta',
        'titulo' => 'CNPJ ficou irregular',
        'descricao' => 'Mudou para SUSPENSA.',
    ]);

    Notification::assertSentToTimes($user, AlertaImediatoNotification::class, 1);
    Notification::assertNotSentTo($user, AlertaDigestNotification::class);
});
