<?php

namespace App\Services\Catalogo\Export;

use App\Support\Reports\ReportTheme;
use App\Support\Reports\XlsxReport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * XLSX do Catálogo de Produtos: aba "Resumo" (KV) + 1 aba por seção do
 * `CatalogoReportBuilder`. Mesma fonte do PDF e do CSV/ZIP. Valores como float + FMT_*.
 */
class CatalogoXlsxBuilder
{
    private const ABAS = [
        'itens' => 'Itens',
        'cfops' => 'CFOPs',
        'csts' => 'CSTs ICMS',
        'drift' => 'Drift de cadastro',
    ];

    public function download(array $relatorio, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'catxlsx');
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
                $xlsx->vazio('Sem dados no recorte.');

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
        $k = $relatorio['kpis'];
        $drift = $relatorio['drift'];

        $xlsx->addSheet('Resumo')
            ->larguras(40, 24)
            ->tituloMarca(ReportTheme::brandName().' — '.$relatorio['titulo'])
            ->subtitulo('Gerado em '.$relatorio['gerado_em']->format('d/m/Y H:i'));

        foreach ($relatorio['filtros'] as $rotulo => $valor) {
            $xlsx->linhaKV($rotulo, $valor);
        }

        $xlsx->linhaKV('', '');
        $xlsx->linhaKV('Total de produtos', (int) $k['total_produtos'], XlsxReport::FMT_INT);
        $xlsx->linhaKV('Com movimentação', (int) $k['com_movimentacao'], XlsxReport::FMT_INT);
        $xlsx->linhaKV('Sem movimentação', (int) $k['sem_movimentacao'], XlsxReport::FMT_INT);
        $xlsx->linhaKV('Valor movimentado', (float) $k['valor_movimentado'], XlsxReport::FMT_BRL);
        $xlsx->linhaKV('Alíquota divergente', (int) $k['aliq_divergente'], XlsxReport::FMT_INT, $k['aliq_divergente'] > 0 ? ReportTheme::ALERTA : null);
        $xlsx->linhaKV('NCM faltando', (int) $k['ncm_faltando'], XlsxReport::FMT_INT, $k['ncm_faltando'] > 0 ? ReportTheme::ALERTA : null);

        $xlsx->linhaKV('', '');
        $xlsx->linhaKV('Mudanças de cadastro (drift)', (int) ($drift['total'] ?? 0), XlsxReport::FMT_INT);
        $xlsx->linhaKV('Itens afetados por drift', (int) ($drift['itens_afetados'] ?? 0), XlsxReport::FMT_INT);
    }

    /** Descrição larga; demais colunas médias. */
    private function larguras(array $colunas): array
    {
        $larguras = array_fill(0, count($colunas), 16.0);
        foreach ($colunas as $i => $col) {
            if ($col === 'Descrição') {
                $larguras[$i] = 48.0;
            } elseif ($col === 'Código') {
                $larguras[$i] = 22.0;
            }
        }

        return $larguras;
    }
}
