<?php

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->participanteId = DB::table('participantes')->insertGetId([
        'user_id' => $this->user->id, 'razao_social' => 'ACME PARTICIPANTE LTDA',
        'documento' => '19131243000197', 'uf' => 'SP', 'situacao_cadastral' => 'ATIVA',
        'regime_tributario' => 'Simples Nacional', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $clienteId = DB::table('clientes')->insertGetId([
        'user_id' => $this->user->id, 'razao_social' => 'EMPRESA PROPRIA', 'documento' => '00000000000100',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $this->user->id, 'cliente_id' => $clienteId,
        'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'f.txt', 'status' => 'concluido', 'iniciado_em' => now(),
    ]);
    EfdNota::create([
        'user_id' => $this->user->id, 'participante_id' => $this->participanteId, 'importacao_id' => $imp->id,
        'cliente_id' => $clienteId,
        'numero' => 1, 'serie' => '1', 'data_emissao' => '2024-03-10', 'valor_total' => 2000,
        'valor_desconto' => 0, 'cancelada' => false, 'chave_acesso' => str_pad('A', 44, '0'),
        'modelo' => '55', 'tipo_operacao' => 'entrada', 'origem_arquivo' => 'fiscal',
    ]);
});

it('baixa o PDF de listagem dos participantes selecionados', function () {
    $resp = $this->actingAs($this->user)->post('/app/participantes/exportar-pdf', ['ids' => [$this->participanteId]]);

    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('application/pdf');
    expect(substr($resp->getContent(), 0, 4))->toBe('%PDF');
});

it('exige ids (validação falha sem seleção)', function () {
    $this->actingAs($this->user)->post('/app/participantes/exportar-pdf', [])
        ->assertSessionHasErrors('ids');
    $this->actingAs($this->user)->post('/app/participantes/exportar-pdf', ['ids' => []])
        ->assertSessionHasErrors('ids');
});

it('ignora ids de outro usuário e redireciona quando nada é válido', function () {
    $outro = User::factory()->create();
    $alheio = DB::table('participantes')->insertGetId([
        'user_id' => $outro->id, 'razao_social' => 'ALHEIO', 'documento' => '99888777000166',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($this->user)->post('/app/participantes/exportar-pdf', ['ids' => [$alheio]])
        ->assertRedirect(route('app.participantes'));
});

it('exige autenticação', function () {
    $this->post('/app/participantes/exportar-pdf', ['ids' => [$this->participanteId]])->assertRedirect('/login');
});

it('renderiza a view de listagem com papel e volume', function () {
    $dados = app(\App\Services\Participantes\ParticipanteListagemBuilder::class)
        ->montar($this->user->id, [$this->participanteId]);

    $html = view('reports.participantes-listagem', $dados)->render();

    expect($html)->toContain('ACME PARTICIPANTE LTDA');
    expect($html)->toContain('Fornecedor');
    expect($html)->toContain('Total movimentado');
});

// ── Planilhas (XLSX/CSV) — mesma seleção, mesmas colunas do PDF ──

it('baixa o XLSX dos participantes selecionados', function () {
    $resp = $this->actingAs($this->user)->post('/app/participantes/exportar-xlsx', ['ids' => [$this->participanteId]]);

    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('spreadsheetml');
    // BinaryFileResponse não tem conteúdo em memória — lê o arquivo temporário.
    $bytes = file_get_contents($resp->baseResponse->getFile()->getPathname());
    expect(substr($bytes, 0, 2))->toBe('PK');
});

it('baixa o CSV dos participantes selecionados (BOM + ";")', function () {
    $resp = $this->actingAs($this->user)->post('/app/participantes/exportar-csv', ['ids' => [$this->participanteId]]);

    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('text/csv');

    $csv = $resp->streamedContent();
    expect($csv)->toStartWith("\xEF\xBB\xBF");
    expect($csv)->toContain('ACME PARTICIPANTE LTDA');
    expect($csv)->toContain('Fornecedor');
    expect($csv)->toContain('2.000,00');
});

it('planilhas exigem ids', function () {
    $this->actingAs($this->user)->post('/app/participantes/exportar-xlsx', [])->assertSessionHasErrors('ids');
    $this->actingAs($this->user)->post('/app/participantes/exportar-csv', [])->assertSessionHasErrors('ids');
});

// Sem actingAs: `actingAs` autentica pelo resto do teste, então a checagem de auth vive isolada.
it('planilhas exigem autenticação', function () {
    $this->post('/app/participantes/exportar-xlsx', ['ids' => [$this->participanteId]])->assertRedirect('/login');
    $this->post('/app/participantes/exportar-csv', ['ids' => [$this->participanteId]])->assertRedirect('/login');
});
