<?php

use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\User;
use App\Services\Clearance\DivergenciaService;
use App\Services\Clearance\RelatorioExecutivoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

/**
 * Cenário base: 1 documento declarado na EFD (R$ 1.000, emitido 2026-01-15) que a SEFAZ
 * não encontrou → divergência crítica (nota fria). Espelha ClearanceResultadoVeredictoTest.
 *
 * @return array{lote: ConsultaLote, resultados: Collection, divergencia: array}
 */
function montarCenarioFria(): array
{
    $user = User::factory()->create(['credits' => 100]);
    $cliente = Cliente::create([
        'user_id' => $user->id, 'is_empresa_propria' => true,
        'tipo_pessoa' => 'PJ', 'documento' => '00000000000191', 'razao_social' => 'Escritorio Contabil ME',
    ]);
    $imp = EfdImportacao::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'tipo_efd' => 'EFD ICMS/IPI', 'status' => 'concluido',
    ]);
    $part = Participante::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'documento' => '13305697000150', 'razao_social' => 'Fornecedor Fria LTDA',
    ]);

    $chave = '35240413305697000150550000000404041953940992';

    EfdNota::create([
        'user_id' => $user->id, 'cliente_id' => $cliente->id, 'participante_id' => $part->id,
        'importacao_id' => $imp->id, 'chave_acesso' => $chave, 'modelo' => '55', 'numero' => 40404,
        'serie' => '0', 'data_emissao' => '2026-01-15', 'tipo_operacao' => 'entrada',
        'valor_total' => 1000.00, 'valor_desconto' => 0, 'origem_arquivo' => 'fiscal', 'metadados' => [],
    ]);

    $lote = ConsultaLote::create([
        'user_id' => $user->id, 'plano_id' => null, 'status' => ConsultaLote::STATUS_FINALIZADO,
        'total_participantes' => 1, 'creditos_cobrados' => 3, 'tab_id' => 'tab-pdf', 'processado_em' => now(),
    ]);

    $resultados = collect([
        (object) [
            'chave_acesso' => $chave, 'tipo_documento' => 'NFE', 'modelo' => '55',
            'numero' => 40404, 'serie' => '0', 'status' => 'NAO_ENCONTRADA', 'status_label' => 'NAO_ENCONTRADA',
            'valor_total' => null, 'data_emissao' => '2026-01-15',
            'emit_nome' => 'Fornecedor Fria LTDA', 'emit_cnpj' => '13305697000150',
            'dest_nome' => 'Escritorio Contabil ME', 'dest_cnpj' => '00000000000191',
            'cliente_nome' => 'Escritorio Contabil ME',
        ],
    ]);

    $divergencia = (new DivergenciaService)->analisar($resultados, $user->id, 3);

    return ['lote' => $lote, 'resultados' => $resultados, 'divergencia' => $divergencia];
}

it('monta a capa com escritório (empresa própria), lote e período', function () {
    ['lote' => $lote, 'resultados' => $resultados, 'divergencia' => $divergencia] = montarCenarioFria();

    $relatorio = (new RelatorioExecutivoService)->montar($lote, $resultados, $divergencia);

    expect($relatorio['capa']['escritorio']['razao_social'])->toBe('Escritorio Contabil ME');
    expect($relatorio['capa']['lote_id'])->toBe($lote->id);
    expect($relatorio['capa']['periodo']['inicio'])->toBe('2026-01-15');
    expect($relatorio['capa']['periodo']['fim'])->toBe('2026-01-15');
});

it('monetiza a exposição: base + multa 75% = total', function () {
    ['lote' => $lote, 'resultados' => $resultados, 'divergencia' => $divergencia] = montarCenarioFria();

    $relatorio = (new RelatorioExecutivoService)->montar($lote, $resultados, $divergencia);

    expect($relatorio['exposicao']['base'])->toBe(1000.00);
    expect($relatorio['exposicao']['multa'])->toBe(750.00);
    expect($relatorio['exposicao']['total'])->toBe(1750.00);
});

it('lista os documentos divergentes com decadência por documento', function () {
    ['lote' => $lote, 'resultados' => $resultados, 'divergencia' => $divergencia] = montarCenarioFria();

    $relatorio = (new RelatorioExecutivoService)->montar($lote, $resultados, $divergencia);

    expect($relatorio['documentos'])->toHaveCount(1);
    $doc = $relatorio['documentos']->first();
    expect($doc->decadencia_label)->toBe('15/01/2031');
    expect($doc->exposicao_base)->toBe(1000.00);
});

it('monta a concentração de risco top-5 por emitente', function () {
    ['lote' => $lote, 'resultados' => $resultados, 'divergencia' => $divergencia] = montarCenarioFria();

    $relatorio = (new RelatorioExecutivoService)->montar($lote, $resultados, $divergencia);

    expect($relatorio['concentracao'])->toHaveCount(1);
    $top = $relatorio['concentracao']->first();
    expect($top['emit_cnpj'])->toBe('13305697000150');
    expect($top['qtd'])->toBe(1);
    expect($top['valor_exposto'])->toBe(1000.00);
});

it('gera hash de integridade estável para o mesmo lote (idempotência de dados)', function () {
    ['lote' => $lote, 'resultados' => $resultados, 'divergencia' => $divergencia] = montarCenarioFria();

    $service = new RelatorioExecutivoService;
    $a = $service->montar($lote, $resultados, $divergencia);
    $b = $service->montar($lote, $resultados, $divergencia);

    expect($a['hash'])->toBe($b['hash']);
    expect($a['hash'])->toHaveLength(64);
});

it('resume os totais de documentos e divergências', function () {
    ['lote' => $lote, 'resultados' => $resultados, 'divergencia' => $divergencia] = montarCenarioFria();

    $relatorio = (new RelatorioExecutivoService)->montar($lote, $resultados, $divergencia);

    expect($relatorio['resumo']['total_documentos'])->toBe(1);
    expect($relatorio['resumo']['total_criticas'])->toBe(1);
    expect($relatorio['resumo']['veredito']['severidade'])->toBe('critica');
});
