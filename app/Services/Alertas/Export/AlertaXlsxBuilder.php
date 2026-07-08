<?php

namespace App\Services\Alertas\Export;

use App\Support\Reports\ReportTheme;
use App\Support\Reports\XlsxReport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Monta o XLSX da Central de Alertas (Resumo por classe · Alertas) a partir dos
 * grupos de AlertaCentralService::alertasAtivosAgrupados(). Espelha as colunas do
 * PDF (reports.alertas): severidade colorida + valor fiscal em risco como número
 * real (o contador soma/filtra/pivota).
 */
final class AlertaXlsxBuilder
{
    /** Severidade → hex (mesmo mapa do reports.alertas). */
    private const SEV_HEX = [
        'alta' => ReportTheme::IRREGULAR,
        'media' => ReportTheme::ALERTA,
        'baixa' => ReportTheme::NEUTRO,
    ];

    private const SEV_LABEL = [
        'alta' => 'Alta',
        'media' => 'Média',
        'baixa' => 'Baixa',
    ];

    /**
     * @param  array<int,array{key:string,label:string,cor:string,alertas:\Illuminate\Support\Collection}>  $grupos
     */
    public function gerarArquivo(array $grupos, string $path): void
    {
        $valorRiscoTotal = array_sum(array_map(
            fn ($g) => (float) $g['alertas']->sum('valor_risco'),
            $grupos
        ));
        $total = array_sum(array_map(fn ($g) => $g['alertas']->count(), $grupos));

        $xlsx = XlsxReport::paraArquivo($path);

        // ── Aba 1: Resumo por classe ─────────────────────
        $xlsx->addSheet('Resumo')
            ->larguras(28, 10, 10, 10, 10, 18)
            ->tituloMarca(ReportTheme::brandName().' — Central de Alertas')
            ->subtitulo('Gerado em '.now()->format('d/m/Y H:i').' · '.$total.' '.($total === 1 ? 'alerta ativo' : 'alertas ativos'))
            ->header(['Classe', 'Alta', 'Média', 'Baixa', 'Total', 'Em risco']);

        foreach ($grupos as $g) {
            $porSev = $g['alertas']->countBy('severidade');
            $xlsx->linha(
                [
                    $g['label'],
                    (int) ($porSev['alta'] ?? 0),
                    (int) ($porSev['media'] ?? 0),
                    (int) ($porSev['baixa'] ?? 0),
                    (int) $g['alertas']->count(),
                    (float) $g['alertas']->sum('valor_risco'),
                ],
                [0 => $g['cor']],
                [1 => XlsxReport::FMT_INT, 2 => XlsxReport::FMT_INT, 3 => XlsxReport::FMT_INT, 4 => XlsxReport::FMT_INT, 5 => XlsxReport::FMT_BRL],
            );
        }

        $xlsx->totais(
            ['Total', '', '', '', $total, $valorRiscoTotal],
            [4 => XlsxReport::FMT_INT, 5 => XlsxReport::FMT_BRL],
        );

        // ── Aba 2: Alertas (linha por alerta) ────────────
        $xlsx->addSheet('Alertas')
            ->larguras(20, 12, 42, 60, 30, 30, 20, 10, 16, 14, 14)
            ->header([
                'Classe', 'Severidade', 'Alerta', 'Descrição', 'Cliente',
                'Participante', 'Documento', 'Afetados', 'Em risco', 'Vence em', 'Criado em',
            ]);

        foreach ($grupos as $g) {
            foreach ($g['alertas'] as $a) {
                $sev = (string) $a->severidade;
                $xlsx->linha(
                    [
                        $g['label'],
                        self::SEV_LABEL[$sev] ?? ucfirst($sev),
                        (string) $a->titulo,
                        (string) $a->descricao,
                        $a->cliente?->razao_social ?? '',
                        $a->participante?->razao_social ?? '',
                        $a->participante?->documento ?? '',
                        (int) $a->total_afetados,
                        (float) $a->valor_risco,
                        $a->vence_em?->format('d/m/Y') ?? '',
                        $a->created_at?->format('d/m/Y') ?? '',
                    ],
                    [1 => self::SEV_HEX[$sev] ?? ReportTheme::NEUTRO],
                    [7 => XlsxReport::FMT_INT, 8 => XlsxReport::FMT_BRL],
                );
            }
        }

        $xlsx->fechar();
    }

    /**
     * Mesmo conteúdo, embrulhado como download HTTP. Grava num arquivo temporário
     * e deixa o framework removê-lo após o envio.
     *
     * @param  array<int,array{key:string,label:string,cor:string,alertas:\Illuminate\Support\Collection}>  $grupos
     */
    public function download(array $grupos, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsxrep');

        if ($tmp === false) {
            throw new \RuntimeException('Falha ao criar arquivo temporário para o XLSX.');
        }

        $this->gerarArquivo($grupos, $tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
