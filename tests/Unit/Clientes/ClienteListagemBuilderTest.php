<?php

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\User;
use App\Services\Clientes\ClienteListagemBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function novoClienteListagem(int $userId, array $attrs = []): int
{
    return DB::table('clientes')->insertGetId(array_merge([
        'user_id' => $userId, 'razao_social' => 'CLIENTE X', 'nome' => 'Cliente X',
        'documento' => '11222333000181', 'tipo_pessoa' => 'PJ', 'uf' => 'SP',
        'situacao_cadastral' => 'ATIVA', 'regime_tributario' => 'Simples Nacional',
        'is_empresa_propria' => false, 'ativo' => true,
        'created_at' => now(), 'updated_at' => now(),
    ], $attrs));
}

function notaFiscalCliente(int $userId, int $clienteId, int $impId, string $chave, float $valor): void
{
    EfdNota::create([
        'user_id' => $userId, 'cliente_id' => $clienteId, 'importacao_id' => $impId,
        'numero' => random_int(1, 99999), 'serie' => '1', 'data_emissao' => '2024-03-10',
        'valor_total' => $valor, 'valor_desconto' => 0, 'cancelada' => false,
        'chave_acesso' => $chave, 'modelo' => '55', 'tipo_operacao' => 'saida', 'origem_arquivo' => 'fiscal',
    ]);
}

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->builder = app(ClienteListagemBuilder::class);
    $this->imp = EfdImportacao::create([
        'user_id' => $this->user->id, 'cliente_id' => null,
        'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'f.txt', 'status' => 'concluido', 'iniciado_em' => now(),
    ]);
});

it('retorna null para seleção vazia', function () {
    expect($this->builder->montar($this->user->id, []))->toBeNull();
});

it('monta linhas com cadastral e volume movimentado somado', function () {
    $c = novoClienteListagem($this->user->id, ['razao_social' => 'ACME LTDA', 'documento' => '11222333000181']);
    notaFiscalCliente($this->user->id, $c, $this->imp->id, str_pad('A', 44, '0'), 1000);
    notaFiscalCliente($this->user->id, $c, $this->imp->id, str_pad('B', 44, '0'), 500);

    $out = $this->builder->montar($this->user->id, [$c]);

    expect($out['total'])->toBe(1);
    expect($out['clientes'][0]['nome'])->toBe('ACME LTDA');
    expect($out['clientes'][0]['movimentado'])->toBe(1500.0);
    expect($out['clientes'][0]['regularidade'])->toBe('Não consultado');
    expect($out['clientes'][0]['ultima_consulta'])->toBeNull();
    expect($out['total_movimentado'])->toBe(1500.0);
});

it('deduplica XML cuja chave já existe no EFD (não dobra o volume)', function () {
    $c = novoClienteListagem($this->user->id);
    $chave = str_pad('C', 44, '0');
    notaFiscalCliente($this->user->id, $c, $this->imp->id, $chave, 1000);

    // XML com a MESMA chave → não conta; XML com chave distinta → conta.
    $xml = fn (string $chaveAcesso, string $num, float $valor, string $data) => DB::table('xml_notas')->insert([
        'user_id' => $this->user->id, 'cliente_id' => $c, 'chave_acesso' => $chaveAcesso,
        'tipo_documento' => 'nfe', 'origem' => 'importacao', 'numero_documento' => $num,
        'emit_documento' => '11222333000181', 'dest_documento' => '99888777000166',
        'valor_total' => $valor, 'tipo_nota' => 1, 'data_emissao' => $data,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $xml($chave, '1', 1000, '2024-03-11');            // mesma chave do EFD → dedup
    $xml(str_pad('D', 44, '0'), '2', 250, '2024-03-12'); // chave distinta → soma

    $out = $this->builder->montar($this->user->id, [$c]);

    // 1000 (EFD) + 250 (XML distinto). O XML duplicado NÃO soma.
    expect($out['clientes'][0]['movimentado'])->toBe(1250.0);
});

it('não vaza cliente de outro usuário', function () {
    $outro = User::factory()->create();
    $alheio = novoClienteListagem($outro->id, ['documento' => '99888777000166']);

    expect($this->builder->montar($this->user->id, [$alheio]))->toBeNull();
});
