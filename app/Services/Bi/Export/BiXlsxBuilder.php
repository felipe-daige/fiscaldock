<?php

namespace App\Services\Bi\Export;

use App\Services\BiExportService;
use App\Support\Dinheiro;
use App\Support\Reports\ReportTheme;
use App\Support\Reports\XlsxReport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Workbook do BI espelhando o PDF executivo (mesma fonte: relatorioCompleto):
 * Resumo + Cobertura + 1 aba por seção, na ordem do relatório.
 *
 * Valores monetários/percentuais viram número real com formato Excel
 * (o contador soma/filtra/pivota); badges de risco/situação usam ReportTheme.
 */
class BiXlsxBuilder
{
    /**
     * Espec por seção: nome da aba, tipos por coluna, linha de totais.
     * Tipos: t=texto, m=moeda (BRL string→float), i=inteiro, p=percentual, n=número.
     * 'cor' = [índice da coluna => resolvedor ReportTheme] para badge colorido.
     * Totais só em séries completas (mensal/UF/CFOP) — nunca em top-N.
     * Larguras NÃO são fixas: autoLarguras() dimensiona cada coluna pelo maior
     * conteúdo (header + células), pra abrir sem precisar arrastar a coluna.
     */
    private const SPECS = [
        'faturamento' => ['aba' => 'Faturamento', 'tipos' => 'tmi', 'totais' => true, 'bar' => 1],
        'tributos' => ['aba' => 'Tributos', 'tipos' => 'tmmmmmp', 'totais' => true, 'bar' => 5],
        'apuracao-notas' => ['aba' => 'Declarado x Computado', 'tipos' => 'tmmmmmm', 'totais' => true],
        'cfop' => ['aba' => 'CFOP', 'tipos' => 'ttmimp', 'totais' => true],
        'top-notas' => ['aba' => 'Top Notas', 'tipos' => 'ttttm', 'totais' => false],
        'catalogo' => ['aba' => 'Catálogo', 'tipos' => 'tttmn', 'totais' => false, 'bar' => 3],
        'uf' => ['aba' => 'UF', 'tipos' => 'tmi', 'totais' => true, 'bar' => 1],
        'devolucoes' => ['aba' => 'Devoluções', 'tipos' => 'tmi', 'totais' => true, 'bar' => 1],
        'riscos-notas' => ['aba' => 'Riscos - Notas', 'tipos' => 'tttttm', 'totais' => false, 'cor' => 3],
        'riscos-fornecedores' => ['aba' => 'Riscos - Fornecedores', 'tipos' => 'tttim', 'totais' => false, 'cor' => 2],
    ];

    private const FMT = [
        'm' => XlsxReport::FMT_BRL,
        'p' => XlsxReport::FMT_PCT,
        'i' => XlsxReport::FMT_INT,
        'n' => XlsxReport::FMT_NUM,
    ];

    public function __construct(protected BiExportService $biExport) {}

    public function download(array $relatorio, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'bixlsx');
        if ($tmp === false) {
            throw new \RuntimeException('Falha ao criar arquivo temporário para o XLSX.');
        }

        $this->gerarArquivo($relatorio, $tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function gerarArquivo(array $relatorio, string $path): void
    {
        $xlsx = XlsxReport::paraArquivo($path);

        $this->sheetResumo($xlsx, $relatorio);
        $this->sheetCobertura($xlsx, $relatorio);

        foreach ($relatorio['ordem_secoes'] as $chave) {
            if ($chave === 'score-carteira') {
                continue; // já entra na aba Resumo
            }
            if ($chave === 'contrapartes') {
                $this->sheetContrapartes($xlsx, $relatorio);

                continue;
            }
            if ($chave === 'dossie-participantes') {
                $this->sheetDossieParticipantes($xlsx, $relatorio);

                continue;
            }
            $this->sheetSecao($xlsx, $relatorio, $chave);
        }

        $xlsx->fechar();
    }

    // ── Resumo (KPIs + score da carteira) ─────────────────────────────

    private function sheetResumo(XlsxReport $xlsx, array $relatorio): void
    {
        $k = $relatorio['kpis'];
        $p = $relatorio['periodo'];
        $modo = ($relatorio['modo'] ?? 'portfolio') === 'cliente'
            ? 'Cliente #'.$p['cliente_id']
            : 'Carteira (todos os clientes)';

        $xlsx->addSheet('Resumo')
            ->larguras(42, 16)
            ->tituloMarca(ReportTheme::brandName().' — BI Fiscal · Relatório Executivo', 2)
            ->subtitulo($modo.' · Período: '.($p['inicio'] ?? 'Todos').' a '.($p['fim'] ?? 'Todos').' · Gerado em '.now()->format('d/m/Y H:i'))
            ->header(['Indicador', 'Valor']);

        $brl = fn (string $v) => Dinheiro::deBrl($v);
        $xlsx->linhaKV('Faturamento', $brl($k['faturamento']), XlsxReport::FMT_BRL)
            ->linhaKV('Aquisições', $brl($k['aquisicoes']), XlsxReport::FMT_BRL)
            ->linhaKV('Tributos (débito s/ saída)', $brl($k['tributos']), XlsxReport::FMT_BRL)
            ->linhaKV('A recolher (apurado)', $brl($relatorio['a_recolher_brl'] ?? '0,00'), XlsxReport::FMT_BRL)
            ->linhaKV('Saldo líquido', $brl($k['saldo_liquido']), XlsxReport::FMT_BRL)
            ->linhaKV('Total de notas', (int) $k['total_notas'], XlsxReport::FMT_INT)
            ->linhaKV('Alíquota média', (float) $k['aliquota_media'], XlsxReport::FMT_PCT);

        $sc = $relatorio['score_carteira'] ?? null;
        if ($sc) {
            // Valores 100% numéricos na coluna (alinhamento consistente):
            // nada de "6 / 48" — irregulares e ativos viram linhas próprias.
            $xlsx->linhaKV('Score da carteira — % regular', (float) $sc['percentual_regular'], XlsxReport::FMT_PCT)
                ->linhaKV('Score da carteira — irregulares', (int) $sc['irregulares'], XlsxReport::FMT_INT)
                ->linhaKV('Score da carteira — participantes ativos', (int) $sc['participantes_ativos'], XlsxReport::FMT_INT)
                ->linhaKV('Score da carteira — % em risco', (float) $sc['percentual_em_risco'], XlsxReport::FMT_PCT)
                ->linhaKV('Score da carteira — valor em risco', $brl($sc['valor_total_em_risco_brl']), XlsxReport::FMT_BRL);
        }

        $cc = $relatorio['cobertura_consulta'] ?? [];
        if (($cc['sem_consulta'] ?? 0) > 0) {
            $xlsx->linhaKV('Participantes nunca consultados', (int) $cc['sem_consulta'], XlsxReport::FMT_INT, ReportTheme::ALERTA);
        }
    }

    // ── Cobertura de fonte por mês ────────────────────────────────────

    private function sheetCobertura(XlsxReport $xlsx, array $relatorio): void
    {
        $cob = $relatorio['cobertura'] ?? [];
        $semFiscal = collect($cob['meses_sem_fiscal'] ?? [])->pluck('mes')->all();
        $semContrib = collect($cob['meses_sem_contrib'] ?? [])->pluck('mes')->all();
        $gap = collect($cob['meses_gap_total'] ?? [])->pluck('mes')->all();

        $xlsx->addSheet('Cobertura')
            ->larguras(10, 13, 15)
            ->tituloMarca('Cobertura de fonte por mês', 3)
            ->header(['Mês', 'EFD ICMS/IPI', 'EFD PIS/COFINS']);

        $todos = collect(array_merge($semFiscal, $semContrib, $gap))->unique()->sort()->values();
        if ($todos->isEmpty()) {
            $xlsx->vazio('Cobertura completa no período — nenhum mês sem EFD.');

            return;
        }

        foreach ($todos as $mes) {
            $temFiscal = ! in_array($mes, $semFiscal, true) && ! in_array($mes, $gap, true);
            $temContrib = ! in_array($mes, $semContrib, true) && ! in_array($mes, $gap, true);
            $xlsx->linha(
                [$mes, $temFiscal ? 'Sim' : 'FALTA', $temContrib ? 'Sim' : 'FALTA'],
                array_filter([1 => $temFiscal ? null : ReportTheme::ALERTA, 2 => $temContrib ? null : ReportTheme::ALERTA])
            );
        }
    }

    // ── Contrapartes (badge de risco colorido) ────────────────────────

    private function sheetContrapartes(XlsxReport $xlsx, array $relatorio): void
    {
        $sec = $relatorio['secoes']['contrapartes'] ?? null;
        if (! $sec) {
            return;
        }

        $tab = $this->biExport->contrapartesTabela($sec);
        $modoCliente = ($sec['modo'] ?? '') === 'cliente';
        // Colunas variam por modo (ver contrapartesTabela): cliente tem Papel; portfólio tem Ticket.
        $tipos = $modoCliente ? 'ttttimit' : 'tttimimt';
        $idxClassificacao = $modoCliente ? 3 : 2;

        $xlsx->addSheet('Contrapartes')
            ->larguras(...$this->autoLarguras($tab['colunas'], $tab['linhas'], $tipos))
            ->tituloMarca($sec['titulo'] ?? 'Principais contrapartes', count($tab['colunas']))
            ->header($tab['colunas']);

        if ($tab['linhas'] === []) {
            $xlsx->vazio();

            return;
        }

        foreach ($tab['linhas'] as $i => $linha) {
            [$valores, $formatos] = $this->converter($linha, $tipos);
            $classificacao = $tab['classificacoes'][$i] ?? null;
            $xlsx->linha($valores, [$idxClassificacao => ReportTheme::riscoHex($classificacao)], $formatos);
        }
    }

    // ── Participantes: todos, entradas × saídas (badge de risco colorido) ─────

    private function sheetDossieParticipantes(XlsxReport $xlsx, array $relatorio): void
    {
        $sec = $relatorio['secoes']['dossie-participantes'] ?? null;
        if (! $sec) {
            return;
        }

        // 12 colunas de datasetTodosParticipantes: razão, cnpj, uf (t), notas (i), valor mov (m),
        // ent qtd (i), ent val (m), sai qtd (i), sai val (m), situação (t), score (i), classificação (t).
        $tipos = 'tttimimimtit';

        $xlsx->addSheet('Participantes')
            ->larguras(...$this->autoLarguras($sec['colunas'], $sec['linhas'], $tipos))
            ->tituloMarca($sec['titulo'], count($sec['colunas']))
            ->header($sec['colunas']);

        if (empty($sec['linhas'])) {
            $xlsx->vazio('Sem participantes com movimentação EFD no escopo.');

            return;
        }

        foreach ($sec['linhas'] as $i => $linha) {
            [$valores, $formatos] = $this->converter(array_values($linha), $tipos);
            // Classificação é a última coluna (índice 11) — badge de risco colorido.
            $xlsx->linha($valores, [11 => ReportTheme::riscoHex($sec['classificacoes'][$i] ?? null)], $formatos);
        }
    }

    // ── Seções tabulares (colunas+linhas) ─────────────────────────────

    private function sheetSecao(XlsxReport $xlsx, array $relatorio, string $chave): void
    {
        $sec = $relatorio['secoes'][$chave] ?? null;
        $spec = self::SPECS[$chave] ?? null;
        if (! $sec || ! $spec || empty($sec['colunas'])) {
            return;
        }

        // Coluna "Gráfico" (barra █ proporcional) nas abas de série — sparkline in-cell,
        // já que o OpenSpout não desenha gráfico. Baseada na coluna de valor $spec['bar'].
        $barIdx = $spec['bar'] ?? null;
        $colunas = $sec['colunas'];
        $linhas = $sec['linhas'];
        $maxBar = 0.0;
        if ($barIdx !== null) {
            $colunas = [...$colunas, 'Gráfico'];
            foreach ($linhas as $l) {
                $maxBar = max($maxBar, $this->parseBrl($l[$barIdx] ?? 0));
            }
        }

        $xlsx->addSheet($spec['aba'])
            ->larguras(...$this->autoLarguras($colunas, $linhas, $spec['tipos']))
            ->tituloMarca($sec['titulo'], count($colunas))
            ->header($colunas);

        if (empty($linhas)) {
            $xlsx->vazio();

            return;
        }

        $somas = [];
        foreach ($linhas as $linha) {
            [$valores, $formatos] = $this->converter(array_values($linha), $spec['tipos']);
            if ($barIdx !== null) {
                $frac = $maxBar > 0 ? $this->parseBrl($linha[$barIdx] ?? 0) / $maxBar : 0.0;
                $valores[] = $this->barraTexto($frac);
            }

            $cores = [];
            if (isset($spec['cor'])) {
                $cores[$spec['cor']] = ReportTheme::statusHex((string) ($valores[$spec['cor']] ?? ''));
            }
            $xlsx->linha($valores, $cores, $formatos);

            if ($spec['totais']) {
                foreach ($valores as $i => $v) {
                    $t = $spec['tipos'][$i] ?? 't';
                    if (in_array($t, ['m', 'i', 'n'], true) && (is_int($v) || is_float($v))) {
                        $somas[$i] = ($somas[$i] ?? 0) + $v;
                    }
                }
            }
        }

        if ($spec['totais'] && $somas !== []) {
            $totais = ['Total'];
            $formatosTotais = [];
            for ($i = 1, $n = count($sec['colunas']); $i < $n; $i++) {
                // "—" nas colunas não somáveis (texto/percentual) — célula cinza
                // vazia parece defeito, travessão comunica "não se aplica".
                $totais[$i] = $somas[$i] ?? '—';
                $t = $spec['tipos'][$i] ?? 't';
                if (isset($somas[$i]) && isset(self::FMT[$t])) {
                    $formatosTotais[$i] = self::FMT[$t];
                }
            }
            if ($barIdx !== null) {
                $totais[] = '—'; // barra não se aplica ao total
            }
            $xlsx->totais($totais, $formatosTotais);
        }
    }

    /** BRL "1.234,56" → 1234.56. */
    private function parseBrl(mixed $brl): float
    {
        return (float) str_replace(',', '.', str_replace('.', '', (string) $brl));
    }

    /** Barra proporcional em blocos (0..1 → "████████░░░░"), 20 segmentos. */
    private function barraTexto(float $frac, int $segmentos = 20): string
    {
        $frac = max(0.0, min(1.0, $frac));
        $cheios = (int) round($frac * $segmentos);

        return str_repeat('█', $cheios).str_repeat('░', $segmentos - $cheios);
    }

    /**
     * Largura de cada coluna (em "caracteres" do Excel) dimensionada pelo maior
     * conteúdo: header + todas as células (as linhas trazem os valores já
     * formatados — "1.234,56", "SP", CNPJ, razão social). Colunas de moeda/percentual
     * ganham folga pro prefixo "R$ "/sufixo "%" que o formato numérico adiciona no
     * display. Sem auto-fit nativo no OpenSpout (writer streaming), então medimos aqui.
     * Padding de +2 e clamp [6, 60] evitam coluna colada e largura absurda.
     *
     * @param  array<int, string>  $colunas
     * @param  array<int, array<int, mixed>>  $linhas
     * @return array<int, float>
     */
    private function autoLarguras(array $colunas, array $linhas, string $tipos = ''): array
    {
        $larguras = [];
        foreach (array_values($colunas) as $i => $cabecalho) {
            $max = mb_strlen((string) $cabecalho);
            $t = $tipos[$i] ?? 't';
            $folgaFormato = $t === 'm' ? 3 : ($t === 'p' ? 1 : 0); // "R$ " / "%"

            foreach ($linhas as $linha) {
                $valor = array_values($linha)[$i] ?? '';
                $len = mb_strlen((string) $valor) + ($valor === '' || $valor === '—' ? 0 : $folgaFormato);
                if ($len > $max) {
                    $max = $len;
                }
            }

            $larguras[$i] = (float) max(6, min(60, $max + 2));
        }

        return $larguras;
    }

    /**
     * Converte uma linha formatada (strings BRL/percentuais) em valores reais
     * + mapa de formatos por coluna, guiado pela string de tipos.
     *
     * @return array{0: array<int,mixed>, 1: array<int,string>}
     */
    private function converter(array $linha, string $tipos): array
    {
        $valores = [];
        $formatos = [];
        foreach (array_values($linha) as $i => $v) {
            $t = $tipos[$i] ?? 't';
            if ($t === 't' || $v === '—' || $v === null || $v === '') {
                $valores[$i] = $v;

                continue;
            }
            $valores[$i] = match ($t) {
                'm' => Dinheiro::deBrl((string) $v),
                'i' => (int) $v,
                default => (float) str_replace(',', '.', (string) $v),
            };
            $formatos[$i] = self::FMT[$t];
        }

        return [$valores, $formatos];
    }
}
