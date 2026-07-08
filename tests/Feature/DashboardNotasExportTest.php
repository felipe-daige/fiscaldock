<?php

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\User;
use App\Services\Notas\Export\DashboardNotasReportBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

/**
 * Exportações do Dashboard de Notas Fiscais (PDF/XLSX/CSV-ZIP).
 *
 * Contrato: os 3 formatos leem o MESMO payload (`DashboardNotasReportBuilder`), que por sua
 * vez lê o `DashboardNotasService` — a mesma fonte das abas. Se o relatório divergir da tela,
 * é aqui que quebra. Ver `docs/dashboard-notas/exportacoes.md`.
 */
beforeEach(function () {
    $this->user = User::factory()->create();

    // Export é recurso pago (gate de entitlements): trial ativo libera.
    $this->user->forceFill([
        'trial_used' => true, 'trial_expires_at' => now()->addDays(30), 'trial_credits_remaining' => 50,
    ])->save();

    $this->cliente = DB::table('clientes')->insertGetId([
        'user_id' => $this->user->id, 'razao_social' => 'EMPRESA TESTE',
        'documento' => '00000000000100', 'is_empresa_propria' => true,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $this->fornecedor = Participante::create([
        'user_id' => $this->user->id, 'documento' => '11222333000181',
        'razao_social' => 'FORNECEDOR IRREGULAR LTDA', 'uf' => 'SP',
        'situacao_cadastral' => 'BAIXADA', 'ultima_consulta_em' => now()->subDay(),
    ]);
    $this->clienteFinal = Participante::create([
        'user_id' => $this->user->id, 'documento' => '99888777000166',
        'razao_social' => 'CLIENTE REGULAR SA', 'uf' => 'MG',
        'situacao_cadastral' => 'ATIVA', 'ultima_consulta_em' => now()->subDay(),
    ]);

    $imp = EfdImportacao::create([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente,
        'tipo_efd' => 'EFD ICMS/IPI', 'filename' => 'i.txt', 'status' => 'concluido', 'iniciado_em' => now(),
    ]);

    $mk = fn (array $a) => EfdNota::create(array_merge([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente, 'importacao_id' => $imp->id,
        'numero' => random_int(1, 99999), 'serie' => '1', 'valor_desconto' => 0, 'cancelada' => false,
        'origem_arquivo' => 'fiscal', 'modelo' => '55',
    ], $a));

    $saida = $mk(['chave_acesso' => str_pad('A', 44, '0', STR_PAD_LEFT), 'tipo_operacao' => 'saida', 'valor_total' => 1000, 'data_emissao' => '2024-01-15', 'participante_id' => $this->clienteFinal->id]);
    $entrada = $mk(['chave_acesso' => str_pad('C', 44, '0', STR_PAD_LEFT), 'tipo_operacao' => 'entrada', 'valor_total' => 700, 'data_emissao' => '2024-02-10', 'participante_id' => $this->fornecedor->id]);
    // CT-e: garante mais de um modelo no mix.
    $mk(['chave_acesso' => str_pad('D', 44, '0', STR_PAD_LEFT), 'modelo' => '57', 'tipo_operacao' => 'entrada', 'valor_total' => 300, 'data_emissao' => '2024-02-20', 'participante_id' => $this->fornecedor->id]);

    $cons = fn (EfdNota $n, float $icms, string $cst) => DB::table('efd_notas_consolidados')->insert([
        'efd_nota_id' => $n->id, 'user_id' => $this->user->id, 'cfop' => $n->tipo_operacao === 'saida' ? 5102 : 1102,
        'cst_icms' => $cst, 'aliquota_icms' => 18, 'valor_operacao' => $n->valor_total, 'valor_bc_icms' => 0,
        'valor_icms' => $icms, 'valor_bc_icms_st' => 0, 'valor_icms_st' => 0, 'valor_reducao_bc' => 0,
        'valor_ipi' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $cons($saida, 180, '00');   // débito ICMS
    $cons($entrada, 126, '60'); // crédito ICMS, CST de ST
});

function exportUrl(string $rota): string
{
    return "/app/notas/dashboard/{$rota}?periodo_inicio=2024-01&periodo_fim=2024-12&tipo_efd=todos";
}

/**
 * Bytes do download. Os 3 exports devolvem tipos de resposta diferentes (DomPDF → Response,
 * XLSX/ZIP → BinaryFileResponse com deleteFileAfterSend, CSV → StreamedResponse).
 */
function corpoDownload($resp): string
{
    $base = $resp->baseResponse;

    if ($base instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
        return (string) file_get_contents($base->getFile()->getPathname());
    }

    if ($base instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
        return $resp->streamedContent();
    }

    return (string) $resp->getContent();
}

it('baixa o PDF do raio-x do acervo', function () {
    $resp = actingAs($this->user)->get(exportUrl('exportar-pdf'));

    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('application/pdf');
    expect(substr(corpoDownload($resp), 0, 4))->toBe('%PDF');
});

it('baixa o XLSX com uma aba por seção', function () {
    $resp = actingAs($this->user)->get(exportUrl('exportar-xlsx'));

    $resp->assertOk();
    expect($resp->headers->get('content-type'))->toContain('spreadsheetml');
    expect(substr(corpoDownload($resp), 0, 2))->toBe('PK'); // xlsx é um zip
});

it('baixa o ZIP com um CSV por seção', function () {
    $resp = actingAs($this->user)->get(exportUrl('exportar-csv-zip'));
    $resp->assertOk();

    $tmp = tempnam(sys_get_temp_dir(), 'zt');
    file_put_contents($tmp, corpoDownload($resp));

    $zip = new ZipArchive;
    expect($zip->open($tmp))->toBeTrue();

    $nomes = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $nomes[] = $zip->getNameIndex($i);
    }
    $zip->close();
    unlink($tmp);

    expect($nomes)->toContain('01-resumo.csv')
        ->and($nomes)->toContain('02-mix-modelo.csv')
        ->and($nomes)->toContain('04-concentracao.csv')
        ->and($nomes)->toContain('07-cst-icms.csv')
        ->and($nomes)->toContain('10-compliance-exposicao.csv');
});

it('anexa o cookie bi_download quando recebe download_token (overlay do frontend)', function () {
    $resp = actingAs($this->user)->get(exportUrl('exportar-pdf').'&download_token=abc123');

    $resp->assertOk();
    // Symfony\Cookie::$name é protegido — pluck('name') não enxerga; usar o accessor.
    $nomes = array_map(fn ($c) => $c->getName(), $resp->headers->getCookies());
    expect($nomes)->toContain('bi_download');
});

it('monta o relatório só com as seções exclusivas deste export', function () {
    $relatorio = app(DashboardNotasReportBuilder::class)->montar($this->user->id, [
        'periodo_inicio' => '2024-01', 'periodo_fim' => '2024-12', 'tipo_efd' => 'todos',
    ]);

    expect($relatorio['ordem_secoes'])->toBe([
        'mix-modelo', 'evolucao-mensal', 'concentracao', 'contrapartes-matriz',
        'cfop', 'cst-icms', 'tributos-mensal', 'alertas', 'compliance-exposicao',
    ]);

    // Seções do BI executivo / Fechamento não podem vazar pra cá (escopo do delta).
    expect($relatorio['secoes'])->not->toHaveKeys(['uf', 'catalogo', 'top-notas', 'dossie-participantes', 'a-recolher', 'retencoes']);
});

it('mix por modelo separa NF-e de CT-e e bate com o KPI de notas', function () {
    $relatorio = app(DashboardNotasReportBuilder::class)->montar($this->user->id, [
        'periodo_inicio' => '2024-01', 'periodo_fim' => '2024-12', 'tipo_efd' => 'todos',
    ]);

    $linhas = $relatorio['secoes']['mix-modelo']['linhas'];
    expect($linhas)->toHaveCount(2);

    // [label, notas, valor, %, notas_ent, valor_ent, notas_sai, valor_sai]
    $porLabel = collect($linhas)->keyBy(0);
    expect($porLabel->keys())->toContain('NF-e');

    expect(array_sum(array_column($linhas, 1)))->toBe($relatorio['kpis']['total_notas']);
    expect(array_sum(array_column($linhas, 2)))->toBe(2000.0); // 1000 saída + 700 + 300 entradas
});

it('concentração mostra 100% quando há uma única contraparte de cada lado', function () {
    $relatorio = app(DashboardNotasReportBuilder::class)->montar($this->user->id, [
        'periodo_inicio' => '2024-01', 'periodo_fim' => '2024-12', 'tipo_efd' => 'todos',
    ]);

    // [faixa, % entradas, % saídas]
    expect($relatorio['secoes']['concentracao']['linhas'][0])->toBe(['Top 5 contrapartes', 100.0, 100.0]);
});

it('matriz de contrapartes traz papel e janela da relação', function () {
    $relatorio = app(DashboardNotasReportBuilder::class)->montar($this->user->id, [
        'periodo_inicio' => '2024-01', 'periodo_fim' => '2024-12', 'tipo_efd' => 'todos',
    ]);

    $linhas = collect($relatorio['secoes']['contrapartes-matriz']['linhas']);
    $fornecedor = $linhas->firstWhere(2, 'FORNECEDOR IRREGULAR LTDA');

    expect($fornecedor[0])->toBe('Fornecedor');          // papel
    expect($fornecedor[8])->toBe('10/02/2024');          // 1ª nota
    expect($fornecedor[9])->toBe('20/02/2024');          // última nota
    expect($fornecedor[7])->toBe(1000.0);                // 700 + 300

    expect($linhas->firstWhere(2, 'CLIENTE REGULAR SA')[0])->toBe('Cliente');
});

it('perfil de CST separa tributado de substituição tributária', function () {
    $relatorio = app(DashboardNotasReportBuilder::class)->montar($this->user->id, [
        'periodo_inicio' => '2024-01', 'periodo_fim' => '2024-12', 'tipo_efd' => 'todos',
    ]);

    $csts = collect($relatorio['secoes']['cst-icms']['linhas'])->pluck(0);
    expect($csts)->toContain('00')->and($csts)->toContain('60');
});

it('saldo mensal por tributo separa débito de crédito por mês', function () {
    $relatorio = app(DashboardNotasReportBuilder::class)->montar($this->user->id, [
        'periodo_inicio' => '2024-01', 'periodo_fim' => '2024-12', 'tipo_efd' => 'todos',
    ]);

    // [mês, icms_deb, icms_cred, saldo_icms, ...]
    $linhas = $relatorio['secoes']['tributos-mensal']['linhas'];
    expect($linhas)->toHaveCount(2);
    expect($linhas[0][1])->toBe(180.0);   // jan: débito
    expect($linhas[0][3])->toBe(180.0);   // jan: saldo
    expect($linhas[1][2])->toBe(126.0);   // fev: crédito
    expect($linhas[1][3])->toBe(-126.0);  // fev: saldo negativo (crédito acumulado)
});

it('contraparte CPF aparece como "Pessoa física", nunca "Não consultado"', function () {
    // CPF sem situação cadastral (11 dígitos, situacao null): não é pendência de consulta.
    $cpf = Participante::create([
        'user_id' => $this->user->id, 'documento' => '12345678909',
        'razao_social' => 'JOÃO DA SILVA', 'uf' => 'RJ', 'situacao_cadastral' => null,
    ]);
    EfdNota::create([
        'user_id' => $this->user->id, 'cliente_id' => $this->cliente,
        'importacao_id' => EfdImportacao::where('user_id', $this->user->id)->first()->id,
        'numero' => 555, 'serie' => '1', 'valor_desconto' => 0, 'cancelada' => false,
        'origem_arquivo' => 'fiscal', 'modelo' => '55', 'chave_acesso' => str_pad('F', 44, '0', STR_PAD_LEFT),
        'tipo_operacao' => 'entrada', 'valor_total' => 250, 'data_emissao' => '2024-03-01', 'participante_id' => $cpf->id,
    ]);

    $relatorio = app(DashboardNotasReportBuilder::class)->montar($this->user->id, [
        'periodo_inicio' => '2024-01', 'periodo_fim' => '2024-12', 'tipo_efd' => 'todos',
    ]);

    $linha = collect($relatorio['secoes']['compliance-exposicao']['linhas'])->firstWhere(1, 'JOÃO DA SILVA');
    expect($linha[3])->toBe('CPF (pessoa física)');           // situação
    expect((float) $linha[7])->toBe(0.0);                     // não entra em exposição
});

it('exposição soma só o volume de contraparte irregular', function () {
    $relatorio = app(DashboardNotasReportBuilder::class)->montar($this->user->id, [
        'periodo_inicio' => '2024-01', 'periodo_fim' => '2024-12', 'tipo_efd' => 'todos',
    ]);

    expect($relatorio['compliance_kpis']['irregulares'])->toBe(1);
    expect((float) $relatorio['compliance_kpis']['exposicao'])->toBe(1000.0); // só o fornecedor BAIXADA
});

it('renderiza a view do PDF com as seções e os gráficos', function () {
    $relatorio = app(DashboardNotasReportBuilder::class)->montar($this->user->id, [
        'periodo_inicio' => '2024-01', 'periodo_fim' => '2024-12', 'tipo_efd' => 'todos',
    ]);

    $html = view('reports.notas-dashboard', ['relatorio' => $relatorio])->render();

    expect($html)->toContain('Mix por modelo de documento')
        ->and($html)->toContain('Concentração de contrapartes')
        ->and($html)->toContain('Perfil de tributação')
        ->and($html)->toContain('Saldo mensal por tributo')
        ->and($html)->toContain('Exposição a contrapartes irregulares')
        ->and($html)->toContain('FORNECEDOR IRREGULAR LTDA');
});

it('não vaza acervo de outro usuário', function () {
    $outro = User::factory()->create();
    $outro->forceFill(['trial_used' => true, 'trial_expires_at' => now()->addDays(30), 'trial_credits_remaining' => 50])->save();

    $relatorio = app(DashboardNotasReportBuilder::class)->montar($outro->id, [
        'periodo_inicio' => '2024-01', 'periodo_fim' => '2024-12', 'tipo_efd' => 'todos',
    ]);

    expect($relatorio['kpis']['total_notas'])->toBe(0);
    expect($relatorio['secoes']['contrapartes-matriz']['linhas'])->toBe([]);
});
