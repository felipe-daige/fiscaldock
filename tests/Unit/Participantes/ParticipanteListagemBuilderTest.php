<?php

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\User;
use App\Services\Participantes\ParticipanteListagemBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function novoParticipanteListagem(int $userId, array $attrs = []): int
{
    return DB::table('participantes')->insertGetId(array_merge([
        'user_id' => $userId, 'razao_social' => 'FORNECEDOR X', 'documento' => '19131243000197',
        'uf' => 'SP', 'situacao_cadastral' => 'ATIVA', 'regime_tributario' => 'Simples Nacional',
        'created_at' => now(), 'updated_at' => now(),
    ], $attrs));
}

function notaDoParticipante(int $userId, int $participanteId, int $impId, int $clienteId, string $chave, float $valor, string $tipoOperacao = 'entrada', string $origem = 'fiscal'): void
{
    EfdNota::create([
        'user_id' => $userId, 'participante_id' => $participanteId, 'importacao_id' => $impId,
        'cliente_id' => $clienteId,
        'numero' => random_int(1, 99999), 'serie' => '1', 'data_emissao' => '2024-03-10',
        'valor_total' => $valor, 'valor_desconto' => 0, 'cancelada' => false,
        'chave_acesso' => $chave, 'modelo' => '55', 'tipo_operacao' => $tipoOperacao, 'origem_arquivo' => $origem,
    ]);
}

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->builder = app(ParticipanteListagemBuilder::class);
    $this->clienteId = DB::table('clientes')->insertGetId([
        'user_id' => $this->user->id, 'razao_social' => 'EMPRESA PROPRIA', 'documento' => '00000000000100',
        'is_empresa_propria' => true, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $this->imp = EfdImportacao::create([
        'user_id' => $this->user->id, 'cliente_id' => $this->clienteId,
        'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'f.txt', 'status' => 'concluido', 'iniciado_em' => now(),
    ]);
});

it('retorna null para seleção vazia', function () {
    expect($this->builder->montar($this->user->id, []))->toBeNull();
});

it('monta linhas com cadastral, papel e volume movimentado', function () {
    $p = novoParticipanteListagem($this->user->id, ['razao_social' => 'ACME FORNECEDOR']);
    notaDoParticipante($this->user->id, $p, $this->imp->id, $this->clienteId, str_pad('A', 44, '0'), 1000, 'entrada');
    notaDoParticipante($this->user->id, $p, $this->imp->id, $this->clienteId, str_pad('B', 44, '0'), 500, 'entrada');

    $out = $this->builder->montar($this->user->id, [$p]);

    expect($out['total'])->toBe(1);
    expect($out['participantes'][0]['nome'])->toBe('ACME FORNECEDOR');
    expect($out['participantes'][0]['papel'])->toBe('Fornecedor');   // só entradas
    expect($out['participantes'][0]['movimentado'])->toBe(1500.0);
    expect($out['participantes'][0]['notas'])->toBe(2);
    expect($out['participantes'][0]['regularidade'])->toBe('Não consultado');
    expect($out['total_movimentado'])->toBe(1500.0);
});

it('classifica papel "Ambos" quando há entrada e saída', function () {
    $p = novoParticipanteListagem($this->user->id);
    notaDoParticipante($this->user->id, $p, $this->imp->id, $this->clienteId, str_pad('C', 44, '0'), 100, 'entrada');
    notaDoParticipante($this->user->id, $p, $this->imp->id, $this->clienteId, str_pad('D', 44, '0'), 200, 'saida');

    $out = $this->builder->montar($this->user->id, [$p]);

    expect($out['participantes'][0]['papel'])->toBe('Ambos');
    expect($out['participantes'][0]['movimentado'])->toBe(300.0);
});

it('usa a dedup P1 escopada: gêmea contribuicoes de mesma chave não dobra o volume', function () {
    $p = novoParticipanteListagem($this->user->id);
    $chave = str_pad('E', 44, '0');
    notaDoParticipante($this->user->id, $p, $this->imp->id, $this->clienteId, $chave, 1000, 'entrada', 'fiscal');
    // Mesma NF-e escriturada também no PIS/COFINS → NÃO soma (dedupParticipanteSql).
    notaDoParticipante($this->user->id, $p, $this->imp->id, $this->clienteId, $chave, 1000, 'entrada', 'contribuicoes');

    $out = $this->builder->montar($this->user->id, [$p]);

    expect($out['participantes'][0]['movimentado'])->toBe(1000.0);
    expect($out['participantes'][0]['notas'])->toBe(1);
});

it('participante sem movimentação aparece com papel "Sem movimentação" e zero', function () {
    $p = novoParticipanteListagem($this->user->id, ['razao_social' => 'SEM NOTAS']);

    $out = $this->builder->montar($this->user->id, [$p]);

    expect($out['participantes'][0]['papel'])->toBe('Sem movimentação');
    expect($out['participantes'][0]['movimentado'])->toBe(0.0);
    expect($out['participantes'][0]['notas'])->toBe(0);
});

it('não vaza participante de outro usuário', function () {
    $outro = User::factory()->create();
    $alheio = novoParticipanteListagem($outro->id, ['documento' => '99888777000166']);

    expect($this->builder->montar($this->user->id, [$alheio]))->toBeNull();
});
