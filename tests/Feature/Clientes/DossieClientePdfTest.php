<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\MonitoramentoPlano;
use App\Models\User;
use App\Services\Clientes\DossieClienteBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->c = Cliente::create([
        'user_id' => $this->user->id, 'razao_social' => 'CLI DOSSIE PDF', 'documento' => '33333333000133',
        'tipo_pessoa' => 'PJ', 'is_empresa_propria' => false, 'uf' => 'SP',
    ]);
    $plano = MonitoramentoPlano::ativos()->first() ?? MonitoramentoPlano::create([
        'nome' => 'Gratuito', 'codigo' => 'gratuito', 'ativo' => true, 'creditos_por_consulta' => 0, 'consultas_incluidas' => [], 'etapas' => [],
    ]);
    $lote = ConsultaLote::create([
        'user_id' => $this->user->id, 'plano_id' => $plano->id, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 0, 'tab_id' => 'tab-clipdf-'.uniqid(), 'processado_em' => now(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $lote->id, 'cliente_id' => $this->c->id, 'status' => ConsultaResultado::STATUS_SUCESSO,
        'resultado_dados' => ['situacao_cadastral' => 'ATIVA', 'cnd_federal' => ['status' => 'Negativa']],
        'consultado_em' => now(),
    ]);
});

it('baixa o dossie do cliente em pdf (dono)', function () {
    $resp = $this->actingAs($this->user)->get("/app/cliente/{$this->c->id}/dossie");
    $resp->assertOk();
    $resp->assertHeader('content-type', 'application/pdf');
});

it('bloqueia dossie de cliente de outro usuario', function () {
    $outro = User::factory()->create();
    $this->actingAs($outro)->get("/app/cliente/{$this->c->id}/dossie")->assertNotFound();
});

it('a view do dossie do cliente renderiza secoes e detalhamento do score', function () {
    $dados = app(DossieClienteBuilder::class)->montar($this->c);
    $html = view('reports.dossie.cliente', $dados)->render();
    expect($html)->toContain('CLI DOSSIE PDF')
        ->and($html)->toContain('Movimentações')
        ->and($html)->toContain('Regularidade')
        ->and($html)->toContain('Detalhamento')
        ->and($html)->toContain('Subscore'); // tabela do score detalhado
});
