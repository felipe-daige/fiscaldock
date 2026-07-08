<?php

namespace App\Services\Notas\Export;

use App\Support\CsvExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * ZIP com 1 CSV por seção do Raio-X do acervo (mesma fonte do PDF e do XLSX).
 * CSV é uma tabela — o relatório tem N seções heterogêneas, então empacota.
 * Prefixo numérico preserva a ordem do relatório. Padrão canônico pt-BR do
 * `CsvExport` (BOM + ";"). Ver `BiCsvZipBuilder`.
 */
class DashboardNotasCsvZipBuilder
{
    public function download(array $relatorio, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'dnfcsvzip');
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

    /**
     * Coluna "Valor" 100% numérica (alinhamento consistente no Excel): a unidade vai no
     * rótulo (R$/%), e o contexto textual (filtros) fica em linhas de célula única.
     */
    private function linhasResumo(array $relatorio): array
    {
        $k = $relatorio['kpis'];
        $s = $relatorio['saldos'];
        $c = $relatorio['compliance_kpis'];
        $a = $relatorio['resumo_alertas'];

        $linhas = [[$relatorio['titulo'].' · gerado em '.$relatorio['gerado_em']->format('d/m/Y H:i')]];

        foreach ($relatorio['filtros'] as $rotulo => $valor) {
            $linhas[] = [$rotulo.': '.$valor];
        }

        $linhas[] = ['Notas no recorte', (int) $k['total_notas']];
        $linhas[] = ['Aquisições (R$)', (float) $k['valor_entradas']];
        $linhas[] = ['Faturamento (R$)', (float) $k['valor_saidas']];
        $linhas[] = ['Saldo líquido (R$)', (float) $k['saldo']];
        $linhas[] = ['Contrapartes distintas', (int) $k['participantes_unicos']];

        foreach (['icms' => 'ICMS', 'pis' => 'PIS', 'cofins' => 'COFINS'] as $chave => $label) {
            $linhas[] = [$label.' — débito (R$)', (float) $s[$chave]['debito']];
            $linhas[] = [$label.' — crédito (R$)', (float) $s[$chave]['credito']];
            $linhas[] = [$label.' — saldo (R$)', (float) $s[$chave]['saldo']];
        }

        if ($relatorio['alerta_pis_cofins']) {
            $linhas[] = ['Ressalva: mais de 70% dos itens da EFD Contribuições sem PIS/COFINS — saldos subestimados.'];
        }

        $linhas[] = ['Alertas — alta', (int) $a['alta']];
        $linhas[] = ['Alertas — média', (int) $a['media']];
        $linhas[] = ['Alertas — baixa', (int) $a['baixa']];

        $linhas[] = ['Contrapartes irregulares', (int) $c['irregulares']];
        $linhas[] = ['Exposição a irregulares (R$)', (float) $c['exposicao']];
        $linhas[] = ['Contrapartes nunca consultadas', (int) $c['nao_consultados']];
        $linhas[] = ['Cobertura de consulta (%)', (float) $c['consultados_pct']];

        return $linhas;
    }

    /**
     * Normaliza células para o Excel pt-BR inferir tipo consistente por coluna:
     * float → vírgula decimal (senão "18.25" viraria texto alinhado à esquerda no
     * meio de números), "—" → vazio (senão texto solto em coluna numérica).
     */
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
