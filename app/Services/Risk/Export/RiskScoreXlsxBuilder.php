<?php

namespace App\Services\Risk\Export;

use App\Support\Reports\ReportTheme;
use App\Support\Reports\XlsxReport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RiskScoreXlsxBuilder
{
    public function download(array $relatorio, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'riskxlsx');
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
        $kpis = $relatorio['kpis'];

        $xlsx->addSheet('Resumo')
            ->larguras(36, 24)
            ->tituloMarca(ReportTheme::brandName().' — '.$relatorio['titulo'])
            ->subtitulo('Gerado em '.$relatorio['gerado_em']->format('d/m/Y H:i'));

        foreach ($relatorio['filtros'] as $rotulo => $valor) {
            $xlsx->linhaKV($rotulo, $valor);
        }

        $xlsx->linhaKV('', '');
        $xlsx->linhaKV('Avaliados', (int) $kpis['avaliados'], XlsxReport::FMT_INT);
        $xlsx->linhaKV('Baixo risco', (int) $kpis['baixo'], XlsxReport::FMT_INT, ReportTheme::OK);
        $xlsx->linhaKV('Médio risco', (int) $kpis['medio'], XlsxReport::FMT_INT, ReportTheme::ALERTA);
        $xlsx->linhaKV('Alto risco', (int) $kpis['alto'], XlsxReport::FMT_INT, '#ea580c');
        $xlsx->linhaKV('Risco crítico', (int) $kpis['critico'], XlsxReport::FMT_INT, ReportTheme::IRREGULAR);
        $xlsx->linhaKV('Risco não conclusivo', (int) $kpis['inconclusivo'], XlsxReport::FMT_INT, ReportTheme::NEUTRO);
        $xlsx->linhaKV('Não consultados', (int) $kpis['nao_consultados'], XlsxReport::FMT_INT);

        $xlsx->addSheet('Score Fiscal')
            ->larguras(16, 20, 42, 30, 16, 18, 8, 13, 22, 12, 14, 14, 12, 12, 20, 18, 18)
            ->tituloMarca(ReportTheme::brandName().' — CNPJs por risco')
            ->subtitulo('0 = melhor regularidade; 100 = pior. Célula vazia = fonte não avaliada.')
            ->header($relatorio['colunas']);

        foreach ($relatorio['registros'] as $registro) {
            $cores = [8 => RiskScoreReportBuilder::corClassificacao($registro['classificacao_codigo'])];
            $formatos = array_fill_keys([7, 9, 10, 11, 12, 13], XlsxReport::FMT_INT);
            $xlsx->linha(RiskScoreReportBuilder::linha($registro), $cores, $formatos);
        }

        $xlsx->fechar();
    }
}
