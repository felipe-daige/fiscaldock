<?php

namespace App\Services\Clearance\Export;

use App\Support\Reports\ReportTheme;
use App\Support\Reports\XlsxReport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Workbook do Clearance DF-e espelhando o PDF executivo (mesma fonte:
 * RelatorioExecutivoService::montar()). Segue o modelo de design aprovado no
 * BI (docs/bi/export-planilhas.md): título em célula única, header slate
 * congelado, larguras justas, números reais com formato, badge por severidade.
 */
class ClearanceXlsxBuilder
{
    private const SEV_HEX = [
        'critica' => ReportTheme::IRREGULAR,
        'revisar' => ReportTheme::ALERTA,
        'ok' => ReportTheme::OK,
    ];

    private const SEV_LABEL = ['critica' => 'Crítica', 'revisar' => 'A revisar', 'ok' => 'Conforme'];

    public function download(array $r, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'clrxlsx');
        if ($tmp === false) {
            throw new \RuntimeException('Falha ao criar arquivo temporário para o XLSX.');
        }

        $this->gerarArquivo($r, $tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function gerarArquivo(array $r, string $path): void
    {
        $xlsx = XlsxReport::paraArquivo($path);

        $this->sheetResumo($xlsx, $r);
        $this->sheetConcentracao($xlsx, $r);
        $this->sheetDivergencias($xlsx, $r);
        $this->sheetSemDivergencia($xlsx, $r);

        $xlsx->fechar();
    }

    private function sheetResumo(XlsxReport $xlsx, array $r): void
    {
        $capa = $r['capa'];
        $resumo = $r['resumo'];
        $sev = (string) ($resumo['veredito']['severidade'] ?? 'ok');

        $xlsx->addSheet('Resumo')
            ->larguras(34, 44)
            ->tituloMarca(ReportTheme::brandName().' — Clearance DF-e · Lote #'.($capa['lote_id'] ?? ''))
            ->subtitulo('Período: '.($capa['periodo']['label'] ?? '—').' · Emitido em '.($capa['emitido_em_label'] ?? ''))
            ->header(['Indicador', 'Valor'])
            ->linhaKV('Escritório responsável', (string) $capa['escritorio']['razao_social'])
            ->linhaKV('CNPJ do escritório', (string) $capa['escritorio']['cnpj'])
            ->linhaKV('Acervo auditado', (string) $capa['cliente_auditado']['razao_social'])
            ->linhaKV('Veredito', self::SEV_LABEL[$sev] ?? ucfirst($sev), null, self::SEV_HEX[$sev] ?? ReportTheme::NAO_ENCONTRADA)
            ->linhaKV('Mensagem do veredito', (string) ($resumo['veredito']['mensagem'] ?? '—'))
            ->linhaKV('Documentos auditados', (int) $resumo['total_documentos'], XlsxReport::FMT_INT)
            ->linhaKV('Divergências', (int) $resumo['total_divergencias'], XlsxReport::FMT_INT)
            ->linhaKV('Críticas', (int) $resumo['total_criticas'], XlsxReport::FMT_INT)
            ->linhaKV('A revisar', (int) ($resumo['total_revisar'] ?? 0), XlsxReport::FMT_INT)
            ->linhaKV('Sem divergência', (int) ($resumo['sem_divergencia'] ?? 0), XlsxReport::FMT_INT)
            ->linhaKV('Exposição — crédito/imposto exposto', (float) $r['exposicao']['base'], XlsxReport::FMT_BRL)
            ->linhaKV('Exposição — multa de ofício (75%)', (float) $r['exposicao']['multa'], XlsxReport::FMT_BRL)
            ->linhaKV('Exposição total estimada', (float) $r['exposicao']['total'], XlsxReport::FMT_BRL)
            ->linhaKV('Tolerância de ruído (R$)', (float) $r['metodologia']['tolerancia_absoluta'], XlsxReport::FMT_BRL)
            ->linhaKV('Tolerância de ruído (%)', (float) $r['metodologia']['tolerancia_percentual'], XlsxReport::FMT_PCT)
            ->linhaKV('Hash de integridade (SHA-256)', (string) $r['hash']);
    }

    private function sheetConcentracao(XlsxReport $xlsx, array $r): void
    {
        $xlsx->addSheet('Concentração de Risco')
            ->larguras(36, 20, 12, 15)
            ->tituloMarca('Concentração de risco — principais emitentes')
            ->header(['Emitente', 'CNPJ', 'Divergências', 'Valor exposto']);

        $itens = collect($r['concentracao'] ?? []);
        if ($itens->isEmpty()) {
            $xlsx->vazio('Sem divergências — nenhuma concentração a listar.');

            return;
        }

        foreach ($itens as $emit) {
            $xlsx->linha(
                [$emit['emit_nome'], $emit['emit_cnpj'], (int) $emit['qtd'], (float) $emit['valor_exposto']],
                [],
                [2 => XlsxReport::FMT_INT, 3 => XlsxReport::FMT_BRL]
            );
        }
    }

    private function sheetDivergencias(XlsxReport $xlsx, array $r): void
    {
        $xlsx->addSheet('Divergências')
            ->larguras(10, 14, 46, 32, 20, 14, 14, 14, 13, 14, 40)
            ->tituloMarca('Divergências por documento (Declarado × SEFAZ)')
            ->header(['Severidade', 'Documento', 'Chave de acesso', 'Emitente', 'CNPJ emitente', 'Declarado', 'SEFAZ', 'Δ valor', 'Decadência', 'Exposição', 'Motivos']);

        $docs = collect($r['documentos'] ?? []);
        if ($docs->isEmpty()) {
            $xlsx->vazio('Nenhuma divergência crítica ou a revisar neste lote.');

            return;
        }

        $formatos = [5 => XlsxReport::FMT_BRL, 6 => XlsxReport::FMT_BRL, 7 => XlsxReport::FMT_BRL, 9 => XlsxReport::FMT_BRL];
        foreach ($docs as $doc) {
            $s = (string) ($doc->severidade ?? 'ok');
            $xlsx->linha([
                self::SEV_LABEL[$s] ?? ucfirst($s),
                trim(($doc->tipo_documento ?? 'NFE').' '.($doc->numero ?? '').'/'.($doc->serie ?? '')),
                (string) ($doc->chave_acesso ?? '—'),
                (string) ($doc->emit_nome ?? '—'),
                (string) ($doc->emit_cnpj ?? '—'),
                $doc->declarado_valor !== null ? (float) $doc->declarado_valor : '—',
                $doc->valor_total !== null ? (float) $doc->valor_total : '—',
                $doc->delta_valor !== null ? (float) $doc->delta_valor : '—',
                (string) ($doc->decadencia_label ?? '—'),
                (float) ($doc->exposicao_base ?? 0),
                implode(' · ', (array) ($doc->motivos ?? [])),
            ], [0 => self::SEV_HEX[$s] ?? ReportTheme::NAO_ENCONTRADA], $formatos);
        }
    }

    private function sheetSemDivergencia(XlsxReport $xlsx, array $r): void
    {
        $xlsx->addSheet('Sem Divergência')
            ->larguras(16, 40, 15)
            ->tituloMarca('Anexo — documentos sem divergência (evidência de cobertura)')
            ->header(['Documento', 'Emitente', 'Valor']);

        $docs = collect($r['sem_divergencia'] ?? []);
        if ($docs->isEmpty()) {
            $xlsx->vazio();

            return;
        }

        foreach ($docs as $doc) {
            $xlsx->linha([
                trim(($doc->tipo_documento ?? 'NFE').' '.($doc->numero ?? '').'/'.($doc->serie ?? '')),
                (string) ($doc->emit_nome ?? '—'),
                $doc->valor_total !== null ? (float) $doc->valor_total : '—',
            ], [], [2 => XlsxReport::FMT_BRL]);
        }
    }
}
