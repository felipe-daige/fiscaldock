<?php

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\User;
use App\Models\XmlImportacao;
use App\Support\Dinheiro;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('historico de importacoes diferencia os previews de EFD e XML', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create([
        'user_id' => $user->id,
        'documento' => '11222333000181',
        'razao_social' => 'Indústria Horizonte Ltda',
        'tipo_pessoa' => 'PJ',
        'ativo' => true,
    ]);

    EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'cnpj' => $cliente->documento,
        'periodo_inicio' => '2026-05-01',
        'periodo_fim' => '2026-05-31',
        'filename' => 'sped-fiscal-maio.txt',
        'total_participantes' => 4,
        'total_notas' => 12,
        'notas_extraidas' => 12,
        'status' => 'concluido',
    ]);

    XmlImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'tipo_documento' => 'NFE',
        'filename' => 'notas-fornecedores.zip',
        'total_xmls' => 8,
        'xmls_processados' => 8,
        'xmls_novos' => 6,
        'xmls_duplicados_processados' => 1,
        'xmls_com_erro' => 1,
        'valor_total' => 1234.56,
        'status' => 'concluido',
    ]);

    actingAs($user)
        ->get('/app/importacao/historico')
        ->assertOk()
        ->assertSee('tabela-cards historico-tabela', false)
        ->assertSee('data-history-result-url="/app/importacao/efd/', false)
        ->assertSee('data-history-result-url="/app/importacao/xml/', false)
        ->assertSee('Importação realizada')
        ->assertSee('Indústria Horizonte Ltda')
        ->assertSee('sped-fiscal-maio.txt')
        ->assertSee('ICMS/IPI')
        ->assertSee('Mai/2026')
        ->assertSee('12 notas')
        ->assertSee('4 participantes')
        ->assertSee('notas-fornecedores.zip')
        ->assertSee('NF-e')
        ->assertSee('8 XMLs')
        ->assertSee('6 novos · 1 duplicado · 1 com erro')
        ->assertSee(Dinheiro::brl(1234.56).' em documentos');
});
