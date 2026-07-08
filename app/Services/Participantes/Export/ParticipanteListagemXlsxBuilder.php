<?php

namespace App\Services\Participantes\Export;

use App\Support\Reports\ReportTheme;
use App\Support\Reports\XlsxReport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * XLSX da listagem de participantes a partir do payload do `ParticipanteListagemBuilder`
 * (mesma fonte do PDF `reports/participantes-listagem`). Volume = dedup P1 escopado, o
 * mesmo número da ficha/dossiê/Score Fiscal. Dinheiro e contagem como números reais.
 */
final class ParticipanteListagemXlsxBuilder
{
    private const REG_HEX = [
        'regular' => ReportTheme::OK,
        'irregular' => ReportTheme::IRREGULAR,
        'indeterminada' => ReportTheme::ALERTA,
        'nao_consultado' => ReportTheme::NEUTRO,
        'cpf' => ReportTheme::OUTRO, // CPF: neutro-escuro, não "sem dado"
    ];

    /** Papel fiscal — tinta neutra (informativo, não é juízo de risco). Igual ao PDF. */
    private const PAPEL_HEX = [
        'fornecedor' => '#1d4ed8',
        'cliente' => '#047857',
        'ambos' => '#7c3aed',
        'sem_movimentacao' => ReportTheme::NEUTRO,
    ];

    /**
     * @param  array{participantes:array<int,array<string,mixed>>,total:int,total_movimentado:float,gerado_em:string}  $dados
     */
    public function gerarArquivo(array $dados, string $path): void
    {
        $total = (int) $dados['total'];

        $xlsx = XlsxReport::paraArquivo($path);

        $xlsx->addSheet('Participantes')
            ->larguras(38, 20, 6, 16, 20, 14, 10, 18, 16, 14)
            ->tituloMarca(ReportTheme::brandName().' — Participantes')
            ->subtitulo('Gerado em '.$dados['gerado_em'].' · '.$total.' '.($total === 1 ? 'participante' : 'participantes'))
            ->header(['Participante', 'Documento', 'UF', 'Situação', 'Regime', 'Papel', 'Notas', 'Movimentado', 'Regularidade', 'Últ. consulta']);

        foreach ($dados['participantes'] as $p) {
            $xlsx->linha(
                [
                    $p['nome'],
                    $p['documento'],
                    $p['uf'],
                    $p['situacao'],
                    $p['regime'],
                    $p['papel'],
                    (int) $p['notas'],
                    (float) $p['movimentado'],
                    $p['regularidade'],
                    $p['ultima_consulta'] ?? '',
                ],
                [
                    5 => self::PAPEL_HEX[$p['papel_classe']] ?? ReportTheme::NEUTRO,
                    8 => self::REG_HEX[$p['regularidade_classe']] ?? ReportTheme::NEUTRO,
                ],
                [6 => XlsxReport::FMT_INT, 7 => XlsxReport::FMT_BRL],
            );
        }

        $xlsx->totais(
            ['Total', '', '', '', '', '', '', (float) $dados['total_movimentado'], '', ''],
            [7 => XlsxReport::FMT_BRL],
        );

        $xlsx->fechar();
    }

    /**
     * @param  array{participantes:array<int,array<string,mixed>>,total:int,total_movimentado:float,gerado_em:string}  $dados
     */
    public function download(array $dados, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'partxlsx');

        if ($tmp === false) {
            throw new \RuntimeException('Falha ao criar arquivo temporário para o XLSX.');
        }

        $this->gerarArquivo($dados, $tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
