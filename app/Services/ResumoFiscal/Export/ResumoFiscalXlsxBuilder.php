<?php

namespace App\Services\ResumoFiscal\Export;

use App\Support\Reports\ReportTheme;
use App\Support\Reports\XlsxReport;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * XLSX do Fechamento Fiscal: 1 aba por seção, na mesma ordem da tela e do PDF
 * (`reports/resumo-fiscal`), lendo o mesmo payload do `ResumoFiscalService`.
 * Dinheiro sempre como float + FMT_BRL (número real — o contador soma/pivota).
 *
 * Este é o export COMPLETO do fechamento. O CSV legado (`exportar`) cobre só a
 * seção "A Recolher" — ver spec `2026-07-08-planilhas-*`.
 */
final class ResumoFiscalXlsxBuilder
{
    private const SEV_HEX = [
        'alta' => ReportTheme::IRREGULAR,
        'media' => ReportTheme::ALERTA,
        'info' => ReportTheme::NEUTRO,
    ];

    private const SEV_LABEL = ['alta' => 'Alta', 'media' => 'Média', 'info' => 'Info'];

    /** Flag canônico do cruzamento → cor/rótulo. */
    private const FLAG_HEX = [
        'verde' => ReportTheme::OK,
        'amarelo' => ReportTheme::ALERTA,
        'vermelho' => ReportTheme::IRREGULAR,
    ];

    private const FLAG_LABEL = ['verde' => 'OK', 'amarelo' => 'Atenção', 'vermelho' => 'Diverge'];

    public function gerarArquivo(array $d, string $path): void
    {
        $xlsx = XlsxReport::paraArquivo($path);
        $nome = $d['cliente']->razao_social ?: $d['cliente']->nome;
        $ctx = $nome.' · '.$d['competenciaLabel'].' · gerado em '.$d['geradoEm']->format('d/m/Y H:i');

        $this->abaVisaoDoMes($xlsx, $d, $ctx);
        $this->abaARecolher($xlsx, $d);
        $this->abaEstaBatendo($xlsx, $d);
        $this->abaAlertas($xlsx, $d);
        $this->abaIcms($xlsx, $d);
        $this->abaPisCofins($xlsx, $d);
        $this->abaRetencoes($xlsx, $d);

        $xlsx->fechar();
    }

    private function abaVisaoDoMes(XlsxReport $xlsx, array $d, string $ctx): void
    {
        $xlsx->addSheet('Visão do Mês')
            ->larguras(34, 20, 16)
            ->tituloMarca(ReportTheme::brandName().' — Fechamento Fiscal')
            ->subtitulo($ctx);

        $resumo = $d['resumo'];
        if (empty($resumo['tem_dados'])) {
            $xlsx->vazio('Sem dados de apuração para esta competência.');

            return;
        }

        $xlsx->header(['Indicador', 'Valor', 'Δ vs anterior (%)']);

        $k = $resumo['kpis'];
        $cards = [
            ['ICMS a recolher', $k['icms_a_recolher']],
            ['PIS a recolher', $k['pis_a_recolher']],
            ['COFINS a recolher', $k['cofins_a_recolher']],
            ['Retenções compensáveis', $k['retencoes_compensaveis']],
            ['Saldo líquido', $k['saldo_liquido']],
        ];
        foreach ($cards as [$label, $kpi]) {
            $xlsx->linha(
                [$label, (float) $kpi['valor'], (float) ($kpi['delta']['percentual'] ?? 0)],
                [],
                [1 => XlsxReport::FMT_BRL, 2 => XlsxReport::FMT_PCT],
            );
        }

        if ($k['saldo_liquido']['parcial'] ?? false) {
            $xlsx->vazio('Competência incompleta — apenas uma das EFDs foi importada.');
        }
    }

    private function abaARecolher(XlsxReport $xlsx, array $d): void
    {
        $xlsx->addSheet('A Recolher')->larguras(30, 18, 16, 14, 10);

        $linhas = $d['aRecolher']['linhas'] ?? [];
        if ($linhas === []) {
            $xlsx->vazio('Nada a recolher nesta competência.');

            return;
        }

        $xlsx->header(['Tributo', 'Valor', 'Vencimento', 'Estimado', 'Fonte']);
        foreach ($linhas as $l) {
            $xlsx->linha(
                [
                    $l['tributo'],
                    (float) $l['valor'],
                    $l['vencimento'] ? Carbon::parse($l['vencimento'])->format('d/m/Y') : '',
                    $l['vencimento_estimado'] ? 'sim' : 'não',
                    $l['fonte'] ?? '',
                ],
                [],
                [1 => XlsxReport::FMT_BRL],
            );
        }
        $xlsx->totais(['Total do mês', (float) $d['aRecolher']['total'], '', '', ''], [1 => XlsxReport::FMT_BRL]);
    }

    private function abaEstaBatendo(XlsxReport $xlsx, array $d): void
    {
        $xlsx->addSheet('Está Batendo')->larguras(38, 18, 18, 12, 12);

        $cz = $d['cruzamentos'];
        $icms = $cz['icms'] ?? [];
        $pc = $cz['pis_cofins'] ?? [];
        $ret = $cz['retencoes'] ?? [];

        $linhas = [];
        if (! empty($icms['tem_dados'])) {
            $linhas[] = ['ICMS débitos (E110 × C190)', $icms['declarado_debito'] ?? 0, $icms['notas_debito'] ?? 0, $icms['divergencia_debito_pct'] ?? 0, $icms['status_debito'] ?? 'verde'];
            $linhas[] = ['ICMS créditos (E110 × C190)', $icms['declarado_credito'] ?? 0, $icms['notas_credito'] ?? 0, $icms['divergencia_credito_pct'] ?? 0, $icms['status_credito'] ?? 'verde'];
        }
        if (! empty($pc['pis_declarado']) || ! empty($pc['pis_notas'])) {
            $linhas[] = ['PIS a recolher (M200 × notas)', $pc['pis_declarado'] ?? 0, $pc['pis_notas'] ?? 0, $pc['pis_divergencia_pct'] ?? 0, $pc['pis_status'] ?? 'verde'];
            $linhas[] = ['COFINS a recolher (M600 × notas)', $pc['cofins_declarado'] ?? 0, $pc['cofins_notas'] ?? 0, $pc['cofins_divergencia_pct'] ?? 0, $pc['cofins_status'] ?? 'verde'];
        }

        if ($linhas === [] && empty($ret['tem_dados'])) {
            $xlsx->vazio('Sem dados de cruzamento para esta competência.');

            return;
        }

        if ($linhas !== []) {
            $xlsx->header(['Cruzamento', 'Declarado', 'Notas', 'Div. (%)', 'Status']);
            foreach ($linhas as [$label, $decl, $notas, $pct, $flag]) {
                $xlsx->linha(
                    [$label, (float) $decl, (float) $notas, (float) $pct, self::FLAG_LABEL[$flag] ?? $flag],
                    [4 => self::FLAG_HEX[$flag] ?? ReportTheme::NEUTRO],
                    [1 => XlsxReport::FMT_BRL, 2 => XlsxReport::FMT_BRL, 3 => XlsxReport::FMT_PCT],
                );
            }
        }

        if (! empty($ret['tem_dados'])) {
            $xlsx->header(['Retenções na fonte', 'Retido (F600)', 'Deduzido (bloco M)', 'Não compensado', '']);
            $naoComp = (float) ($ret['nao_compensado'] ?? 0);
            $xlsx->linha(
                ['PIS/COFINS retido × deduzido', (float) $ret['total_retido'], (float) $ret['deduzido_apuracao'], $naoComp, ''],
                [3 => $naoComp > 0.01 ? ReportTheme::ALERTA : ReportTheme::OK],
                [1 => XlsxReport::FMT_BRL, 2 => XlsxReport::FMT_BRL, 3 => XlsxReport::FMT_BRL],
            );
        }
    }

    private function abaAlertas(XlsxReport $xlsx, array $d): void
    {
        $xlsx->addSheet('Alertas')->larguras(12, 16, 44, 70, 16);

        $alertas = $d['alertas']['alertas'] ?? [];
        if ($alertas === []) {
            $xlsx->vazio('Nenhum alerta fiscal nesta competência.');

            return;
        }

        $xlsx->header(['Severidade', 'Categoria', 'Alerta', 'Descrição', 'Valor']);
        foreach ($alertas as $a) {
            $sev = (string) $a['severidade'];
            $xlsx->linha(
                [
                    self::SEV_LABEL[$sev] ?? ucfirst($sev),
                    $a['categoria'],
                    $a['titulo'],
                    $a['descricao'] ?? '',
                    isset($a['valor']) ? (float) $a['valor'] : '',
                ],
                [0 => self::SEV_HEX[$sev] ?? ReportTheme::NEUTRO],
                [4 => XlsxReport::FMT_BRL],
            );
        }
    }

    private function abaIcms(XlsxReport $xlsx, array $d): void
    {
        $xlsx->addSheet('ICMS-IPI')->larguras(38, 20);

        $icms = $d['icms'];
        if (empty($icms['tem_dados'])) {
            $xlsx->vazio('Sem dados de apuração ICMS/IPI para esta competência.');

            return;
        }

        $xlsx->header(['Linha da apuração (E110)', 'Valor']);
        $ip = $icms['icms_proprio'];
        $linhas = [
            ['Débitos', 'tot_debitos'],
            ['Ajustes de débito', 'tot_aj_debitos'],
            ['Estornos de crédito', 'estornos_credito'],
            ['Créditos', 'tot_creditos'],
            ['Ajustes de crédito', 'tot_aj_creditos'],
            ['Saldo credor anterior', 'sld_credor_ant'],
            ['Deduções', 'tot_deducoes'],
            ['ICMS a recolher', 'a_recolher'],
            ['Saldo credor a transportar', 'sld_credor_transportar'],
            ['Débitos especiais', 'deb_especiais'],
        ];
        foreach ($linhas as [$label, $key]) {
            $xlsx->linha([$label, (float) ($ip[$key] ?? 0)], [], [1 => XlsxReport::FMT_BRL]);
        }

        if (! empty($icms['tem_st']) && ! empty($icms['icms_st'])) {
            $xlsx->totais(['ICMS-ST a recolher ('.($icms['icms_st']['uf'] ?? '').')', (float) $icms['icms_st']['icms_recolher']], [1 => XlsxReport::FMT_BRL]);
        }
    }

    private function abaPisCofins(XlsxReport $xlsx, array $d): void
    {
        $xlsx->addSheet('PIS-COFINS')->larguras(38, 18, 18);

        $pc = $d['pisCofins'];
        if (empty($pc['tem_dados'])) {
            $xlsx->vazio('Sem dados de apuração PIS/COFINS para esta competência.');

            return;
        }

        $xlsx->header(['Linha da apuração (bloco M)', 'PIS', 'COFINS']);
        $p = $pc['pis'];
        $c = $pc['cofins'];
        $linhas = [
            ['Não cumulativo', 'nao_cumulativo'],
            ['Crédito descontado', 'credito_descontado'],
            ['Devida (não cumulativo)', 'nc_devida'],
            ['Retenção', 'retencao_nc'],
            ['A recolher (não cumulativo)', 'nc_recolher'],
            ['Cumulativo', 'cumulativo'],
            ['A recolher (cumulativo)', 'cum_recolher'],
        ];
        foreach ($linhas as [$label, $key]) {
            $xlsx->linha(
                [$label, (float) ($p[$key] ?? 0), (float) ($c[$key] ?? 0)],
                [],
                [1 => XlsxReport::FMT_BRL, 2 => XlsxReport::FMT_BRL],
            );
        }
        $xlsx->totais(
            ['Total a recolher', (float) $p['total_recolher'], (float) $c['total_recolher']],
            [1 => XlsxReport::FMT_BRL, 2 => XlsxReport::FMT_BRL],
        );
    }

    private function abaRetencoes(XlsxReport $xlsx, array $d): void
    {
        $xlsx->addSheet('Retenções')->larguras(12, 20, 34, 16, 16, 16);

        $ret = $d['retencoes'];
        if (empty($ret['tem_dados'])) {
            $xlsx->vazio('Sem retenções na fonte nesta competência.');

            return;
        }

        $xlsx->header(['Data', 'Documento', 'Natureza', 'PIS', 'COFINS', 'Total']);
        foreach ($ret['retencoes'] as $r) {
            $xlsx->linha(
                [$r['data'], $r['documento'], $r['natureza'], (float) $r['valor_pis'], (float) $r['valor_cofins'], (float) $r['total']],
                [],
                [3 => XlsxReport::FMT_BRL, 4 => XlsxReport::FMT_BRL, 5 => XlsxReport::FMT_BRL],
            );
        }
        $xlsx->totais(
            ['Total retido', '', '', '', '', (float) $ret['kpis']['total_retido']],
            [5 => XlsxReport::FMT_BRL],
        );
    }

    public function download(array $dados, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'rfxlsx');

        if ($tmp === false) {
            throw new \RuntimeException('Falha ao criar arquivo temporário para o XLSX.');
        }

        $this->gerarArquivo($dados, $tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
