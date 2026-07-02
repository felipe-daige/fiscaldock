<?php

namespace App\Services\Bi\Export;

use App\Services\BiExportService;
use App\Support\CsvExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * ZIP com 1 CSV por seção do relatório completo do BI (mesma fonte do PDF
 * executivo e do XLSX: relatorioCompleto). CSV é 1 tabela — o relatório tem
 * N seções, então empacota. Prefixo numérico preserva a ordem do relatório.
 * Valores mantêm o padrão canônico pt-BR do CsvExport (BOM + ";").
 */
class BiCsvZipBuilder
{
    /** Slug do arquivo por seção (score-carteira entra no resumo, não vira CSV). */
    private const SLUGS = [
        'faturamento' => 'faturamento',
        'tributos' => 'tributos',
        'apuracao-notas' => 'declarado-x-computado',
        'contrapartes' => 'contrapartes',
        'dossie-participantes' => 'dossie-participantes',
        'cfop' => 'cfop',
        'top-notas' => 'top-notas',
        'catalogo' => 'catalogo',
        'uf' => 'uf',
        'devolucoes' => 'devolucoes',
        'riscos-notas' => 'riscos-notas',
        'riscos-fornecedores' => 'riscos-fornecedores',
    ];

    public function __construct(protected BiExportService $biExport) {}

    public function download(array $relatorio, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'bicsvzip');
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
        $add('cobertura', ['Mês', 'EFD ICMS/IPI', 'EFD PIS/COFINS'], $this->linhasCobertura($relatorio));

        foreach ($relatorio['ordem_secoes'] as $chave) {
            $slug = self::SLUGS[$chave] ?? null;
            $sec = $relatorio['secoes'][$chave] ?? null;
            if ($slug === null || $sec === null) {
                continue;
            }

            if ($chave === 'contrapartes') {
                $tab = $this->biExport->contrapartesTabela($sec);
                $add($slug, $tab['colunas'], $tab['linhas']);

                continue;
            }

            $add($slug, $sec['colunas'] ?? [], $sec['linhas'] ?? []);
        }

        $zip->close();
    }

    /**
     * Coluna "Valor" 100% numérica (alinhamento consistente no Excel):
     * unidade vai no rótulo (R$/%), contexto textual (modo/período) fica numa
     * linha própria de célula única, e nada de "R$ " prefixando número.
     */
    private function linhasResumo(array $relatorio): array
    {
        $k = $relatorio['kpis'];
        $p = $relatorio['periodo'];
        $modo = ($relatorio['modo'] ?? 'portfolio') === 'cliente' ? 'Cliente #'.$p['cliente_id'] : 'Carteira';

        $linhas = [
            [$modo.' · Período: '.($p['inicio'] ?? 'Todos').' a '.($p['fim'] ?? 'Todos')],
            ['Faturamento (R$)', $k['faturamento']],
            ['Aquisições (R$)', $k['aquisicoes']],
            ['Tributos — débito s/ saída (R$)', $k['tributos']],
            ['A recolher — apurado (R$)', $relatorio['a_recolher_brl'] ?? '0,00'],
            ['Saldo líquido (R$)', $k['saldo_liquido']],
            ['Total de notas', $k['total_notas']],
            ['Alíquota média (%)', $k['aliquota_media']],
        ];

        $sc = $relatorio['score_carteira'] ?? null;
        if ($sc) {
            $linhas[] = ['Score da carteira — regular (%)', $sc['percentual_regular']];
            $linhas[] = ['Score da carteira — irregulares', $sc['irregulares']];
            $linhas[] = ['Score da carteira — participantes ativos', $sc['participantes_ativos']];
            $linhas[] = ['Score da carteira — em risco (%)', $sc['percentual_em_risco']];
            $linhas[] = ['Score da carteira — valor em risco (R$)', $sc['valor_total_em_risco_brl']];
        }

        return $linhas;
    }

    /**
     * Normaliza células para o Excel pt-BR inferir tipo consistente por coluna:
     * float → vírgula decimal (senão "18.25" viraria texto alinhado à esquerda
     * no meio de números), "—" → vazio (senão texto solto em coluna numérica).
     */
    private function sanitizar(array $linhas): array
    {
        return array_map(
            fn (array $linha) => array_map(function ($v) {
                if ($v === '—') {
                    return '';
                }
                if (is_float($v)) {
                    return str_replace('.', ',', (string) $v);
                }

                return $v;
            }, $linha),
            $linhas
        );
    }

    private function linhasCobertura(array $relatorio): array
    {
        $cob = $relatorio['cobertura'] ?? [];
        $semFiscal = collect($cob['meses_sem_fiscal'] ?? [])->pluck('mes')->all();
        $semContrib = collect($cob['meses_sem_contrib'] ?? [])->pluck('mes')->all();
        $gap = collect($cob['meses_gap_total'] ?? [])->pluck('mes')->all();

        return collect(array_merge($semFiscal, $semContrib, $gap))
            ->unique()->sort()->values()
            ->map(fn ($mes) => [
                $mes,
                ! in_array($mes, $semFiscal, true) && ! in_array($mes, $gap, true) ? 'Sim' : 'FALTA',
                ! in_array($mes, $semContrib, true) && ! in_array($mes, $gap, true) ? 'Sim' : 'FALTA',
            ])->all();
    }
}
