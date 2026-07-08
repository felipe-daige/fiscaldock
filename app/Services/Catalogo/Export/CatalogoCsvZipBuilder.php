<?php

namespace App\Services\Catalogo\Export;

use App\Support\CsvExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * ZIP com 1 CSV por seção do Catálogo (mesma fonte do PDF e do XLSX). CSV é uma tabela;
 * o relatório tem N seções → empacota. Padrão canônico pt-BR do `CsvExport` (BOM + ";").
 */
class CatalogoCsvZipBuilder
{
    public function download(array $relatorio, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'catcsvzip');
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
        $k = $relatorio['kpis'];
        $drift = $relatorio['drift'];

        $linhas = [[$relatorio['titulo'].' · gerado em '.$relatorio['gerado_em']->format('d/m/Y H:i')]];

        foreach ($relatorio['filtros'] as $rotulo => $valor) {
            $linhas[] = [$rotulo.': '.$valor];
        }

        $linhas[] = ['Total de produtos', (int) $k['total_produtos']];
        $linhas[] = ['Com movimentação', (int) $k['com_movimentacao']];
        $linhas[] = ['Sem movimentação', (int) $k['sem_movimentacao']];
        $linhas[] = ['Valor movimentado (R$)', (float) $k['valor_movimentado']];
        $linhas[] = ['Alíquota divergente', (int) $k['aliq_divergente']];
        $linhas[] = ['NCM faltando', (int) $k['ncm_faltando']];
        $linhas[] = ['Mudanças de cadastro (drift)', (int) ($drift['total'] ?? 0)];
        $linhas[] = ['Itens afetados por drift', (int) ($drift['itens_afetados'] ?? 0)];

        return $linhas;
    }

    /**
     * float → vírgula decimal (senão vira texto no Excel pt-BR); "—" → vazio; null → vazio.
     */
    private function sanitizar(array $linhas): array
    {
        return array_map(
            fn (array $linha) => array_map(function ($v) {
                if ($v === '—' || $v === null) {
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
