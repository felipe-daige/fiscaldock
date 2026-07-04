<?php

use App\Models\Alerta;
use App\Models\AlertaAuditoria;
use App\Models\User;
use App\Services\AlertaCentralService;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['name' => 'Fulano de Tal']);
    $this->svc = app(AlertaCentralService::class);

    $this->mkAlerta = fn (array $attrs = []) => Alerta::create(array_merge([
        'user_id' => $this->user->id,
        'tipo' => 'notas_duplicadas',
        'categoria' => 'notas_fiscais',
        'severidade' => 'media',
        'titulo' => 'Notas duplicadas',
        'descricao' => 'd',
        'status' => 'ativo',
        'hash' => hash('sha256', 'a'.uniqid('', true)),
    ], $attrs));
});

it('criar alerta registra auditoria "criado"', function () {
    $alerta = ($this->mkAlerta)();

    $aud = AlertaAuditoria::where('alerta_id', $alerta->id)->get();
    expect($aud)->toHaveCount(1)
        ->and($aud->first()->acao)->toBe('criado')
        ->and($aud->first()->para_status)->toBe('ativo')
        ->and($aud->first()->user_id)->toBeNull(); // sem contexto de ator
});

it('resolver via marcarStatus grava quem, nota e a transição', function () {
    $alerta = ($this->mkAlerta)();

    $this->svc->marcarStatus($alerta->id, $this->user->id, 'resolvido', 'feito no ERP');

    $aud = AlertaAuditoria::where('alerta_id', $alerta->id)->where('acao', 'resolvido')->first();
    expect($aud)->not->toBeNull()
        ->and($aud->de_status)->toBe('ativo')
        ->and($aud->para_status)->toBe('resolvido')
        ->and($aud->user_id)->toBe($this->user->id)
        ->and($aud->ator_nome)->toBe('Fulano de Tal')
        ->and($aud->notas)->toBe('feito no ERP');
});

it('reabrir (resolvido → ativo) por usuário registra "reaberto"', function () {
    $alerta = ($this->mkAlerta)(['status' => 'resolvido']);

    $this->svc->marcarStatus($alerta->id, $this->user->id, 'ativo', null);

    $aud = AlertaAuditoria::where('alerta_id', $alerta->id)->latest('id')->first();
    expect($aud->acao)->toBe('reaberto')
        ->and($aud->ator_nome)->toBe('Fulano de Tal');
});

it('auto-resolve no recalcular registra "auto_resolvido" pelo Sistema (sem ator)', function () {
    // Alerta órfão (hash que o recalcular nunca produz) → auto-resolvido.
    $alerta = ($this->mkAlerta)(['hash' => hash('sha256', 'orfao-'.$this->user->id)]);
    AlertaAuditoria::where('alerta_id', $alerta->id)->delete(); // limpa o "criado"

    $this->svc->recalcular($this->user->id);

    expect($alerta->fresh()->status)->toBe('resolvido');
    $aud = AlertaAuditoria::where('alerta_id', $alerta->id)->latest('id')->first();
    expect($aud->acao)->toBe('auto_resolvido')
        ->and($aud->user_id)->toBeNull()
        ->and($aud->ator_nome)->toBeNull(); // Sistema
});

it('página de histórico lista os eventos do usuário e filtra por ação', function () {
    $alerta = ($this->mkAlerta)();
    $this->svc->marcarStatus($alerta->id, $this->user->id, 'resolvido', 'ok');

    actingAs($this->user)
        ->get('/app/alertas/historico')
        ->assertOk()
        ->assertSee('Histórico de Alertas')
        ->assertSee('Resolvido')
        ->assertSee('Fulano de Tal');

    // Filtro por ação inexistente nos dados não quebra.
    actingAs($this->user)
        ->get('/app/alertas/historico?acao=ignorado')
        ->assertOk();
});

it('histórico não vaza eventos de outro usuário', function () {
    $alerta = ($this->mkAlerta)();
    $this->svc->marcarStatus($alerta->id, $this->user->id, 'resolvido', 'segredo');

    $outro = User::factory()->create();
    actingAs($outro)
        ->get('/app/alertas/historico')
        ->assertOk()
        ->assertDontSee('segredo');
});
