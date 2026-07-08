<?php

namespace App\Services\Clientes\Export;

use App\Support\Reports\ReportTheme;
use App\Support\Reports\XlsxReport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * XLSX da carteira de clientes a partir do payload do `ClienteListagemBuilder` (mesma
 * fonte do PDF `reports/clientes-listagem`). Colunas idênticas ao PDF; valor movimentado
 * como número real (o contador soma/filtra/pivota), regularidade em célula colorida.
 */
final class ClienteListagemXlsxBuilder
{
    /** Regularidade → hex (mesmo mapa do PDF de listagem). */
    private const REG_HEX = [
        'regular' => ReportTheme::OK,
        'irregular' => ReportTheme::IRREGULAR,
        'indeterminada' => ReportTheme::ALERTA,
        'nao_consultado' => ReportTheme::NEUTRO,
        'cpf' => ReportTheme::OUTRO, // CPF: neutro-escuro, não "sem dado"
    ];

    /**
     * @param  array{clientes:array<int,array<string,mixed>>,total:int,total_movimentado:float,gerado_em:string}  $dados
     */
    public function gerarArquivo(array $dados, string $path): void
    {
        $total = (int) $dados['total'];

        $xlsx = XlsxReport::paraArquivo($path);

        $xlsx->addSheet('Clientes')
            ->larguras(38, 20, 8, 6, 16, 20, 18, 16, 14)
            ->tituloMarca(ReportTheme::brandName().' — Carteira de Clientes')
            ->subtitulo('Gerado em '.$dados['gerado_em'].' · '.$total.' '.($total === 1 ? 'cliente' : 'clientes'))
            ->header(['Cliente', 'Documento', 'Tipo', 'UF', 'Situação', 'Regime', 'Movimentado', 'Regularidade', 'Últ. consulta']);

        foreach ($dados['clientes'] as $c) {
            $xlsx->linha(
                [
                    $c['nome'],
                    $c['documento'],
                    $c['tipo'],
                    $c['uf'],
                    $c['situacao'],
                    $c['regime'],
                    (float) $c['movimentado'],
                    $c['regularidade'],
                    $c['ultima_consulta'] ?? '',
                ],
                [7 => self::REG_HEX[$c['regularidade_classe']] ?? ReportTheme::NEUTRO],
                [6 => XlsxReport::FMT_BRL],
            );
        }

        $xlsx->totais(
            ['Total', '', '', '', '', '', (float) $dados['total_movimentado'], '', ''],
            [6 => XlsxReport::FMT_BRL],
        );

        $xlsx->fechar();
    }

    /**
     * @param  array{clientes:array<int,array<string,mixed>>,total:int,total_movimentado:float,gerado_em:string}  $dados
     */
    public function download(array $dados, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'clixlsx');

        if ($tmp === false) {
            throw new \RuntimeException('Falha ao criar arquivo temporário para o XLSX.');
        }

        $this->gerarArquivo($dados, $tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
