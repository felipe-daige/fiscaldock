<?php

use App\Actions\Monitoramento\DispararConsultaMonitoramento;
use App\Models\Cliente;
use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoConsulta;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use Database\Seeders\MonitoramentoPlanoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(MonitoramentoPlanoSeeder::class);
    $this->plano = MonitoramentoPlano::query()->where('custo_creditos', '>', 0)->first();
    config()->set('services.webhook.monitoramento_cnpj_participante_url', 'https://n8n.test/monitoramento/participante');
    config()->set('services.webhook.monitoramento_cnpj_cliente_url', 'https://n8n.test/monitoramento/cliente');
});

it('debita crédito, cria consulta pendente e dispara o webhook de participante', function () {
    Http::fake(['n8n.test/*' => Http::response(['ok' => true], 200)]);

    $user = User::factory()->create(['credits' => 100]);
    $participante = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000181',
        'tipo_documento' => 'PJ', 'razao_social' => 'Fornecedor X',
    ]);
    $assinatura = MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'participante_id' => $participante->id,
        'plano_id' => $this->plano->id, 'status' => 'ativo', 'frequencia_dias' => 30,
    ]);

    $consulta = app(DispararConsultaMonitoramento::class)->execute($assinatura);

    expect($consulta->status)->toBe('pendente');
    expect($consulta->tipo)->toBe('assinatura');
    expect($consulta->creditos_cobrados)->toBe((int) $this->plano->custo_creditos);
    expect($consulta->assinatura_id)->toBe($assinatura->id);
    expect($user->fresh()->credits)->toBe(100 - (int) $this->plano->custo_creditos);

    Http::assertSent(fn ($req) => $req->url() === 'https://n8n.test/monitoramento/participante'
        && $req['consulta_id'] === $consulta->id
        && $req['tipo_alvo'] === 'participante');
});

it('roteia para o webhook de cliente quando o alvo é cliente', function () {
    Http::fake(['n8n.test/*' => Http::response(['ok' => true], 200)]);

    $user = User::factory()->create(['credits' => 100]);
    $cliente = Cliente::create([
        'user_id' => $user->id, 'documento' => '44555666000199', 'razao_social' => 'Cliente Y',
    ]);
    $assinatura = MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id,
        'plano_id' => $this->plano->id, 'status' => 'ativo', 'frequencia_dias' => 30,
    ]);

    app(DispararConsultaMonitoramento::class)->execute($assinatura);

    Http::assertSent(fn ($req) => $req->url() === 'https://n8n.test/monitoramento/cliente'
        && $req['tipo_alvo'] === 'cliente');
});

it('marca consulta como erro e estorna crédito quando o webhook falha', function () {
    Http::fake(['n8n.test/*' => Http::response('erro', 500)]);

    $user = User::factory()->create(['credits' => 100]);
    $participante = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000181',
        'tipo_documento' => 'PJ', 'razao_social' => 'Fornecedor X',
    ]);
    $assinatura = MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'participante_id' => $participante->id,
        'plano_id' => $this->plano->id, 'status' => 'ativo', 'frequencia_dias' => 30,
    ]);

    $consulta = app(DispararConsultaMonitoramento::class)->execute($assinatura);

    expect($consulta->fresh()->status)->toBe('erro');
    expect($consulta->fresh()->error_code)->toBe('webhook_dispatch_failed');
    expect($user->fresh()->credits)->toBe(100);
});

it('liga o retry ao parent_consulta_id', function () {
    Http::fake(['n8n.test/*' => Http::response(['ok' => true], 200)]);

    $user = User::factory()->create(['credits' => 100]);
    $participante = Participante::create([
        'user_id' => $user->id, 'documento' => '11222333000181',
        'tipo_documento' => 'PJ', 'razao_social' => 'Fornecedor X',
    ]);
    $assinatura = MonitoramentoAssinatura::create([
        'user_id' => $user->id, 'participante_id' => $participante->id,
        'plano_id' => $this->plano->id, 'status' => 'ativo', 'frequencia_dias' => 30,
    ]);
    $original = MonitoramentoConsulta::create([
        'user_id' => $user->id, 'participante_id' => $participante->id,
        'plano_id' => $this->plano->id, 'assinatura_id' => $assinatura->id,
        'tipo' => 'assinatura', 'status' => 'erro', 'creditos_cobrados' => 5, 'executado_em' => now(),
    ]);

    $retry = app(DispararConsultaMonitoramento::class)->execute($assinatura, $original);

    expect($retry->parent_consulta_id)->toBe($original->id);
});
