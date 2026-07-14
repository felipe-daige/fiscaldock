<?php

use App\Models\Cliente;
use App\Models\User;
use App\Models\XmlImportacao;
use App\Models\XmlNota;
use App\Models\XmlNotaItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

/**
 * Itens negociados nas views de nota XML: card inline do drill-down (/app/notas e
 * notas-fiscais-card de cliente/participante/minha-empresa) e página cheia xml-nota.
 * O lado EFD (efd-inline/efd-nota) já exibia itens via itensDetalhe().
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $cliente = Cliente::create([
        'user_id' => $this->user->id,
        'is_empresa_propria' => true,
        'tipo_pessoa' => 'PJ',
        'documento' => '00000000000191',
        'razao_social' => 'Empresa Itens XML',
    ]);
    $importacao = XmlImportacao::create([
        'user_id' => $this->user->id,
        'cliente_id' => $cliente->id,
        'status' => 'concluido',
        'tipo_documento' => 'NFE',
    ]);
    $this->nota = XmlNota::create([
        'user_id' => $this->user->id,
        'importacao_xml_id' => $importacao->id,
        'cliente_id' => $cliente->id,
        'chave_acesso' => str_repeat('6', 44),
        'tipo_documento' => 'NFE',
        'numero_documento' => 66001,
        'serie' => 1,
        'data_emissao' => '2026-03-05 08:00:00',
        'valor_total' => 450.00,
        'tipo_nota' => XmlNota::TIPO_SAIDA,
        'emit_cliente_id' => $cliente->id,
        'emit_documento' => $cliente->documento,
        'emit_razao_social' => $cliente->razao_social,
        'dest_documento' => '97551165000193',
        'dest_razao_social' => 'Destino Itens XML',
    ]);
    XmlNotaItem::create([
        'xml_nota_id' => $this->nota->id,
        'user_id' => $this->user->id,
        'numero_item' => 1,
        'codigo_item' => 'SKU-450',
        'descricao' => 'Esmerilhadeira Angular 900W',
        'quantidade' => 3,
        'unidade_medida' => 'UN',
        'valor_unitario' => 150.00,
        'valor_total' => 450.00,
        'cfop' => '5102',
        'ncm' => '84672920',
        'valor_icms' => 54.00,
        'valor_pis' => 7.43,
        'valor_cofins' => 34.20,
    ]);
});

it('mostra os itens no card inline do drill-down da nota XML', function () {
    actingAs($this->user)
        ->get('/app/notas/xml/'.$this->nota->id, [
            'X-Requested-With' => 'XMLHttpRequest',
            'X-Nota-Detalhe' => 'inline',
        ])
        ->assertOk()
        ->assertSee('Itens da Nota')
        ->assertSee('Esmerilhadeira Angular 900W')
        ->assertSee('84672920')
        ->assertSee('SKU-450');
});

it('mostra os itens na pagina cheia da nota XML', function () {
    actingAs($this->user)
        ->get('/app/notas/xml/'.$this->nota->id)
        ->assertOk()
        ->assertSee('Itens da Nota')
        ->assertSee('Esmerilhadeira Angular 900W')
        ->assertSee('84672920')
        ->assertSee('5102');
});

it('nao mostra o card de itens quando a nota XML nao tem itens extraidos', function () {
    XmlNotaItem::where('xml_nota_id', $this->nota->id)->delete();

    actingAs($this->user)
        ->get('/app/notas/xml/'.$this->nota->id)
        ->assertOk()
        ->assertDontSee('Itens da Nota');
});
