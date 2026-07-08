<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->clienteId = DB::table('clientes')->insertGetId([
        'user_id' => $this->user->id, 'razao_social' => 'ACME LISTAGEM LTDA', 'nome' => 'Acme',
        'documento' => '11222333000181', 'tipo_pessoa' => 'PJ', 'uf' => 'SP',
        'situacao_cadastral' => 'ATIVA', 'regime_tributario' => 'Simples Nacional',
        'is_empresa_propria' => false, 'ativo' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $this->user->id, 'cliente_id' => $this->clienteId,
        'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'f.txt', 'status' => 'concluido', 'iniciado_em' => now(),
    ]);
    EfdNota::create([
        'user_id' => $this->user->id, 'cliente_id' => $this->clienteId, 'importacao_id' => $imp->id,
        'numero' => 1, 'serie' => '1', 'data_emissao' => '2024-03-10', 'valor_total' => 2000,
        'valor_desconto' => 0, 'cancelada' => false, 'chave_acesso' => str_pad('A', 44, '0'),
        'modelo' => '55', 'tipo_operacao' => 'saida', 'origem_arquivo' => 'fiscal',
    ]);
});

it('baixa o PDF de listagem dos clientes selecionados', function () {
    $resp = $this->actingAs($this->user)->post('/app/clientes/exportar-pdf', ['ids' => [$this->clienteId]]);

    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('application/pdf');
    expect(substr($resp->getContent(), 0, 4))->toBe('%PDF');
});

it('exige ids (validação falha sem seleção)', function () {
    // POST web (form): validação falha redireciona de volta com erro de sessão.
    $this->actingAs($this->user)->post('/app/clientes/exportar-pdf', [])
        ->assertSessionHasErrors('ids');
    $this->actingAs($this->user)->post('/app/clientes/exportar-pdf', ['ids' => []])
        ->assertSessionHasErrors('ids');
});

it('ignora ids de outro usuário e redireciona quando nada é válido', function () {
    $outro = User::factory()->create();
    $alheio = DB::table('clientes')->insertGetId([
        'user_id' => $outro->id, 'razao_social' => 'ALHEIO', 'documento' => '99888777000166',
        'tipo_pessoa' => 'PJ', 'is_empresa_propria' => false, 'ativo' => true,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->actingAs($this->user)->post('/app/clientes/exportar-pdf', ['ids' => [$alheio]])
        ->assertRedirect(route('app.clientes'));
});

it('exige autenticação', function () {
    $this->post('/app/clientes/exportar-pdf', ['ids' => [$this->clienteId]])->assertRedirect('/login');
});

// ── Planilhas (XLSX/CSV) — mesma seleção, mesmas colunas do PDF ──

it('baixa o XLSX da carteira selecionada', function () {
    $resp = $this->actingAs($this->user)->post('/app/clientes/exportar-xlsx', ['ids' => [$this->clienteId]]);

    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('spreadsheetml');
    // BinaryFileResponse não tem conteúdo em memória — lê o arquivo temporário.
    $bytes = file_get_contents($resp->baseResponse->getFile()->getPathname());
    expect(substr($bytes, 0, 2))->toBe('PK'); // container zip do xlsx
});

it('baixa o CSV da carteira selecionada (BOM + ";")', function () {
    $resp = $this->actingAs($this->user)->post('/app/clientes/exportar-csv', ['ids' => [$this->clienteId]]);

    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('text/csv');

    $csv = $resp->streamedContent();
    expect($csv)->toStartWith("\xEF\xBB\xBF");
    expect($csv)->toContain('ACME LISTAGEM LTDA');
    expect($csv)->toContain('2.000,00');   // movimentado pt-BR
    expect($csv)->toContain('Total');
});

it('planilhas exigem ids', function () {
    $this->actingAs($this->user)->post('/app/clientes/exportar-xlsx', [])->assertSessionHasErrors('ids');
    $this->actingAs($this->user)->post('/app/clientes/exportar-csv', [])->assertSessionHasErrors('ids');
});

// Sem actingAs: `actingAs` autentica pelo resto do teste, então a checagem de auth vive isolada.
it('planilhas exigem autenticação', function () {
    $this->post('/app/clientes/exportar-xlsx', ['ids' => [$this->clienteId]])->assertRedirect('/login');
    $this->post('/app/clientes/exportar-csv', ['ids' => [$this->clienteId]])->assertRedirect('/login');
});
