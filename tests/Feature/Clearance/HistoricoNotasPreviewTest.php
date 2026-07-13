<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\CteConsulta;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\NfeConsulta;
use App\Models\Participante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('exibe preview expansivel dos resultados de cada lote no historico de notas', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create([
        'user_id' => $user->id,
        'is_empresa_propria' => true,
        'tipo_pessoa' => 'PJ',
        'documento' => '00000000000191',
        'razao_social' => 'Cliente do Histórico',
        'situacao_cadastral' => 'ATIVA',
        'regime_tributario' => 'Lucro Presumido',
        'inscricao_estadual' => '110042490114',
        'municipio' => 'Campo Grande',
        'uf' => 'MS',
    ]);
    $participante = Participante::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'documento' => '13305697000150',
        'razao_social' => 'Fornecedor com Perfil Fiscal',
        'situacao_cadastral' => 'ATIVA',
        'regime_tributario' => 'Simples Nacional',
        'inscricao_estadual' => '123456789',
        'municipio' => 'São Paulo',
        'uf' => 'SP',
    ]);
    $importacao = EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'status' => 'concluido',
    ]);
    $lote = ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => null,
        'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 2,
        'creditos_cobrados' => 2,
        'resultado_resumo' => ['tier' => 'basico', 'fluxo_origem' => 'lote'],
        'processado_em' => now(),
    ]);

    foreach ([
        ['chave' => str_repeat('1', 44), 'numero' => 1001, 'valor' => 120.50, 'modelo' => '55'],
        ['chave' => str_repeat('2', 44), 'numero' => 1002, 'valor' => 95.00, 'modelo' => '57'],
    ] as $nota) {
        EfdNota::create([
            'user_id' => $user->id,
            'cliente_id' => $cliente->id,
            'participante_id' => $participante->id,
            'importacao_id' => $importacao->id,
            'chave_acesso' => $nota['chave'],
            'modelo' => $nota['modelo'],
            'numero' => $nota['numero'],
            'serie' => '1',
            'data_emissao' => '2026-07-13',
            'tipo_operacao' => 'entrada',
            'valor_total' => $nota['valor'],
            'valor_desconto' => 0,
            'origem_arquivo' => 'fiscal',
            'metadados' => [],
        ]);
    }

    NfeConsulta::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'consulta_lote_id' => $lote->id,
        'chave_acesso' => str_repeat('1', 44),
        'tipo_documento' => 'NFE',
        'modelo' => '55',
        'numero' => '1001',
        'serie' => '1',
        'status' => 'AUTORIZADA',
        'valor_total' => 120.50,
        'data_emissao' => '2026-07-13 09:00:00',
        'emit_nome' => 'Fornecedor Autorizado',
        'emit_cnpj' => '13305697000150',
        'emit_uf' => 'SP',
        'emit_ie' => '123456789',
        'dest_nome' => 'Cliente do Histórico',
        'dest_cnpj' => $cliente->documento,
        'dest_uf' => 'MS',
        'consultado_em' => now()->subMinute(),
    ]);
    CteConsulta::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'consulta_lote_id' => $lote->id,
        'chave_acesso' => str_repeat('2', 44),
        'tipo_documento' => 'CTE',
        'modelo' => '57',
        'numero' => '1002',
        'serie' => '1',
        'status' => 'CANCELADA',
        'valor_prestacao' => 90.00,
        'data_emissao' => '2026-07-13 10:00:00',
        'emit_nome' => 'Fornecedor Cancelado',
        'emit_cnpj' => '13305697000150',
        'emit_uf' => 'SP',
        'emit_ie' => '123456789',
        'dest_nome' => 'Cliente do Histórico',
        'dest_cnpj' => $cliente->documento,
        'dest_uf' => 'MS',
        'consultado_em' => now(),
    ]);

    actingAs($user)
        ->get(route('app.clearance.notas.historico'))
        ->assertOk()
        ->assertSee('Ver detalhes')
        ->assertSee('data-history-details="lote-'.$lote->id.'"', false)
        ->assertSee('Preview do resultado')
        ->assertSee('2 de 2')
        ->assertSee('Veredito da validação')
        ->assertSee('Divergências críticas encontradas')
        ->assertSee('1 crítica')
        ->assertSee('Nº 1001')
        ->assertSee('Nº 1002')
        ->assertSee('Fornecedor Autorizado')
        ->assertSee('Fornecedor Cancelado')
        ->assertSee('R$ 120,50')
        ->assertSee('Conferência Declarado × SEFAZ')
        ->assertSee('R$ 95,00')
        ->assertSee('R$ 90,00')
        ->assertSee('R$ -5,00')
        ->assertSee('Documento cancelado na SEFAZ, mas escriturado nos livros.')
        ->assertSee('CNPJ contraparte')
        ->assertSee('Razão social')
        ->assertSee('Inscrição Estadual')
        ->assertSee('<span class="text-gray-400">SEFAZ:</span> 123456789', false)
        ->assertSee('Data de emissão')
        ->assertSee('Número / Série')
        ->assertSee('Modelo')
        ->assertSee('Partes retornadas pela SEFAZ')
        ->assertSee('Perfis vinculados à escrituração')
        ->assertSee('Perfil do cliente')
        ->assertSee('Perfil do participante')
        ->assertSee('Cliente do Histórico')
        ->assertSee('Fornecedor com Perfil Fiscal')
        ->assertSee('00.000.000/0001-91')
        ->assertSee('13.305.697/0001-50')
        ->assertSee('Lucro Presumido')
        ->assertSee('Simples Nacional')
        ->assertSee('Campo Grande / MS')
        ->assertSee('São Paulo / SP')
        ->assertSee('Abrir resultado completo');
});
