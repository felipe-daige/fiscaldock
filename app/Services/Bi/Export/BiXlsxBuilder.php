<?php

namespace App\Services\Bi\Export;

use App\Support\Reports\XlsxReport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BiXlsxBuilder
{
    /**
     * Workbook multi-sheet do BI: Resumo + Cobertura + as 4 seções.
     */
    public function download(array $relatorio, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'bixlsx');
        if ($tmp === false) {
            throw new \RuntimeException('Falha ao criar arquivo temporário para o XLSX.');
        }

        $this->gerar($relatorio, $tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function gerar(array $relatorio, string $path): void
    {
        $xlsx = XlsxReport::paraArquivo($path);

        // Sheet Resumo (KPIs + período)
        $k = $relatorio['kpis'];
        $p = $relatorio['periodo'];
        $xlsx->addSheet('Resumo')
            ->tituloMarca('BI Fiscal — Resumo')
            ->header(['Indicador', 'Valor']);
        foreach ([
            ['Período início', $p['inicio'] ?? 'Todos'],
            ['Período fim', $p['fim'] ?? 'Todos'],
            ['Faturamento', 'R$ '.$k['faturamento']],
            ['Aquisições', 'R$ '.$k['aquisicoes']],
            ['Tributos', 'R$ '.$k['tributos']],
            ['Saldo líquido', 'R$ '.$k['saldo_liquido']],
            ['Total de notas', $k['total_notas']],
            ['Alíquota média %', $k['aliquota_media']],
        ] as $linha) {
            $xlsx->linha($linha);
        }

        // Sheet Cobertura (1 linha por mês)
        $cob = $relatorio['cobertura'] ?? [];
        $semFiscal = collect($cob['meses_sem_fiscal'] ?? [])->pluck('mes')->all();
        $semContrib = collect($cob['meses_sem_contrib'] ?? [])->pluck('mes')->all();
        $gap = collect($cob['meses_gap_total'] ?? [])->pluck('mes')->all();
        $xlsx->addSheet('Cobertura')
            ->tituloMarca('Cobertura de fonte por mês')
            ->header(['Mês', 'EFD ICMS/IPI', 'EFD PIS/COFINS']);
        $todos = collect(array_merge($semFiscal, $semContrib, $gap))->unique()->sort()->values();
        foreach ($todos as $mes) {
            $temFiscal = ! in_array($mes, $semFiscal, true) && ! in_array($mes, $gap, true);
            $temContrib = ! in_array($mes, $semContrib, true) && ! in_array($mes, $gap, true);
            $xlsx->linha([$mes, $temFiscal ? 'Sim' : '— FALTA', $temContrib ? 'Sim' : '— FALTA']);
        }

        // Sheets das 4 seções
        $nomes = [
            'faturamento' => 'Faturamento', 'tributos' => 'Tributos',
            'apuracao-notas' => 'Declarado x Computado', 'cfop' => 'CFOP',
        ];
        foreach ($nomes as $aba => $nome) {
            $sec = $relatorio['secoes'][$aba] ?? null;
            if (! $sec) {
                continue;
            }
            $xlsx->addSheet($nome)->tituloMarca($sec['titulo'])->header($sec['colunas']);
            foreach ($sec['linhas'] as $linha) {
                $xlsx->linha(array_values($linha));
            }
        }

        $xlsx->fechar();
    }
}
