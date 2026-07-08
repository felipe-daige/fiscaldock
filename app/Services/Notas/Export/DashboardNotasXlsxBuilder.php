<?php

namespace App\Services\Notas\Export;

use App\Support\Reports\ReportTheme;
use App\Support\Reports\XlsxReport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * XLSX do Raio-X do acervo: aba "Resumo" (KV) + 1 aba por seção do
 * `DashboardNotasReportBuilder`. Mesma fonte do PDF e do CSV/ZIP.
 *
 * Valores monetários vão como float + FMT_BRL (número real na célula — o contador
 * soma/filtra/pivota), nunca como string formatada.
 */
class DashboardNotasXlsxBuilder
{
    /** Nome da aba por seção (limite de 31 chars do Excel já respeitado). */
    private const ABAS = [
        'mix-modelo' => 'Mix por modelo',
        'evolucao-mensal' => 'Evolução mensal',
        'concentracao' => 'Concentração',
        'contrapartes-matriz' => 'Contrapartes',
        'cfop' => 'CFOP',
        'cst-icms' => 'CST ICMS',
        'tributos-mensal' => 'Tributos mensal',
        'alertas' => 'Alertas',
        'compliance-exposicao' => 'Exposição',
    ];

    public function download(array $relatorio, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'dnfxlsx');
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
        $s = $relatorio['saldos'];
        $c = $relatorio['compliance_kpis'];
        $a = $relatorio['resumo_alertas'];

        $xlsx->addSheet('Resumo')
            ->larguras(42, 26)
            ->tituloMarca(ReportTheme::brandName().' — '.$relatorio['titulo'])
            ->subtitulo('Gerado em '.$relatorio['gerado_em']->format('d/m/Y H:i'));

        foreach ($relatorio['filtros'] as $rotulo => $valor) {
            $xlsx->linhaKV($rotulo, $valor);
        }

        $xlsx->linhaKV('', '');
        $xlsx->linhaKV('Notas no recorte', (int) $k['total_notas'], XlsxReport::FMT_INT);
        $xlsx->linhaKV('Aquisições', (float) $k['valor_entradas'], XlsxReport::FMT_BRL);
        $xlsx->linhaKV('Faturamento', (float) $k['valor_saidas'], XlsxReport::FMT_BRL);
        $xlsx->linhaKV('Saldo líquido', (float) $k['saldo'], XlsxReport::FMT_BRL);
        $xlsx->linhaKV('Contrapartes distintas', (int) $k['participantes_unicos'], XlsxReport::FMT_INT);

        $xlsx->linhaKV('', '');
        foreach (['icms' => 'ICMS', 'pis' => 'PIS', 'cofins' => 'COFINS'] as $chave => $label) {
            $xlsx->linhaKV($label.' — débito', (float) $s[$chave]['debito'], XlsxReport::FMT_BRL);
            $xlsx->linhaKV($label.' — crédito', (float) $s[$chave]['credito'], XlsxReport::FMT_BRL);
            $xlsx->linhaKV($label.' — saldo', (float) $s[$chave]['saldo'], XlsxReport::FMT_BRL);
        }

        if ($relatorio['alerta_pis_cofins']) {
            $xlsx->linhaKV('Ressalva', 'Mais de 70% dos itens sem PIS/COFINS — saldos subestimados.', null, ReportTheme::ALERTA);
        }

        $xlsx->linhaKV('', '');
        $xlsx->linhaKV('Alertas — alta', (int) $a['alta'], XlsxReport::FMT_INT, $a['alta'] > 0 ? ReportTheme::IRREGULAR : null);
        $xlsx->linhaKV('Alertas — média', (int) $a['media'], XlsxReport::FMT_INT);
        $xlsx->linhaKV('Alertas — baixa', (int) $a['baixa'], XlsxReport::FMT_INT);

        $xlsx->linhaKV('', '');
        $xlsx->linhaKV('Contrapartes irregulares', (int) $c['irregulares'], XlsxReport::FMT_INT, $c['irregulares'] > 0 ? ReportTheme::IRREGULAR : null);
        $xlsx->linhaKV('Exposição a irregulares', (float) $c['exposicao'], XlsxReport::FMT_BRL);
        $xlsx->linhaKV('Nunca consultadas', (int) $c['nao_consultados'], XlsxReport::FMT_INT);
        $xlsx->linhaKV('Cobertura de consulta (%)', (float) $c['consultados_pct'], XlsxReport::FMT_PCT);
    }

    /** Primeira coluna larga (rótulo), demais médias — evita coluna de razão social truncada. */
    private function larguras(array $colunas): array
    {
        $larguras = array_fill(0, count($colunas), 16.0);
        $larguras[0] = 22.0;

        foreach ($colunas as $i => $col) {
            if (in_array($col, ['Razão social', 'Descrição'], true)) {
                $larguras[$i] = 42.0;
            }
        }

        return $larguras;
    }
}
