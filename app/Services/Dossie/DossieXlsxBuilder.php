<?php

namespace App\Services\Dossie;

use App\Support\Reports\ReportTheme;
use App\Support\Reports\XlsxReport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Workbook do dossiê (cliente OU participante — payloads idênticos de
 * DossieClienteBuilder/DossieParticipanteBuilder, mudando só o dono).
 * Espelha o PDF reports.dossie.* e segue o modelo de design aprovado no BI
 * (docs/bi/export-planilhas.md).
 */
class DossieXlsxBuilder
{
    /**
     * @param  array  $dados  payload do Dossie*Builder::montar()
     * @param  object  $dono  Cliente ou Participante (razao_social, documento, situacao_cadastral, uf)
     */
    public function download(array $dados, object $dono, string $filename): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'dossiexlsx');
        if ($tmp === false) {
            throw new \RuntimeException('Falha ao criar arquivo temporário para o XLSX.');
        }

        $this->gerarArquivo($dados, $dono, $tmp);

        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function gerarArquivo(array $dados, object $dono, string $path): void
    {
        $xlsx = XlsxReport::paraArquivo($path);

        $this->sheetResumo($xlsx, $dados, $dono);
        $docNum = preg_replace('/\D/', '', (string) $dono->documento);
        $this->sheetCertidoes($xlsx, $dados['consulta'] ?? ['tem' => false], strlen($docNum) === 11);
        $this->sheetCompetencia($xlsx, $dados['movimentacao']['por_competencia'] ?? []);
        $this->sheetQtdValor($xlsx, 'CFOP', 'Movimentação por CFOP', 'cfop', $dados['movimentacao']['por_cfop'] ?? []);
        $this->sheetQtdValor($xlsx, 'CST', 'Movimentação por CST (ICMS)', 'cst', $dados['movimentacao']['por_cst'] ?? []);
        $this->sheetTopProdutos($xlsx, $dados['top_produtos'] ?? []);
        $this->sheetTopCfops($xlsx, $dados['top_cfops'] ?? []);

        $xlsx->fechar();
    }

    private function sheetResumo(XlsxReport $xlsx, array $dados, object $dono): void
    {
        $k = $dados['movimentacao']['kpis'];
        $imp = $dados['movimentacao']['impostos'];
        $score = $dados['score'] ?? [];
        $classificacao = $score['classificacao'] ?? null;
        $inconclusivo = $classificacao === 'inconclusivo';
        $naoAvaliado = $classificacao === null
            || $classificacao === 'nao_avaliado'
            || (($score['score_total'] ?? null) === null && ! $inconclusivo);
        $docNum = preg_replace('/\D/', '', (string) $dono->documento);
        $isCpf = strlen($docNum) === 11;
        $scoreRotulo = $isCpf ? 'Risco de crédito (CPF)' : 'Score fiscal';
        $scoreValor = $isCpf || $naoAvaliado
            ? 'Não avaliado'
            : ($inconclusivo ? 'Não conclusivo' : (int) $score['score_total']);
        $classificacaoValor = $isCpf || $naoAvaliado
            ? 'não avaliado'
            : ($inconclusivo ? 'não conclusivo' : (string) $classificacao);

        $xlsx->addSheet('Resumo')
            ->larguras(30, 44)
            ->tituloMarca(ReportTheme::brandName().' — Dossiê Fiscal')
            ->subtitulo(($dono->razao_social ?: '—').' · Gerado em '.($dados['gerado_em'] ?? ''))
            ->header(['Indicador', 'Valor'])
            ->linhaKV('Razão social', (string) ($dono->razao_social ?: '—'))
            ->linhaKV(strlen($docNum) === 11 ? 'CPF' : 'CNPJ', (string) $dono->documento)
            ->linhaKV('Situação cadastral', (string) ($dono->situacao_cadastral ?? '—'))
            ->linhaKV('UF', (string) ($dono->uf ?: '—'))
            ->linhaKV(
                $scoreRotulo,
                $scoreValor,
                XlsxReport::FMT_INT,
                ReportTheme::riscoHex($classificacao)
            )
            ->linhaKV('Classificação de risco', $classificacaoValor)
            ->linhaKV('Total de notas', (int) $k['total_notas'], XlsxReport::FMT_INT)
            ->linhaKV('Valor movimentado', (float) $k['valor_movimentado'], XlsxReport::FMT_BRL)
            ->linhaKV('Entradas — quantidade', (int) $k['entradas_qtd'], XlsxReport::FMT_INT)
            ->linhaKV('Entradas — valor', (float) $k['entradas_valor'], XlsxReport::FMT_BRL)
            ->linhaKV('Saídas — quantidade', (int) $k['saidas_qtd'], XlsxReport::FMT_INT)
            ->linhaKV('Saídas — valor', (float) $k['saidas_valor'], XlsxReport::FMT_BRL)
            ->linhaKV('Período movimentado', ($k['periodo_inicio'] ?? '—').' a '.($k['periodo_fim'] ?? '—'))
            ->linhaKV('ICMS (EFD)', (float) $imp['icms'], XlsxReport::FMT_BRL)
            ->linhaKV('PIS (EFD)', (float) $imp['pis'], XlsxReport::FMT_BRL)
            ->linhaKV('COFINS (EFD)', (float) $imp['cofins'], XlsxReport::FMT_BRL)
            ->linhaKV('Alíquota ICMS média', (float) $imp['aliquota_icms_media'], XlsxReport::FMT_PCT);
    }

    /** Certidões/fontes da última consulta — mesma tabularização do XLSX da consulta. */
    private function sheetCertidoes(XlsxReport $xlsx, array $consulta, bool $isCpf = false): void
    {
        $xlsx->addSheet('Certidões')
            ->larguras(24, 16, 70)
            ->tituloMarca('Regularidade — certidões e fontes consultadas')
            ->header(['Fonte', 'Situação', 'Detalhe']);

        if (empty($consulta['tem']) || empty($consulta['blocos'])) {
            $xlsx->vazio($isCpf
                ? 'Certidões de CNPJ não se aplicam a CPF; risco de crédito ainda sem fonte integrada.'
                : 'Sem consulta de certidões para este participante.');

            return;
        }

        foreach ($consulta['blocos'] as $bloco) {
            $detalhe = collect($bloco['itens'] ?? [])
                ->map(fn ($i) => ($i['label'] ?? '').': '.($i['valor'] ?? ''))
                ->implode(' · ');
            $hex = $bloco['badge']['hex'] ?? null;
            $xlsx->linha(
                [$bloco['titulo'] ?? '—', $bloco['badge']['label'] ?? '—', $detalhe],
                $hex ? [1 => $hex] : []
            );
        }
    }

    private function sheetCompetencia(XlsxReport $xlsx, array $comp): void
    {
        $xlsx->addSheet('Por Competência')
            ->larguras(12, 15, 15)
            ->tituloMarca('Movimentação por competência (EFD)')
            ->header(['Competência', 'Entradas', 'Saídas']);

        if ($comp === []) {
            $xlsx->vazio('Sem movimentação EFD registrada.');

            return;
        }

        $fmt = [1 => XlsxReport::FMT_BRL, 2 => XlsxReport::FMT_BRL];
        $totEntrada = 0.0;
        $totSaida = 0.0;
        foreach ($comp as $c) {
            $xlsx->linha([$c['competencia'], (float) $c['entrada'], (float) $c['saida']], [], $fmt);
            $totEntrada += (float) $c['entrada'];
            $totSaida += (float) $c['saida'];
        }
        $xlsx->totais(['Total', $totEntrada, $totSaida], $fmt);
    }

    /** Aba genérica [chave, qtd, valor] para por_cfop / por_cst. */
    private function sheetQtdValor(XlsxReport $xlsx, string $aba, string $titulo, string $campo, array $linhas): void
    {
        $xlsx->addSheet($aba)
            ->larguras(10, 10, 16)
            ->tituloMarca($titulo)
            ->header([$aba, 'Qtd', 'Valor']);

        if ($linhas === []) {
            $xlsx->vazio('Sem itens EFD.');

            return;
        }

        foreach ($linhas as $l) {
            $xlsx->linha(
                [(string) ($l[$campo] ?: '—'), (int) $l['qtd'], (float) $l['valor']],
                [],
                [1 => XlsxReport::FMT_INT, 2 => XlsxReport::FMT_BRL]
            );
        }
    }

    private function sheetTopProdutos(XlsxReport $xlsx, array $produtos): void
    {
        $xlsx->addSheet('Top Produtos')
            ->larguras(12, 48, 16, 10)
            ->tituloMarca('Principais produtos')
            ->header(['Código', 'Descrição', 'Valor', 'Qtd']);

        if ($produtos === []) {
            $xlsx->vazio('Sem produtos no acervo.');

            return;
        }

        foreach ($produtos as $p) {
            $xlsx->linha(
                [(string) ($p['cod_item'] ?? '—'), (string) ($p['descricao'] ?? '—'), (float) ($p['valor'] ?? 0), (float) ($p['qtd'] ?? 0)],
                [],
                [2 => XlsxReport::FMT_BRL, 3 => XlsxReport::FMT_NUM]
            );
        }
    }

    private function sheetTopCfops(XlsxReport $xlsx, array $cfops): void
    {
        $xlsx->addSheet('Top CFOPs')
            ->larguras(8, 48, 16, 10)
            ->tituloMarca('CFOPs detalhados')
            ->header(['CFOP', 'Descrição', 'Valor', 'Qtd']);

        if ($cfops === []) {
            $xlsx->vazio('Sem CFOPs no acervo.');

            return;
        }

        foreach ($cfops as $c) {
            $xlsx->linha(
                [(string) ($c['cfop'] ?? '—'), (string) ($c['descricao'] ?? ''), (float) ($c['valor'] ?? 0), (int) ($c['qtd'] ?? 0)],
                [],
                [2 => XlsxReport::FMT_BRL, 3 => XlsxReport::FMT_INT]
            );
        }
    }
}
