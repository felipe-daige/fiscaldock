<?php
// tests/Feature/Admin/AdminAcaoServiceTest.php
use App\Models\AdminActionLog;
use App\Models\User;
use App\Services\Admin\AdminAcaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->svc = app(AdminAcaoService::class);
    $this->admin = User::factory()->create(['is_admin' => true]);
});

it('creditar positivo adiciona saldo e registra audit', function () {
    $alvo = User::factory()->create(['credits' => 10]);

    $log = $this->svc->creditar($this->admin, $alvo, 50, 'cortesia');

    expect($alvo->fresh()->credits)->toBe(60);
    expect($log->acao)->toBe('creditar');
    expect($log->detalhe['valor'])->toBe(50);
    expect($log->detalhe['saldo_depois'])->toBe(60);
    expect(AdminActionLog::where('target_user_id', $alvo->id)->count())->toBe(1);
});

it('creditar negativo debita via deduct e registra acao debitar', function () {
    $alvo = User::factory()->create(['credits' => 100]);

    $log = $this->svc->creditar($this->admin, $alvo, -30, 'estorno manual');

    expect($alvo->fresh()->credits)->toBe(70);
    expect($log->acao)->toBe('debitar');
});

it('creditar com valor zero lança', function () {
    $alvo = User::factory()->create(['credits' => 10]);
    expect(fn () => $this->svc->creditar($this->admin, $alvo, 0, 'x'))
        ->toThrow(InvalidArgumentException::class);
});

it('debito acima do saldo lança e não muta', function () {
    $alvo = User::factory()->create(['credits' => 10]);
    expect(fn () => $this->svc->creditar($this->admin, $alvo, -50, 'x'))
        ->toThrow(RuntimeException::class);
    expect($alvo->fresh()->credits)->toBe(10);
});

it('bloquear seta bloqueado_em e desbloquear limpa, ambos com audit', function () {
    $alvo = User::factory()->create();

    $this->svc->bloquear($this->admin, $alvo, 'fraude suspeita');
    expect($alvo->fresh()->bloqueado_em)->not->toBeNull();

    $this->svc->desbloquear($this->admin, $alvo, 'resolvido');
    expect($alvo->fresh()->bloqueado_em)->toBeNull();

    expect(AdminActionLog::where('target_user_id', $alvo->id)->pluck('acao')->all())
        ->toBe(['bloquear', 'desbloquear']);
});

it('definirAdmin promove e rebaixa', function () {
    $alvo = User::factory()->create(['is_admin' => false]);

    $this->svc->definirAdmin($this->admin, $alvo, true, 'novo operador');
    expect($alvo->fresh()->is_admin)->toBeTrue();

    $this->svc->definirAdmin($this->admin, $alvo, false, 'saiu');
    expect($alvo->fresh()->is_admin)->toBeFalse();
});

it('admin não pode bloquear nem se auto-rebaixar', function () {
    expect(fn () => $this->svc->bloquear($this->admin, $this->admin, 'x'))
        ->toThrow(InvalidArgumentException::class);
    expect(fn () => $this->svc->definirAdmin($this->admin, $this->admin, false, 'x'))
        ->toThrow(InvalidArgumentException::class);
});
