<?php

namespace App\Services\Notas\Export;

use App\Models\Cliente;
use App\Models\EfdImportacao;
use App\Models\Participante;
use App\Services\Notas\DashboardNotasService;
use App\Support\Reports\ReportTheme;
use App\Support\Reports\XlsxReport;
use Illuminate\Support\Carbon;

/**
 * Monta o "Raio-X do acervo de notas" — payload único consumido pelo PDF, XLSX e CSV/ZIP
 * de `/app/notas/dashboard`.
 *
 * Regra de escopo (pedido do usuário, 2026-07-08): este relatório só carrega o que os
 * outros PDFs/planilhas NÃO têm. Fora daqui, de propósito: UF, catálogo, top-notas,
 * dossiês e score da carteira (BI executivo); apuração de uma competência, retenções e
 * a-recolher (Resumo Fiscal). Ver `docs/dashboard-notas/exportacoes.md`.
 *
 * Contrato de seção:
 *   ['chave','titulo','colunas','linhas','formatos'?,'grafico'?,'nota'?,'cores'?]
 * `linhas` carrega NÚMERO REAL nas colunas numéricas (o XLSX precisa; o CSV converte
 * pra vírgula; o PDF formata na renderização) — nunca "R$ 1.234,56" como string.
 */
class DashboardNotasReportBuilder
{
    /** Linhas de tabela longa que cabem no PDF sem virar listagem (XLSX/CSV levam tudo). */
    public const LIMITE_PDF = 50;

    private const HEX_ENTRADA = '#b45309';

    private const HEX_SAIDA = '#2563eb';

    private const HEX_NEUTRO = '#7c3aed';

    /** Paleta ordinal do mix por modelo / CST (barra empilhada). */
    private const PALETA = ['#2563eb', '#0d9488', '#b45309', '#7c3aed', '#be185d', '#0891b2', '#65a30d', '#9ca3af'];

    private const SEVERIDADE_HEX = [
        'alta' => ReportTheme::IRREGULAR,
        'media' => ReportTheme::ALERTA,
        'baixa' => ReportTheme::NEUTRO,
    ];

    public function __construct(private DashboardNotasService $service) {}

    public function montar(int $userId, array $filtros): array
    {
        $visaoGeral = $this->service->visaoGeral($userId, $filtros);
        $tributario = $this->service->tributario($userId, $filtros);
        $participantes = $this->service->participantesCompleto($userId, $filtros);
        $cfop = $this->service->cfop($userId, $filtros);
        $alertas = $this->service->alertas($userId, $filtros);
        $compliance = $this->service->compliance($userId, $filtros);

        $secoes = [
            $this->secaoMixModelo($visaoGeral),
            $this->secaoEvolucao($visaoGeral),
            $this->secaoConcentracao($participantes),
            $this->secaoContrapartes($participantes),
            $this->secaoCfop($cfop),
            $this->secaoCstIcms($tributario),
            $this->secaoTributosMensal($tributario),
            $this->secaoAlertas($alertas),
            $this->secaoCompliance($compliance),
        ];

        return [
            'titulo' => 'Raio-X do Acervo de Notas Fiscais',
            'gerado_em' => now(),
            'periodo' => [
                'inicio' => (string) ($filtros['periodo_inicio'] ?? ''),
                'fim' => (string) ($filtros['periodo_fim'] ?? ''),
            ],
            'filtros' => $this->descreverFiltros($userId, $filtros),
            'kpis' => $visaoGeral['kpis'],
            'saldos' => $tributario['saldos'],
            'alerta_pis_cofins' => (bool) $tributario['alerta_pis_cofins'],
            'resumo_alertas' => $alertas['resumo'],
            'compliance_kpis' => $compliance['kpis'],
            'secoes' => collect($secoes)->keyBy('chave')->all(),
            'ordem_secoes' => array_column($secoes, 'chave'),
        ];
    }

    // ── Seções ────────────────────────────────────────────────────────────────

    /**
     * Mix por modelo de documento (55 / 57 / NFS-e / …) quebrado em entrada × saída.
     * Nenhum outro relatório enxerga o acervo por modelo.
     */
    private function secaoMixModelo(array $visaoGeral): array
    {
        $modelos = collect($visaoGeral['por_modelo']);

        $linhas = $modelos->map(fn ($m) => [
            $m['label'],
            (int) $m['quantidade'],
            (float) $m['valor_total'],
            (float) $m['percentual'],
            (int) $m['entradas']['quantidade'],
            (float) $m['entradas']['valor'],
            (int) $m['saidas']['quantidade'],
            (float) $m['saidas']['valor'],
        ])->all();

        $grafico = $modelos->values()->map(fn ($m, $i) => [
            'label' => $m['label'],
            'pct' => (float) $m['percentual'],
            'valor' => $this->brl($m['valor_total']),
            'hex' => self::PALETA[$i % count(self::PALETA)],
        ])->all();

        return [
            'chave' => 'mix-modelo',
            'titulo' => 'Mix por modelo de documento',
            'nota' => 'Participação de cada modelo no valor movimentado, separando entradas de saídas. Base comercial (exclui CFOPs fora-faturamento).',
            'colunas' => ['Modelo', 'Notas', 'Valor total', '% do total', 'Notas (entrada)', 'Valor (entrada)', 'Notas (saída)', 'Valor (saída)'],
            'formatos' => [1 => XlsxReport::FMT_INT, 2 => XlsxReport::FMT_BRL, 3 => XlsxReport::FMT_PCT, 4 => XlsxReport::FMT_INT, 5 => XlsxReport::FMT_BRL, 6 => XlsxReport::FMT_INT, 7 => XlsxReport::FMT_BRL],
            'linhas' => $linhas,
            'grafico' => ['tipo' => 'stacked', 'itens' => $grafico],
        ];
    }

    /** Entradas × saídas mês a mês. O BI executivo só plota faturamento (saída). */
    private function secaoEvolucao(array $visaoGeral): array
    {
        $evolucao = collect($visaoGeral['evolucao']);

        $linhas = $evolucao->map(fn ($e) => [
            $this->mesLabel($e->mes),
            (float) $e->entradas,
            (float) $e->saidas,
            (float) $e->saidas - (float) $e->entradas,
        ])->all();

        $colunas = $evolucao->map(fn ($e) => [
            'label' => $this->mesCurto($e->mes),
            'series' => [
                ['valor' => (float) $e->entradas, 'hex' => self::HEX_ENTRADA],
                ['valor' => (float) $e->saidas, 'hex' => self::HEX_SAIDA],
            ],
        ])->all();

        return [
            'chave' => 'evolucao-mensal',
            'titulo' => 'Evolução — entradas × saídas',
            'nota' => 'Saldo = saídas − entradas no mês (base comercial). Negativo = mês comprador.',
            'colunas' => ['Mês', 'Entradas', 'Saídas', 'Saldo'],
            'formatos' => [1 => XlsxReport::FMT_BRL, 2 => XlsxReport::FMT_BRL, 3 => XlsxReport::FMT_BRL],
            'linhas' => $linhas,
            'grafico' => [
                'tipo' => 'colunas',
                'colunas' => $colunas,
                'legenda' => [['label' => 'Entradas', 'hex' => self::HEX_ENTRADA], ['label' => 'Saídas', 'hex' => self::HEX_SAIDA]],
            ],
        ];
    }

    /**
     * Dependência de poucas contrapartes — leitura de risco comercial que não existe
     * em nenhum outro relatório.
     */
    private function secaoConcentracao(array $participantes): array
    {
        $c = $participantes['resumo']['concentracao'];
        $r = $participantes['resumo'];

        $linhas = [
            ['Top 5 contrapartes', (float) $c['top5_entradas_pct'], (float) $c['top5_saidas_pct']],
            ['Top 10 contrapartes', (float) $c['top10_entradas_pct'], (float) $c['top10_saidas_pct']],
        ];

        return [
            'chave' => 'concentracao',
            'titulo' => 'Concentração de contrapartes',
            'nota' => sprintf(
                '%d contrapartes no recorte — %d fornecedores, %d clientes (quem compra e vende conta nos dois). Quanto maior o %%, maior a dependência de poucos parceiros.',
                $r['total_participantes'],
                $r['total_fornecedores'],
                $r['total_clientes'],
            ),
            'colunas' => ['Faixa', '% das entradas', '% das saídas'],
            'formatos' => [1 => XlsxReport::FMT_PCT, 2 => XlsxReport::FMT_PCT],
            'linhas' => $linhas,
            'grafico' => ['tipo' => 'barras', 'itens' => [
                ['label' => 'Top 5 — entradas', 'pct' => (float) $c['top5_entradas_pct'], 'valor' => $c['top5_entradas_pct'].'%', 'hex' => self::HEX_ENTRADA],
                ['label' => 'Top 10 — entradas', 'pct' => (float) $c['top10_entradas_pct'], 'valor' => $c['top10_entradas_pct'].'%', 'hex' => self::HEX_ENTRADA],
                ['label' => 'Top 5 — saídas', 'pct' => (float) $c['top5_saidas_pct'], 'valor' => $c['top5_saidas_pct'].'%', 'hex' => self::HEX_SAIDA],
                ['label' => 'Top 10 — saídas', 'pct' => (float) $c['top10_saidas_pct'], 'valor' => $c['top10_saidas_pct'].'%', 'hex' => self::HEX_SAIDA],
            ]],
        ];
    }

    /**
     * Matriz de contrapartes: papel (fornecedor/cliente/ambos) e janela da relação.
     * O BI lista volume + score; o papel e a primeira/última nota só existem aqui.
     */
    private function secaoContrapartes(array $participantes): array
    {
        $linhas = collect($participantes['participantes'])->map(fn ($p) => [
            ucfirst($p['papel']),
            $p['cnpj'],
            $p['razao_social'],
            $p['uf'],
            (int) $p['total_notas'],
            (float) $p['valor_entradas'],
            (float) $p['valor_saidas'],
            (float) $p['valor_total'],
            $this->data($p['primeira_nota']),
            $this->data($p['ultima_nota']),
        ])->all();

        return [
            'chave' => 'contrapartes-matriz',
            'titulo' => 'Matriz de contrapartes — papel e janela da relação',
            'nota' => '"Ambos" = a contraparte aparece como fornecedor e como cliente no recorte. A janela mostra desde quando e até quando houve movimento.',
            'colunas' => ['Papel', 'CNPJ/CPF', 'Razão social', 'UF', 'Notas', 'Entradas', 'Saídas', 'Total', '1ª nota', 'Última nota'],
            'formatos' => [4 => XlsxReport::FMT_INT, 5 => XlsxReport::FMT_BRL, 6 => XlsxReport::FMT_BRL, 7 => XlsxReport::FMT_BRL],
            'linhas' => $linhas,
            'total' => ['Total', '', '', '', array_sum(array_column($linhas, 4)), array_sum(array_column($linhas, 5)), array_sum(array_column($linhas, 6)), array_sum(array_column($linhas, 7)), '', ''],
        ];
    }

    /** CFOP pelo ângulo do dashboard: tipo/natureza + nº de linhas C190 (o BI só mostra valor). */
    private function secaoCfop(array $cfop): array
    {
        $cfops = collect($cfop['cfops']);

        $linhas = $cfops->map(fn ($c) => [
            (string) $c['cfop'],
            $c['descricao'],
            ucfirst((string) $c['tipo']),
            (string) $c['natureza'],
            (int) $c['qtd_itens'],
            (float) $c['valor_total'],
            (float) $c['percentual'],
        ])->all();

        $max = (float) $cfops->max('valor_total') ?: 1.0;
        $grafico = $cfops->take(12)->map(fn ($c) => [
            'label' => $c['cfop'].' — '.mb_strimwidth((string) $c['descricao'], 0, 34, '…'),
            'pct' => (int) round((float) $c['valor_total'] / $max * 100),
            'valor' => $this->brl($c['valor_total']),
            'hex' => $c['tipo'] === 'entrada' ? self::HEX_ENTRADA : self::HEX_SAIDA,
        ])->all();

        return [
            'chave' => 'cfop',
            'titulo' => 'CFOP — natureza da operação',
            'nota' => 'Fonte: consolidado C190/D190 (autoritativo p/ CFOP × valor). "Linhas" = registros de consolidado, não itens da nota.',
            'colunas' => ['CFOP', 'Descrição', 'Tipo', 'Natureza', 'Linhas C190', 'Valor', '% do valor'],
            'formatos' => [4 => XlsxReport::FMT_INT, 5 => XlsxReport::FMT_BRL, 6 => XlsxReport::FMT_PCT],
            'linhas' => $linhas,
            'grafico' => ['tipo' => 'barras', 'itens' => $grafico],
        ];
    }

    /** Perfil de tributação do ICMS por CST. Não existe em nenhum outro relatório. */
    private function secaoCstIcms(array $tributario): array
    {
        $csts = collect($tributario['csts']);
        $total = (float) $csts->sum('valor_total') ?: 1.0;

        $linhas = $csts->map(fn ($c) => [
            (string) ($c['cst'] ?? '—'),
            $c['descricao'],
            (int) $c['qtd_itens'],
            (float) $c['valor_total'],
            round((float) $c['valor_total'] / $total * 100, 1),
        ])->all();

        $grafico = $csts->values()->map(fn ($c, $i) => [
            'label' => ($c['cst'] ?? '—').' — '.mb_strimwidth((string) $c['descricao'], 0, 28, '…'),
            'pct' => round((float) $c['valor_total'] / $total * 100, 1),
            'valor' => $this->brl($c['valor_total']),
            'hex' => self::PALETA[$i % count(self::PALETA)],
        ])->all();

        return [
            'chave' => 'cst-icms',
            'titulo' => 'Perfil de tributação — CST ICMS',
            'nota' => 'Quanto do valor de operação passa por cada CST (tributado, substituição tributária, isento, não-tributado). Fonte: C190.',
            'colunas' => ['CST', 'Descrição', 'Linhas C190', 'Valor de operação', '% do valor'],
            'formatos' => [2 => XlsxReport::FMT_INT, 3 => XlsxReport::FMT_BRL, 4 => XlsxReport::FMT_PCT],
            'linhas' => $linhas,
            'grafico' => ['tipo' => 'stacked', 'itens' => $grafico],
        ];
    }

    /**
     * Débito × crédito × saldo, mês a mês, por tributo. O Resumo Fiscal apura UMA
     * competência — a série do período só existe aqui.
     */
    private function secaoTributosMensal(array $tributario): array
    {
        $periodos = collect($tributario['por_periodo']);

        $linhas = $periodos->map(fn ($p) => [
            $this->mesLabel($p['mes']),
            (float) $p['icms_debito'], (float) $p['icms_credito'], (float) $p['saldo_icms'],
            (float) $p['pis_debito'], (float) $p['pis_credito'], (float) $p['saldo_pis'],
            (float) $p['cofins_debito'], (float) $p['cofins_credito'], (float) $p['saldo_cofins'],
        ])->all();

        $colunas = $periodos->map(fn ($p) => [
            'label' => $this->mesCurto($p['mes']),
            'series' => [
                ['valor' => (float) $p['saldo_icms'], 'hex' => '#2563eb'],
                ['valor' => (float) $p['saldo_pis'], 'hex' => '#0d9488'],
                ['valor' => (float) $p['saldo_cofins'], 'hex' => '#b45309'],
            ],
        ])->all();

        $fmt = XlsxReport::FMT_BRL;

        return [
            'chave' => 'tributos-mensal',
            'titulo' => 'Saldo mensal por tributo (débito × crédito)',
            'nota' => 'Saldo = débito (saídas) − crédito (entradas). ICMS vem do C190; PIS/COFINS dos itens da EFD Contribuições. Saldo negativo = crédito acumulado no mês.',
            'colunas' => ['Mês', 'ICMS débito', 'ICMS crédito', 'Saldo ICMS', 'PIS débito', 'PIS crédito', 'Saldo PIS', 'COFINS débito', 'COFINS crédito', 'Saldo COFINS'],
            'formatos' => [1 => $fmt, 2 => $fmt, 3 => $fmt, 4 => $fmt, 5 => $fmt, 6 => $fmt, 7 => $fmt, 8 => $fmt, 9 => $fmt],
            'linhas' => $linhas,
            'grafico' => [
                'tipo' => 'colunas',
                'colunas' => $colunas,
                'legenda' => [
                    ['label' => 'Saldo ICMS', 'hex' => '#2563eb'],
                    ['label' => 'Saldo PIS', 'hex' => '#0d9488'],
                    ['label' => 'Saldo COFINS', 'hex' => '#b45309'],
                ],
            ],
        ];
    }

    /** Alertas do acervo (duplicadas, valor zerado, CFOP inconsistente, …). Nunca foram pra PDF. */
    private function secaoAlertas(array $alertas): array
    {
        $linhas = [];
        $cores = [];

        foreach ($alertas['alertas'] as $i => $a) {
            $bloqueado = ($a['tipo'] ?? 'free') === 'paid' && ! ($a['disponivel'] ?? true);
            $linhas[] = [
                ucfirst((string) $a['severidade']),
                $a['titulo'],
                $bloqueado ? 0 : (int) $a['total_afetados'],
                $bloqueado ? 'Recurso pago — não avaliado neste acervo.' : $a['descricao'],
            ];
            $cores[$i] = [0 => self::SEVERIDADE_HEX[$a['severidade']] ?? ReportTheme::NEUTRO];
        }

        return [
            'chave' => 'alertas',
            'titulo' => 'Alertas do acervo',
            'nota' => 'Inconsistências detectadas nas notas do recorte (não são alertas de contraparte — esses vivem na Central de Alertas).',
            'colunas' => ['Severidade', 'Alerta', 'Afetados', 'Descrição'],
            'formatos' => [2 => XlsxReport::FMT_INT],
            'linhas' => $linhas,
            'cores' => $cores,
        ];
    }

    /** Exposição financeira a contrapartes irregulares, no recorte do dashboard. */
    private function secaoCompliance(array $compliance): array
    {
        $linhas = [];
        $cores = [];

        foreach ($compliance['participantes'] as $i => $p) {
            // CPF não tem situação cadastral consultável na Receita como PJ — marcar CPF em vez
            // de "Não consultado" (sugeriria pendência de consulta). Fonte única: App\Support\Documento.
            $ehCpf = \App\Support\Documento::ehCpf($p['cnpj']);

            $situacao = $p['situacao_cadastral'] ?: \App\Support\Documento::rotuloSemConsulta($p['cnpj'], 'Não consultado');

            $linhas[] = [
                $p['cnpj'],
                $p['razao_social'],
                (string) ($p['uf'] ?? ''),
                $situacao,
                (string) ($p['regime_tributario'] ?? ''),
                (int) $p['total_notas'],
                (float) $p['volume'],
                (float) $p['exposicao'],
                (string) ($p['ultima_consulta_em'] ?? ''),
            ];

            if ($p['irregular']) {
                $hex = ReportTheme::IRREGULAR;
            } elseif ($p['situacao_cadastral'] === 'ATIVA') {
                $hex = ReportTheme::OK;
            } elseif ($ehCpf) {
                $hex = ReportTheme::OUTRO; // CPF: neutro-escuro, não "sem dado"
            } else {
                $hex = ReportTheme::NEUTRO;
            }
            $cores[$i] = [3 => $hex];
        }

        return [
            'chave' => 'compliance-exposicao',
            'titulo' => 'Exposição a contrapartes irregulares',
            'nota' => 'Exposição = volume movimentado com contraparte cuja situação cadastral ≠ ATIVA. Quem nunca foi consultado não entra na exposição — entra no risco não avaliado.',
            'colunas' => ['CNPJ/CPF', 'Razão social', 'UF', 'Situação', 'Regime', 'Notas', 'Volume', 'Exposição', 'Últ. consulta'],
            'formatos' => [5 => XlsxReport::FMT_INT, 6 => XlsxReport::FMT_BRL, 7 => XlsxReport::FMT_BRL],
            'linhas' => $linhas,
            'cores' => $cores,
            'total' => ['Total', '', '', '', '', array_sum(array_column($linhas, 5)), array_sum(array_column($linhas, 6)), array_sum(array_column($linhas, 7)), ''],
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Rótulos legíveis dos filtros aplicados (vão no cabeçalho dos 3 formatos). */
    private function descreverFiltros(int $userId, array $filtros): array
    {
        $out = [];

        $inicio = $filtros['periodo_inicio'] ?? null;
        $fim = $filtros['periodo_fim'] ?? null;
        $out['Período'] = $inicio || $fim
            ? ($inicio ? $this->mesLabel($inicio) : 'início').' a '.($fim ? $this->mesLabel($fim) : 'hoje')
            : 'Todo o acervo';

        $out['Cliente'] = ! empty($filtros['cliente_id'])
            ? (Cliente::where('user_id', $userId)->find($filtros['cliente_id'])?->razao_social ?? 'Cliente #'.$filtros['cliente_id'])
            : 'Todos';

        $out['Participante'] = ! empty($filtros['participante_id'])
            ? (Participante::where('user_id', $userId)->find($filtros['participante_id'])?->razao_social ?? 'Participante #'.$filtros['participante_id'])
            : 'Todos';

        $tipo = $filtros['tipo_efd'] ?? 'todos';
        $out['Tipo EFD'] = ($tipo === '' || $tipo === 'todos') ? 'Todos (deduplicado)' : $tipo;

        $out['Importação'] = ! empty($filtros['importacao_id'])
            ? (EfdImportacao::where('user_id', $userId)->find($filtros['importacao_id'])?->filename ?? '#'.$filtros['importacao_id'])
            : 'Todas';

        return $out;
    }

    private function brl(float|int|null $v): string
    {
        return 'R$ '.number_format((float) $v, 2, ',', '.');
    }

    private function mesLabel(?string $mes): string
    {
        if (! $mes) {
            return '';
        }

        return Carbon::parse($mes.'-01')->translatedFormat('F/Y');
    }

    private function mesCurto(?string $mes): string
    {
        if (! $mes) {
            return '';
        }

        return Carbon::parse($mes.'-01')->format('m/y');
    }

    private function data(?string $iso): string
    {
        return $iso ? Carbon::parse($iso)->format('d/m/Y') : '';
    }
}
