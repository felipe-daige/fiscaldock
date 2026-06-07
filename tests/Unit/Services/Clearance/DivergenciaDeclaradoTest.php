<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\User;
use App\Models\XmlImportacao;
use App\Models\XmlNota;
use App\Services\Clearance\DivergenciaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

it('devolve contraparte_cnpj e data_emissao do declarado (EFD via participante)', function () {
    $u = User::factory()->create();
    $cli = Cliente::create(['user_id' => $u->id, 'is_empresa_propria' => true, 'tipo_pessoa' => 'PJ', 'documento' => '00000000000191', 'razao_social' => 'Propria']);
    $imp = EfdImportacao::create(['user_id' => $u->id, 'cliente_id' => $cli->id, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido']);
    $part = Participante::create(['user_id' => $u->id, 'cliente_id' => $cli->id, 'documento' => '13305697000150', 'razao_social' => 'Fornecedor']);
    $chave = str_repeat('7', 44);
    EfdNota::create([
        'user_id' => $u->id, 'cliente_id' => $cli->id, 'participante_id' => $part->id, 'importacao_id' => $imp->id,
        'chave_acesso' => $chave, 'modelo' => '55', 'numero' => 1, 'serie' => '0', 'data_emissao' => '2026-01-15',
        'tipo_operacao' => 'entrada', 'valor_total' => 500, 'valor_desconto' => 0, 'origem_arquivo' => 'fiscal', 'metadados' => [],
    ]);

    $map = (new DivergenciaService)->buscarDeclaradoPorChave($u->id, [$chave]);

    expect($map[$chave]['contraparte_cnpj'])->toBe('13305697000150');
    expect($map[$chave]['valor_total'])->toBe(500.0);
    expect($map[$chave]['data_emissao'])->toBe('2026-01-15');
});

it('XML: contraparte é o lado sem cliente_id', function () {
    $u = User::factory()->create();
    $cli = Cliente::create(['user_id' => $u->id, 'is_empresa_propria' => true, 'tipo_pessoa' => 'PJ', 'documento' => '00000000000191', 'razao_social' => 'Propria']);
    $imp = XmlImportacao::create(['user_id' => $u->id, 'cliente_id' => $cli->id, 'status' => 'concluido', 'tipo_documento' => 'NFE']);
    $chave = str_repeat('8', 44);
    XmlNota::create([
        'user_id' => $u->id, 'importacao_xml_id' => $imp->id, 'cliente_id' => $cli->id,
        'chave_acesso' => $chave, 'tipo_documento' => 'NFE', 'numero_documento' => 9, 'serie' => 1,
        'data_emissao' => '2026-02-10 09:00:00', 'valor_total' => 750, 'tipo_nota' => XmlNota::TIPO_ENTRADA,
        'emit_documento' => '22222222000181', 'emit_razao_social' => 'Fornecedor XML', 'emit_cliente_id' => null,
        'dest_documento' => '00000000000191', 'dest_razao_social' => 'Propria', 'dest_cliente_id' => $cli->id,
        'payload' => [],
    ]);

    $map = (new DivergenciaService)->buscarDeclaradoPorChave($u->id, [$chave]);
    expect($map[$chave]['contraparte_cnpj'])->toBe('22222222000181');
});
