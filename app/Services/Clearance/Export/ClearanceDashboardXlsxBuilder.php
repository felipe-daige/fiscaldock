<?php

namespace App\Services\Clearance\Export;

use App\Support\Reports\ReportTheme;
use App\Support\Reports\XlsxReport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * XLSX do Panorama de Clearance: aba "Resumo" (KV) + 1 aba por seção do
 * `ClearanceDashboardReportBuilder`. Mesma fonte do PDF e do CSV/ZIP.
 *
 * Valores monetários vão como float + FMT_BRL (número real na célula), nunca string.
 */
class ClearanceDashboardXlsxBuilder
{
    private const ABAS = [
        'status-valor' => 'Status × valor',
        'exposicao-bloqueante' => 'Exposição bloqueante',
        'cobertura-cliente' => 'Cobertura por cliente',
    ];

    public function download(array $relatorio, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'clrxlsx');
        if ($tmp === false) {
            throw new \RuntimeException('Falha ao criar arquivo temporário para o XLSX.');
        }

        $this->gerarArquivo($relatorio, $tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function gerarArquivo(array $relatorio, string $path): void
    {
        $xlsx = XlsxReport::paraArquivo($path);

        $this->abaResumo($xlsx, $relatorio);

        foreach ($relatorio['ordem_secoes'] as $chave) {
            $sec = $relatorio['secoes'][$chave] ?? null;
            if ($sec === null) {
                continue;
            }

            $xlsx->addSheet(self::ABAS[$chave] ?? $chave)
                ->larguras(...$this->larguras($sec['colunas']))
                ->tituloMarca(ReportTheme::brandName().' — '.$sec['titulo'])
                ->subtitulo($sec['nota'] ?? '');

            if (empty($sec['linhas'])) {
                $xlsx->vazio('Sem dados no acervo.');

                continue;
            }

            $xlsx->header($sec['colunas']);

            $formatos = $sec['formatos'] ?? [];
            $cores = $sec['cores'] ?? [];

            foreach ($sec['linhas'] as $i => $linha) {
                $xlsx->linha($linha, $cores[$i] ?? [], $formatos);
            }

            if (! empty($sec['total'])) {
                $xlsx->totais($sec['total'], $formatos);
            }
        }

        $xlsx->fechar();
    }

    private function abaResumo(XlsxReport $xlsx, array $relatorio): void
    {
        $r = $relatorio['resumo'];
        $b = $relatorio['backlog'];

        $xlsx->addSheet('Resumo')
            ->larguras(44, 26)
            ->tituloMarca(ReportTheme::brandName().' — '.$relatorio['titulo'])
            ->subtitulo('Gerado em '.$relatorio['gerado_em']->format('d/m/Y H:i'));

        foreach ($relatorio['filtros'] as $rotulo => $valor) {
            $xlsx->linhaKV($rotulo, $valor);
        }

        $xlsx->linhaKV('', '');
        $xlsx->linhaKV('Notas no acervo', (int) $r['total_notas'], XlsxReport::FMT_INT);
        $xlsx->linhaKV('Verificadas na Receita', (int) $r['verificadas'], XlsxReport::FMT_INT);
        $xlsx->linhaKV('Pendentes de verificação', (int) $r['pendentes'], XlsxReport::FMT_INT);
        $xlsx->linhaKV('Cobertura de verificação (%)', (float) $r['cobertura_pct'], XlsxReport::FMT_PCT);
        $xlsx->linhaKV('Valor total movimentado', (float) $r['valor_total'], XlsxReport::FMT_BRL);
        $xlsx->linhaKV('Valor pendente de verificação', (float) $r['valor_pendente'], XlsxReport::FMT_BRL);

        $xlsx->linhaKV('', '');
        $xlsx->linhaKV('Notas bloqueantes', (int) $r['notas_bloqueantes'], XlsxReport::FMT_INT, $r['notas_bloqueantes'] > 0 ? ReportTheme::IRREGULAR : null);
        $xlsx->linhaKV('Exposição bloqueante (R$)', (float) $r['valor_bloqueante'], XlsxReport::FMT_BRL, $r['valor_bloqueante'] > 0 ? ReportTheme::IRREGULAR : null);

        $xlsx->linhaKV('', '');
        $xlsx->linhaKV('Backlog — notas a verificar', (int) $b['notas'], XlsxReport::FMT_INT);
        $xlsx->linhaKV('Backlog — custo estimado (R$)', (float) $b['custo_reais'], XlsxReport::FMT_BRL);
    }

    private function larguras(array $colunas): array
    {
        $larguras = array_fill(0, count($colunas), 16.0);
        $larguras[0] = 22.0;

        foreach ($colunas as $i => $col) {
            if (in_array($col, ['Cliente', 'Contraparte'], true)) {
                $larguras[$i] = 38.0;
            }
            if ($col === 'Chave de acesso') {
                $larguras[$i] = 46.0;
            }
        }

        return $larguras;
    }
}
