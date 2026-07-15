<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\MonitoramentoPlano;
use App\Models\Participante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('prioriza os itens e mostra as últimas certidões do participante e do cliente', function () {
    $user = User::factory()->create();
    $cliente = Cliente::create([
        'user_id' => $user->id,
        'tipo_pessoa' => 'PJ',
        'documento' => '11222333000181',
        'razao_social' => 'CLIENTE DA NOTA LTDA',
        'situacao_cadastral' => 'ATIVA',
        'is_empresa_propria' => true,
        'ativo' => true,
    ]);
    $participante = Participante::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'documento' => '44555666000102',
        'razao_social' => 'PARTICIPANTE DA NOTA LTDA',
        'situacao_cadastral' => 'ATIVA',
        'uf' => 'SP',
    ]);
    $importacao = EfdImportacao::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'tipo_efd' => 'EFD ICMS/IPI',
        'filename' => 'detalhe-refactor.txt',
        'status' => 'concluido',
        'iniciado_em' => now(),
    ]);
    $nota = EfdNota::create([
        'user_id' => $user->id,
        'cliente_id' => $cliente->id,
        'participante_id' => $participante->id,
        'importacao_id' => $importacao->id,
        'numero' => '901',
        'serie' => '1',
        'data_emissao' => '2026-07-10',
        'valor_desconto' => 0,
        'cancelada' => false,
        'chave_acesso' => str_repeat('9', 44),
        'modelo' => '55',
        'tipo_operacao' => 'entrada',
        'origem_arquivo' => 'fiscal',
        'valor_total' => 250,
    ]);
    DB::table('efd_notas_itens')->insert([
        'efd_nota_id' => $nota->id,
        'user_id' => $user->id,
        'numero_item' => 1,
        'codigo_item' => 'ITEM-REF',
        'descricao' => 'ITEM BEM POSICIONADO',
        'quantidade' => 2,
        'valor_unitario' => 125,
        'valor_total' => 250,
        'cfop' => 1102,
        'valor_icms' => 45,
        'valor_pis' => 4,
        'valor_cofins' => 18,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $plano = MonitoramentoPlano::create([
        'codigo' => 'nota-refactor',
        'nome' => 'Consulta da nota',
        'descricao' => 'Plano usado no teste do detalhe EFD.',
        'consultas_incluidas' => ['cnd_federal', 'cnd_estadual'],
        'etapas' => [],
        'custo_creditos' => 0,
        'is_gratuito' => true,
        'is_active' => true,
        'ordem' => 99,
    ]);
    $loteParticipante = ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => $plano->id,
        'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'tab_id' => 'nota-participante',
        'processado_em' => now()->subHour(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $loteParticipante->id,
        'participante_id' => $participante->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'consultado_em' => now()->subHour(),
        'resultado_dados' => [
            'cnd_federal' => [
                'status' => 'Negativa',
                'conseguiu_emitir' => true,
                'certidao_codigo' => 'FED-PART-001',
            ],
            'consultas_realizadas' => ['cnd_federal'],
        ],
    ]);

    $loteCliente = ConsultaLote::create([
        'user_id' => $user->id,
        'plano_id' => $plano->id,
        'status' => ConsultaLote::STATUS_CONCLUIDO,
        'total_participantes' => 1,
        'creditos_cobrados' => 0,
        'tab_id' => 'nota-cliente',
        'processado_em' => now(),
    ]);
    ConsultaResultado::create([
        'consulta_lote_id' => $loteCliente->id,
        'cliente_id' => $cliente->id,
        'status' => ConsultaResultado::STATUS_SUCESSO,
        'consultado_em' => now(),
        'resultado_dados' => [
            'cnd_estadual' => [
                'status' => 'Negativa',
                'conseguiu_emitir' => true,
                'certidao_codigo' => 'EST-CLIENT-001',
                'uf' => 'SP',
            ],
            'consultas_realizadas' => ['cnd_estadual'],
        ],
    ]);

    $response = actingAs($user)->get('/app/notas/efd/'.$nota->id)->assertOk();
    $html = $response->getContent();

    $response
        ->assertSeeInOrder(['Itens da nota', 'Partes da operação'])
        ->assertSee('ITEM BEM POSICIONADO')
        ->assertSee('FED-PART-001')
        ->assertSee('EST-CLIENT-001')
        ->assertSee('data-layout-principal', false)
        ->assertSee('data-itens-lista', false)
        ->assertSee('data-parte-operacao-card', false)
        ->assertSee('data-dados-tabela', false)
        ->assertSee('data-regularidade-partes', false)
        ->assertSee('data-certidoes-contexto="participante"', false)
        ->assertSee('data-certidoes-contexto="cliente"', false)
        ->assertSeeInOrder([
            'data-partes-operacao',
            'data-regularidade-partes',
            'data-certidoes-contexto="participante"',
            'data-certidoes-contexto="cliente"',
        ], false);

    expect(substr_count($html, 'Resultado fiscal mais recente disponível para este cadastro.'))->toBe(2)
        ->and(substr_count($html, 'data-parte-operacao-card'))->toBe(2)
        ->and(substr_count($html, 'data-dados-tabela'))->toBe(2)
        ->and(substr_count($html, 'data-dado-celula'))->toBe(18)
        ->and(substr_count($html, 'Abrir cadastro completo'))->toBe(2);

    preg_match('/<div class="([^"]*)" data-partes-operacao>/', $html, $partesGrid);

    expect($partesGrid[1] ?? '')
        ->toContain('items-stretch')
        ->not->toContain('items-start');
});
