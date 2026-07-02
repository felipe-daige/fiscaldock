<?php

namespace App\Services\Bi\Export;

use App\Support\Reports\ReportTheme;
use App\Support\Reports\XlsxReport;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * XLSX do catálogo de itens (mesma fonte do PDF/CSV: itensAgregados + filtros).
 * Segue o modelo de design aprovado no BI (docs/bi/export-planilhas.md);
 * números reais com formato — quantidade/alíquota/valor somáveis direto.
 */
class CatalogoItensXlsxBuilder
{
    /**
     * @param  Collection<int,array<string,mixed>>  $itens
     * @param  list<array{rotulo:string,valor:string}>  $resumoFiltros
     */
    public function download(Collection $itens, array $resumoFiltros, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'catxlsx');
        if ($tmp === false) {
            throw new \RuntimeException('Falha ao criar arquivo temporário para o XLSX.');
        }

        $this->gerarArquivo($itens, $resumoFiltros, $tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * @param  Collection<int,array<string,mixed>>  $itens
     * @param  list<array{rotulo:string,valor:string}>  $resumoFiltros
     */
    public function gerarArquivo(Collection $itens, array $resumoFiltros, string $path): void
    {
        $filtros = collect($resumoFiltros)
            ->map(fn ($f) => ($f['rotulo'] ?? '').': '.($f['valor'] ?? ''))
            ->implode(' · ');

        $xlsx = XlsxReport::paraArquivo($path)
            ->addSheet('Catálogo de Itens')
            ->larguras(14, 44, 12, 12, 22, 14, 12, 12, 12, 16, 30)
            ->tituloMarca(ReportTheme::brandName().' — Catálogo de Itens')
            ->subtitulo(($filtros !== '' ? $filtros.' · ' : '').'Gerado em '.now()->format('d/m/Y H:i'))
            ->header(['Código', 'Descrição', 'Origem', 'NCM', 'CFOPs', 'CSTs', 'Quantidade', 'Ocorrências', 'Alíq. média %', 'Valor movimentado', 'Catálogo']);

        if ($itens->isEmpty()) {
            $xlsx->vazio('Nenhum item para os filtros aplicados.');
            $xlsx->fechar();

            return;
        }

        $formatos = [
            6 => XlsxReport::FMT_NUM,
            7 => XlsxReport::FMT_INT,
            8 => XlsxReport::FMT_PCT,
            9 => XlsxReport::FMT_BRL,
        ];

        $totQtd = 0.0;
        $totOcorrencias = 0;
        $totValor = 0.0;
        foreach ($itens as $i) {
            $xlsx->linha([
                (string) $i['codigo_item'],
                (string) ($i['descricao'] ?? ''),
                (string) $i['fontes'],
                (string) ($i['ncm'] ?? ''),
                (string) ($i['cfops'] ?? ''),
                (string) ($i['csts'] ?? ''),
                (float) $i['quantidade'],
                (int) $i['ocorrencias'],
                $i['aliquota_media'] !== null ? (float) $i['aliquota_media'] : '—',
                (float) $i['valor_total'],
                $i['tem_catalogo'] ? (string) ($i['catalogo']['descr_item'] ?? 'Sim') : 'Sem catálogo',
            ], [], $formatos);

            $totQtd += (float) $i['quantidade'];
            $totOcorrencias += (int) $i['ocorrencias'];
            $totValor += (float) $i['valor_total'];
        }

        $xlsx->totais(
            ['Total', '—', '—', '—', '—', '—', $totQtd, $totOcorrencias, '—', $totValor, '—'],
            $formatos
        );

        $xlsx->fechar();
    }
}
