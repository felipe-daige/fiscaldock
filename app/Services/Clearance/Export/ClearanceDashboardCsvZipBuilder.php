<?php

namespace App\Services\Clearance\Export;

use App\Support\CsvExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * ZIP com 1 CSV por seção do Panorama de Clearance (mesma fonte do PDF e do XLSX).
 * CSV é uma tabela — o relatório tem N seções heterogêneas, então empacota. Prefixo
 * numérico preserva a ordem. Padrão canônico pt-BR do `CsvExport` (BOM + ";").
 */
class ClearanceDashboardCsvZipBuilder
{
    public function download(array $relatorio, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'clrcsvzip');
        if ($tmp === false) {
            throw new \RuntimeException('Falha ao criar arquivo temporário para o ZIP.');
        }

        $this->gerarArquivo($relatorio, $tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    public function gerarArquivo(array $relatorio, string $path): void
    {
        $zip = new \ZipArchive;
        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Falha ao abrir o ZIP para escrita.');
        }

        $n = 1;
        $add = function (string $slug, array $colunas, array $linhas) use ($zip, &$n): void {
            $zip->addFromString(sprintf('%02d-%s.csv', $n++, $slug), CsvExport::build($colunas, $this->sanitizar($linhas)));
        };

        $add('resumo', ['Indicador', 'Valor'], $this->linhasResumo($relatorio));

        foreach ($relatorio['ordem_secoes'] as $chave) {
            $sec = $relatorio['secoes'][$chave] ?? null;
            if ($sec === null) {
                continue;
            }

            $linhas = $sec['linhas'];
            if (! empty($sec['total'])) {
                $linhas[] = $sec['total'];
            }

            $add($chave, $sec['colunas'], $linhas);
        }

        $zip->close();
    }

    private function linhasResumo(array $relatorio): array
    {
        $r = $relatorio['resumo'];
        $b = $relatorio['backlog'];

        $linhas = [[$relatorio['titulo'].' · gerado em '.$relatorio['gerado_em']->format('d/m/Y H:i')]];

        foreach ($relatorio['filtros'] as $rotulo => $valor) {
            $linhas[] = [$rotulo.': '.$valor];
        }

        $linhas[] = ['Notas no acervo', (int) $r['total_notas']];
        $linhas[] = ['Verificadas na Receita', (int) $r['verificadas']];
        $linhas[] = ['Pendentes de verificação', (int) $r['pendentes']];
        $linhas[] = ['Cobertura de verificação (%)', (float) $r['cobertura_pct']];
        $linhas[] = ['Valor total movimentado (R$)', (float) $r['valor_total']];
        $linhas[] = ['Valor pendente de verificação (R$)', (float) $r['valor_pendente']];
        $linhas[] = ['Notas bloqueantes', (int) $r['notas_bloqueantes']];
        $linhas[] = ['Exposição bloqueante (R$)', (float) $r['valor_bloqueante']];
        $linhas[] = ['Backlog — notas a verificar', (int) $b['notas']];
        $linhas[] = ['Backlog — custo (créditos)', (int) $b['custo_creditos']];
        $linhas[] = ['Backlog — custo estimado (R$)', (float) $b['custo_reais']];

        return $linhas;
    }

    private function sanitizar(array $linhas): array
    {
        return array_map(
            fn (array $linha) => array_map(function ($v) {
                if ($v === '—') {
                    return '';
                }
                if (is_float($v)) {
                    return str_replace('.', ',', (string) round($v, 2));
                }

                return $v;
            }, $linha),
            $linhas
        );
    }
}
