<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\EfdNotaItem;
use App\Models\NfeConsulta;
use App\Models\Participante;
use App\Models\User;
use App\Models\XmlImportacao;
use App\Models\XmlNota;
use App\Models\XmlNotaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function clearanceDetalhesCliente(User $user, array $atributos = []): Cliente
{
    return Cliente::create(array_merge([
        'user_id' => $user->id,
        'is_empresa_propria' => true,
        'tipo_pessoa' => 'PJ',
        'documento' => '00000000000191',
        'razao_social' => 'Cliente Perfil Fiscal',
        'situacao_cadastral' => 'ATIVA',
        'regime_tributario' => 'Lucro Presumido',
        'inscricao_estadual' => '110042490114',
        'municipio' => 'Campo Grande',
        'uf' => 'MS',
    ], $atributos));
}

it('enriquece o expansivel com resultado do clearance e perfis das partes', function () {
    $user = User::factory()->create();
    $cliente = clearanceDetalhesCliente($user);
    $importacao = EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'status' => 'concluido',
    ]);
    $participante = Participante::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'documento' => '13305697000150',
        'razao_social' => 'Fornecedor Perfil Fiscal',
        'situacao_cadastral' => 'ATIVA',
        'regime_tributario' => 'Simples Nacional',
        'inscricao_estadual' => '123456789',
        'municipio' => 'São Paulo',
        'uf' => 'SP',
    ]);
    $nota = EfdNota::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'participante_id' => $participante->id,
        'importacao_id' => $importacao->id,
        'chave_acesso' => '35240413305697000150550000000404041953940992',
        'modelo' => '55',
        'numero' => 40404,
        'serie' => '0',
        'data_emissao' => '2026-01-15',
        'tipo_operacao' => 'entrada',
        'valor_total' => 161.00,
        'valor_desconto' => 0,
        'origem_arquivo' => 'fiscal',
        'metadados' => [],
    ]);

    NfeConsulta::create([
        'user_id' => $user->id,
        'chave_acesso' => $nota->chave_acesso,
        'tipo_documento' => 'NFE',
        'modelo' => '55',
        'numero' => '40404',
        'serie' => '0',
        'status' => 'AUTORIZADA',
        'valor_total' => 160.00,
        'data_emissao' => '2026-01-15 09:00:00',
        'natureza_operacao' => 'COMPRA PARA COMERCIALIZAÇÃO',
        'emit_nome' => 'Fornecedor Perfil Fiscal',
        'emit_cnpj' => '13305697000150',
        'emit_uf' => 'SP',
        'emit_ie' => '123456789',
        'dest_nome' => 'Cliente Perfil Fiscal',
        'dest_cnpj' => '00000000000191',
        'dest_uf' => 'MS',
        'consultado_em' => '2026-07-13 12:00:00',
        'eventos' => [[
            'evento' => 'Carta de Correção Eletrônica',
            'protocolo' => '999',
        ]],
        'url_html' => 'https://receita.example/danfe',
        'payload' => ['nfe_clearance' => ['situacao_ambiente' => 'produção']],
    ]);

    actingAs($user)
        ->get('/app/clearance/notas')
        ->assertOk()
        ->assertSee('data-nota-key="efd-'.$nota->id.'"', false)
        ->assertSee('href="/app/notas/efd/'.$nota->id.'"', false)
        ->assertSee('<span>Detalhes</span>', false)
        ->assertSee('Ver detalhes da nota')
        ->assertSee('Resultado do clearance')
        ->assertSee('Conferência Declarado × SEFAZ')
        ->assertSee('Perfil do cliente')
        ->assertSee('Cliente Perfil Fiscal')
        ->assertSee('Lucro Presumido')
        ->assertSee('Perfil do participante')
        ->assertSee('Fornecedor Perfil Fiscal')
        ->assertSee('Simples Nacional')
        ->assertSee('R$ 160,00')
        ->assertSee('R$ -1,00')
        ->assertSee('CC-e')
        ->assertSee('Ver na Receita');
});

it('resolve cliente e participante de uma nota XML no expansivel', function () {
    $user = User::factory()->create();
    $cliente = clearanceDetalhesCliente($user, ['razao_social' => 'Emitente da Carteira']);
    $participante = Participante::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'documento' => '97551165000193',
        'razao_social' => 'Destinatário Contraparte',
        'situacao_cadastral' => 'ATIVA',
        'regime_tributario' => 'Lucro Real',
        'municipio' => 'Dourados',
        'uf' => 'MS',
    ]);
    $importacao = XmlImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'status' => 'concluido',
        'tipo_documento' => 'NFE',
    ]);
    $nota = XmlNota::create([
        'user_id' => $user->id,
        'importacao_xml_id' => $importacao->id,
        'cliente_id' => $cliente->id,
        'chave_acesso' => str_repeat('7', 44),
        'tipo_documento' => 'NFE',
        'numero_documento' => 77001,
        'serie' => 1,
        'data_emissao' => '2026-01-10 10:00:00',
        'valor_total' => 999.99,
        'tipo_nota' => XmlNota::TIPO_SAIDA,
        'emit_cliente_id' => $cliente->id,
        'emit_documento' => $cliente->documento,
        'emit_razao_social' => $cliente->razao_social,
        'dest_participante_id' => $participante->id,
        'dest_documento' => $participante->documento,
        'dest_razao_social' => $participante->razao_social,
    ]);

    actingAs($user)
        ->get('/app/clearance/notas')
        ->assertOk()
        ->assertSee('data-nota-key="xml-'.$nota->id.'"', false)
        ->assertSee('href="/app/notas/xml/'.$nota->id.'"', false)
        ->assertSee('<span>Detalhes</span>', false)
        ->assertSee('Ver detalhes da nota')
        ->assertSee('Emitente da saída')
        ->assertSee('Destinatário / cliente')
        ->assertSee('Emitente da Carteira')
        ->assertSee('Destinatário Contraparte')
        ->assertSee('Lucro Real')
        ->assertSee('Documento ainda não consultado na SEFAZ')
        ->assertSee('data-expand-for="xml-'.$nota->id.'"', false);
});

it('mostra os itens negociados de uma nota XML no expansivel', function () {
    $user = User::factory()->create();
    $cliente = clearanceDetalhesCliente($user);
    $importacao = XmlImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'status' => 'concluido',
        'tipo_documento' => 'NFE',
    ]);
    $nota = XmlNota::create([
        'user_id' => $user->id,
        'importacao_xml_id' => $importacao->id,
        'cliente_id' => $cliente->id,
        'chave_acesso' => str_repeat('8', 44),
        'tipo_documento' => 'NFE',
        'numero_documento' => 88001,
        'serie' => 1,
        'data_emissao' => '2026-02-10 10:00:00',
        'valor_total' => 1500.00,
        'tipo_nota' => XmlNota::TIPO_SAIDA,
        'emit_cliente_id' => $cliente->id,
        'emit_documento' => $cliente->documento,
        'emit_razao_social' => $cliente->razao_social,
        'dest_documento' => '97551165000193',
        'dest_razao_social' => 'Comprador do Item',
    ]);
    XmlNotaItem::create([
        'xml_nota_id' => $nota->id,
        'user_id' => $user->id,
        'numero_item' => 1,
        'codigo_item' => 'SKU-778',
        'descricao' => 'Compressor de Ar 50L Profissional',
        'quantidade' => 2,
        'unidade_medida' => 'UN',
        'valor_unitario' => 750.00,
        'valor_total' => 1500.00,
        'cfop' => '5102',
        'ncm' => '84144080',
    ]);

    actingAs($user)
        ->get('/app/clearance/notas')
        ->assertOk()
        ->assertSee('Itens da nota')
        ->assertSee('Compressor de Ar 50L Profissional')
        ->assertSee('84144080')
        ->assertSee('5102')
        ->assertSee('1 item(ns)');
});

it('trunca o expansivel em 15 itens e oferece o restante via nota completa', function () {
    $user = User::factory()->create();
    $cliente = clearanceDetalhesCliente($user);
    $importacao = XmlImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'status' => 'concluido',
        'tipo_documento' => 'NFE',
    ]);
    $nota = XmlNota::create([
        'user_id' => $user->id,
        'importacao_xml_id' => $importacao->id,
        'cliente_id' => $cliente->id,
        'chave_acesso' => str_repeat('5', 44),
        'tipo_documento' => 'NFE',
        'numero_documento' => 55001,
        'serie' => 1,
        'data_emissao' => '2026-02-20 10:00:00',
        'valor_total' => 160.00,
        'tipo_nota' => XmlNota::TIPO_SAIDA,
        'emit_cliente_id' => $cliente->id,
        'emit_documento' => $cliente->documento,
        'emit_razao_social' => $cliente->razao_social,
        'dest_documento' => '97551165000193',
        'dest_razao_social' => 'Comprador em Volume',
    ]);
    foreach (range(1, 16) as $n) {
        XmlNotaItem::create([
            'xml_nota_id' => $nota->id,
            'user_id' => $user->id,
            'numero_item' => $n,
            'codigo_item' => 'SKU-'.$n,
            'descricao' => 'Produto Truncado Numero '.$n,
            'quantidade' => 1,
            'unidade_medida' => 'UN',
            'valor_unitario' => 10.00,
            'valor_total' => 10.00,
            'cfop' => '5102',
        ]);
    }

    actingAs($user)
        ->get('/app/clearance/notas')
        ->assertOk()
        ->assertSee('16 item(ns)')
        ->assertSee('Produto Truncado Numero 15')
        ->assertDontSee('Produto Truncado Numero 16')
        ->assertSee('+ 1 item(ns) — ver nota completa');
});

it('mostra os itens da gemea de contribuicoes quando a nota EFD fiscal so tem consolidado', function () {
    $user = User::factory()->create();
    $cliente = clearanceDetalhesCliente($user);
    $importacaoFiscal = EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'status' => 'concluido',
    ]);
    $importacaoContribuicoes = EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'tipo_efd' => 'EFD PIS/COFINS',
        'status' => 'concluido',
    ]);
    $chave = '35240413305697000150550000000505051953940993';
    $base = [
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'chave_acesso' => $chave,
        'modelo' => '55',
        'numero' => 50505,
        'serie' => '0',
        'data_emissao' => '2026-02-15',
        'tipo_operacao' => 'saida',
        'valor_total' => 320.00,
        'valor_desconto' => 0,
        'metadados' => [],
    ];
    EfdNota::create($base + ['importacao_id' => $importacaoFiscal->id, 'origem_arquivo' => 'fiscal']);
    $gemea = EfdNota::create($base + ['importacao_id' => $importacaoContribuicoes->id, 'origem_arquivo' => 'contribuicoes']);
    EfdNotaItem::create([
        'efd_nota_id' => $gemea->id,
        'user_id' => $user->id,
        'numero_item' => 1,
        'codigo_item' => 'PROD-42',
        'descricao' => 'Parafusadeira Eletrica 12V',
        'quantidade' => 4,
        'unidade_medida' => 'UN',
        'valor_unitario' => 80.00,
        'valor_total' => 320.00,
        'cfop' => '5102',
    ]);

    actingAs($user)
        ->get('/app/clearance/notas')
        ->assertOk()
        ->assertSee('Itens da nota')
        ->assertSee('Parafusadeira Eletrica 12V')
        ->assertSee('Itens via EFD Contribuições');
});

it('mostra os itens negociados na view de detalhe da nota', function () {
    $user = User::factory()->create();
    $cliente = clearanceDetalhesCliente($user);
    $importacao = XmlImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'status' => 'concluido',
        'tipo_documento' => 'NFE',
    ]);
    $nota = XmlNota::create([
        'user_id' => $user->id,
        'importacao_xml_id' => $importacao->id,
        'cliente_id' => $cliente->id,
        'chave_acesso' => str_repeat('9', 44),
        'tipo_documento' => 'NFE',
        'numero_documento' => 99001,
        'serie' => 1,
        'data_emissao' => '2026-03-01 09:00:00',
        'valor_total' => 259.98,
        'tipo_nota' => XmlNota::TIPO_SAIDA,
        'emit_cliente_id' => $cliente->id,
        'emit_documento' => $cliente->documento,
        'emit_razao_social' => $cliente->razao_social,
        'dest_documento' => '97551165000193',
        'dest_razao_social' => 'Comprador do Item',
    ]);
    XmlNotaItem::create([
        'xml_nota_id' => $nota->id,
        'user_id' => $user->id,
        'numero_item' => 1,
        'codigo_item' => 'SKU-901',
        'descricao' => 'Furadeira de Impacto 650W',
        'quantidade' => 2,
        'unidade_medida' => 'PC',
        'valor_unitario' => 129.99,
        'valor_total' => 259.98,
        'cfop' => '5405',
        'ncm' => '84672100',
    ]);

    actingAs($user)
        ->get('/app/clearance/nota/'.$nota->id)
        ->assertOk()
        ->assertSee('Itens da Nota')
        ->assertSee('Furadeira de Impacto 650W')
        ->assertSee('SKU-901')
        ->assertSee('84672100')
        ->assertSee('5405');
});
