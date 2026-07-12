<?php

namespace App\Services\Clearance\Export;

use App\Support\Reports\XlsxReport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * XLSX do resultado da busca avulsa (1 documento): espelha a tela de resultado —
 * aba Documento (resumo + partes), Eventos (linha do tempo), Produtos/Componentes
 * e Totais. Fonte única: o array de ClearanceController::formatarResultadoConsultaDfe.
 */
class BuscaAvulsaXlsxBuilder
{
    public function download(array $nota, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'clravulsa');
        if ($tmp === false) {
            throw new \RuntimeException('Falha ao criar arquivo temporário para o XLSX.');
        }

        $this->gerarArquivo($nota, $tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function gerarArquivo(array $nota, string $path): void
    {
        $xlsx = XlsxReport::paraArquivo($path);

        $this->sheetDocumento($xlsx, $nota);
        $this->sheetEventos($xlsx, $nota);
        $this->sheetItens($xlsx, $nota);

        $xlsx->fechar();
    }

    private function sheetDocumento(XlsxReport $xlsx, array $nota): void
    {
        $d = $nota['detalhes'] ?? [];
        $isCte = ($nota['tipo_documento'] ?? '') === 'CTE';

        $xlsx->addSheet('Documento')
            ->larguras(32, 60)
            ->tituloMarca('Clearance — Consulta de documento fiscal', 2)
            ->subtitulo('Consultado na SEFAZ em '.($nota['consultado_em'] ?? '—'))
            ->linhaKV('Chave de acesso', $nota['nfe_id'] ?? '—')
            ->linhaKV('Tipo', ($nota['tipo_documento'] ?? 'NFE').(! empty($nota['modelo']) ? ' (modelo '.$nota['modelo'].')' : ''))
            ->linhaKV('Situação', $nota['situacao'] ?? '—', null, $nota['situacao_hex'] ?? null)
            ->linhaKV('Número / Série', trim(($nota['numero'] ?? '—').' / '.($nota['serie'] ?? '—')))
            ->linhaKV('Emissão', $nota['data_emissao'] ?? '—')
            ->linhaKV('Valor total', $nota['valor_total'] !== null ? (float) $nota['valor_total'] : '—', $nota['valor_total'] !== null ? XlsxReport::FMT_BRL : null)
            ->linhaKV('Natureza da operação', $d['natureza_operacao'] ?? '—')
            ->linhaKV('Abrangência', ($d['consulta_sem_certificado'] ?? false) ? 'Consulta pública (sem certificado — dados parcialmente mascarados pela SEFAZ)' : 'Consulta completa')
            ->linhaKV('Cliente associado', $nota['cliente_nome'] ?? '—')
            ->linhaKV('', '')
            ->linhaKV('Emitente', ($d['emit']['nome'] ?? '—').'  '.($d['emit']['documento'] ?? ''))
            ->linhaKV('Emitente — IE / Local', trim(($d['emit']['ie'] ?? '').'  '.($d['emit']['local'] ?? '')) ?: '—');

        if ($isCte) {
            foreach ($d['partes'] ?? [] as $parte) {
                $xlsx->linhaKV(
                    $parte['papel'].(! empty($parte['identificado_acervo']) ? ' (identificado no acervo)' : ''),
                    trim(($parte['nome'] ?? '—').'  '.($parte['documento'] ?? '').'  '.($parte['local'] ?? ''))
                );
            }
        } else {
            $xlsx->linhaKV(
                'Destinatário'.(! empty($d['dest']['identificado_acervo']) ? ' (identificado no acervo)' : ''),
                trim(($d['dest']['nome'] ?? '—').'  '.($d['dest']['documento'] ?? '').'  '.($d['dest']['local'] ?? ''))
            );
        }

        // Totais da SEFAZ (NF-e) na mesma aba — lista KV já formatada pela tela.
        if (! empty($d['totais'])) {
            $xlsx->linhaKV('', '');
            foreach ($d['totais'] as $t) {
                $xlsx->linhaKV('Total — '.$t['label'], $t['valor']);
            }
        }
    }

    private function sheetEventos(XlsxReport $xlsx, array $nota): void
    {
        $eventos = $nota['detalhes']['eventos_timeline'] ?? [];
        if ($eventos === []) {
            return;
        }

        $xlsx->addSheet('Eventos')
            ->larguras(18, 22, 50, 22)
            ->tituloMarca('Eventos na SEFAZ (linha do tempo)', 4)
            ->header(['Situação', 'Data', 'Evento', 'Protocolo']);

        foreach ($eventos as $ev) {
            $xlsx->linha([
                $ev['label'] ?? '—',
                $ev['data_label'] ?? '—',
                $ev['descricao'] ?? ($ev['label'] ?? '—'),
                $ev['protocolo'] ?? '—',
            ], [0 => $ev['hex'] ?? null]);
        }
    }

    private function sheetItens(XlsxReport $xlsx, array $nota): void
    {
        $d = $nota['detalhes'] ?? [];

        if (($nota['tipo_documento'] ?? '') === 'CTE') {
            if (empty($d['componentes'])) {
                return;
            }

            $xlsx->addSheet('Componentes')
                ->larguras(40, 20)
                ->tituloMarca('Componentes da prestação', 2)
                ->header(['Componente', 'Valor']);

            foreach ($d['componentes'] as $c) {
                $xlsx->linha([$c['nome'] ?? '—', $c['valor'] ?? '—']);
            }

            return;
        }

        if (empty($d['produtos'])) {
            return;
        }

        $xlsx->addSheet('Produtos')
            ->larguras(50, 14, 12, 14, 16)
            ->tituloMarca('Produtos ('.count($d['produtos']).')', 5)
            ->header(['Descrição', 'NCM', 'CFOP', 'Quantidade', 'Valor']);

        foreach ($d['produtos'] as $p) {
            $xlsx->linha([
                $p['descricao'] ?? '—',
                $p['ncm'] ?? '—',
                $p['cfop'] ?? '—',
                $p['quantidade'] ?? '—',
                $p['valor'] ?? '—',
            ]);
        }
    }
}
