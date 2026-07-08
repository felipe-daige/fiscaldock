<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    [$this->userId, $this->clienteId] = montarMassaFechamento();
    // Export é recurso pago (gate de entitlements): trial ativo libera.
    \App\Models\User::find($this->userId)->forceFill([
        'trial_used' => true, 'trial_expires_at' => now()->addDays(30), 'trial_credits_remaining' => 50,
    ])->save();
});

it('baixa o PDF do fechamento para o dono com dados', function () {
    $user = \App\Models\User::find($this->userId);
    $resp = $this->actingAs($user)->get("/app/resumo-fiscal/exportar-pdf?cliente_id={$this->clienteId}&competencia=2024-01");

    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('application/pdf');
    expect(substr($resp->getContent(), 0, 4))->toBe('%PDF');
});

it('exige cliente_id e competencia (422 sem eles)', function () {
    $user = \App\Models\User::find($this->userId);
    $this->actingAs($user)->get('/app/resumo-fiscal/exportar-pdf')->assertStatus(422);
    $this->actingAs($user)->get("/app/resumo-fiscal/exportar-pdf?cliente_id={$this->clienteId}")->assertStatus(422);
});

it('nao exporta cliente de outro usuario (404)', function () {
    [$outroUserId, $outroClienteId] = montarMassaFechamento();
    $user = \App\Models\User::find($this->userId);

    // cliente do outro user, sob a sessão deste user → 404 no validarParams
    $this->actingAs($user)->get("/app/resumo-fiscal/exportar-pdf?cliente_id={$outroClienteId}&competencia=2024-01")
        ->assertStatus(404);
});

it('renderiza a view do relatorio com as secoes do fechamento', function () {
    $dados = [
        'cliente' => \App\Models\Cliente::find($this->clienteId),
        'competencia' => '2024-01',
        'competenciaLabel' => 'janeiro/2024',
        'geradoEm' => now(),
        'resumo' => app(\App\Services\ResumoFiscalService::class)->getResumoExecutivo($this->userId, $this->clienteId, '2024-01'),
        'aRecolher' => app(\App\Services\ResumoFiscalService::class)->getARecolherData($this->userId, $this->clienteId, '2024-01'),
        'cruzamentos' => app(\App\Services\ResumoFiscalService::class)->getCruzamentosData($this->userId, $this->clienteId, '2024-01'),
        'alertas' => app(\App\Services\ResumoFiscalService::class)->getAlertasFiscaisData($this->userId, $this->clienteId, '2024-01'),
        'icms' => app(\App\Services\ResumoFiscalService::class)->getApuracaoIcmsData($this->userId, $this->clienteId, '2024-01'),
        'pisCofins' => app(\App\Services\ResumoFiscalService::class)->getApuracaoPisCofinsData($this->userId, $this->clienteId, '2024-01'),
        'retencoes' => app(\App\Services\ResumoFiscalService::class)->getRetencoesData($this->userId, $this->clienteId, '2024-01'),
        'hashDoc' => 'ABC123',
    ];

    $html = view('reports.resumo-fiscal', $dados)->render();

    expect($html)->toContain('EMPRESA TESTE');       // razão social
    expect($html)->toContain('A Recolher');          // seção
    expect($html)->toContain('Espelho ICMS/IPI');    // seção com dados
    expect($html)->toContain('Total do mês');        // rodapé a-recolher
});

it('gera PDF valido mesmo sem dados de apuracao (competencia vazia)', function () {
    $user = \App\Models\User::find($this->userId);
    // competência sem EFD → seções caem no "sem dados", PDF ainda válido
    $resp = $this->actingAs($user)->get("/app/resumo-fiscal/exportar-pdf?cliente_id={$this->clienteId}&competencia=2019-05");

    $resp->assertOk();
    expect(substr($resp->getContent(), 0, 4))->toBe('%PDF');
});
