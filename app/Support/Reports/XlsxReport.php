<?php

namespace App\Support\Reports;

use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Options\PageOrientation;
use OpenSpout\Writer\XLSX\Options\PageSetup;
use OpenSpout\Writer\XLSX\Options\PaperSize;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Wrapper fino sobre OpenSpout para planilhas de relatório, dirigido pelos
 * tokens de ReportTheme (cores vêm de ReportTheme::statusHex/riscoHex).
 * Estilo: barra de título slate (mesma linguagem do .secao-header dos PDFs),
 * header congelado, célula colorida, formatos numéricos e linha de totais.
 *
 * Valores monetários devem chegar como float + FMT_BRL (número real na
 * célula — o contador soma/filtra/pivota), nunca como string "1.234,56".
 *
 * Uso: XlsxReport::paraArquivo($path)->addSheet(..)->header(..)->linha(..)->fechar();
 */
final class XlsxReport
{
    /** Formatos de célula (códigos Excel; separadores exibidos conforme o locale). */
    public const FMT_BRL = '"R$" #,##0.00';

    public const FMT_PCT = '0.00"%"';

    public const FMT_INT = '#,##0';

    public const FMT_NUM = '#,##0.####';

    /** Slate da barra de título — mesmo tom do .secao-header do layout PDF. */
    private const SLATE_TITULO = '1F2937';

    /** Slate do header de colunas. */
    private const SLATE_HEADER = '374151';

    private Writer $writer;

    private Options $options;

    private bool $primeiraSheet = true;

    /** Linhas já escritas na sheet corrente (para calcular o freeze do header). */
    private int $linhaAtual = 0;

    public static function disponivel(): bool
    {
        return class_exists(Writer::class);
    }

    public static function paraArquivo(string $path): self
    {
        $self = new self;
        $self->options = new Options;
        // Impressão: paisagem A4 ajustada à largura de 1 página. Sem isso, abas
        // largas quebram em várias páginas e a barra de título mesclada aparece
        // "sobrando" vazia à direita/no topo das páginas de continuação.
        $self->options->setPageSetup(new PageSetup(PageOrientation::LANDSCAPE, PaperSize::A4, 0, 1));
        $self->writer = new Writer($self->options);
        $self->writer->openToFile($path);

        return $self;
    }

    public function addSheet(string $nome): self
    {
        $nome = mb_substr($nome, 0, 31); // limite de nome de aba do Excel

        if ($this->primeiraSheet) {
            // openToFile já criou a primeira aba — só renomeia
            $this->primeiraSheet = false;
        } else {
            $this->writer->addNewSheetAndMakeItCurrent();
        }

        $this->writer->getCurrentSheet()->setName($nome);
        $this->linhaAtual = 0;

        return $this;
    }

    /**
     * Larguras das colunas da sheet corrente, em "caracteres" do Excel.
     */
    public function larguras(float ...$larguras): self
    {
        foreach ($larguras as $i => $largura) {
            $this->writer->getCurrentSheet()->setColumnWidth($largura, $i + 1);
        }

        return $this;
    }

    /**
     * Título em UMA célula: texto slate em negrito, SEM preenchimento.
     * Sem merge nem células vazias estilizadas (renderizam diferente entre
     * visualizadores e viram "colunas vazias"); sem fundo escuro porque texto
     * claro que transborda a coluna ficaria branco-sobre-branco. A barra de
     * cor do design fica no header de colunas, que tem a largura exata da
     * tabela. O $span é aceito por compatibilidade, mas ignorado de propósito.
     */
    public function tituloMarca(string $titulo, int $span = 1): self
    {
        $style = (new Style)
            ->setFontBold()->setFontSize(12)
            ->setFontColor(self::SLATE_TITULO);

        $this->writer->addRow(Row::fromValues([$titulo], $style));
        $this->linhaAtual++;

        return $this;
    }

    /** Linha de contexto abaixo do título (período, filtro, gerado em). */
    public function subtitulo(string $texto): self
    {
        $style = (new Style)->setFontItalic()->setFontSize(9)->setFontColor('6B7280');
        $this->writer->addRow(Row::fromValues([$texto], $style));
        $this->linhaAtual++;

        return $this;
    }

    public function header(array $colunas): self
    {
        $style = (new Style)->setFontBold()->setFontColor(Color::WHITE)->setBackgroundColor(self::SLATE_HEADER);
        $this->writer->addRow(Row::fromValues(array_values($colunas), $style));
        $this->linhaAtual++;

        // Congela tudo acima da próxima linha (header e o que vier antes ficam fixos).
        $this->writer->getCurrentSheet()->setSheetView(
            (new SheetView)->setFreezeRow($this->linhaAtual + 1)
        );

        return $this;
    }

    /**
     * @param  array<int,mixed>  $valores
     * @param  array<int,string>  $coresPorIndice  índice 0-based da coluna => hex (#opcional)
     * @param  array<int,string>  $formatosPorIndice  índice 0-based => formato (self::FMT_*)
     */
    public function linha(array $valores, array $coresPorIndice = [], array $formatosPorIndice = []): self
    {
        if ($coresPorIndice === [] && $formatosPorIndice === []) {
            $this->writer->addRow(Row::fromValues(array_values($valores)));
            $this->linhaAtual++;

            return $this;
        }

        $cells = [];
        foreach (array_values($valores) as $i => $v) {
            $style = null;

            $hex = $coresPorIndice[$i] ?? null;
            if ($hex !== null && $hex !== '') {
                $style = (new Style)
                    ->setBackgroundColor(strtoupper(ltrim((string) $hex, '#')))
                    ->setFontColor(Color::WHITE)
                    ->setFontBold();
            }

            $fmt = $formatosPorIndice[$i] ?? null;
            if ($fmt !== null && is_int($v) === false && is_float($v) === false) {
                $fmt = null; // formato numérico só faz sentido em número real
            }
            if ($fmt !== null) {
                $style ??= new Style;
                $style->setFormat($fmt);
            }

            $cells[] = $style !== null ? Cell::fromValue($v, $style) : Cell::fromValue($v);
        }
        $this->writer->addRow(new Row($cells));
        $this->linhaAtual++;

        return $this;
    }

    /**
     * @param  array<int,string>  $formatosPorIndice  índice 0-based => formato (self::FMT_*)
     */
    public function totais(array $valores, array $formatosPorIndice = []): self
    {
        $cells = [];
        foreach (array_values($valores) as $i => $v) {
            $style = (new Style)->setFontBold()->setBackgroundColor('E5E7EB');
            $fmt = $formatosPorIndice[$i] ?? null;
            if ($fmt !== null && (is_int($v) || is_float($v))) {
                $style->setFormat($fmt);
            }
            $cells[] = Cell::fromValue($v, $style);
        }
        $this->writer->addRow(new Row($cells));
        $this->linhaAtual++;

        return $this;
    }

    /**
     * Linha rótulo→valor (abas tipo Resumo). O valor vai alinhado à ESQUERDA,
     * colado no rótulo — número alinhado à direita numa coluna dimensionada
     * pelo maior rótulo deixa um vão no meio que parece coluna vazia.
     */
    public function linhaKV(string $rotulo, mixed $valor, ?string $formato = null, ?string $hex = null): self
    {
        $style = (new Style)->setCellAlignment(CellAlignment::LEFT);
        if ($formato !== null && (is_int($valor) || is_float($valor))) {
            $style->setFormat($formato);
        }
        if ($hex !== null && $hex !== '') {
            $style->setBackgroundColor(strtoupper(ltrim($hex, '#')))
                ->setFontColor(Color::WHITE)
                ->setFontBold();
        }

        $this->writer->addRow(new Row([Cell::fromValue($rotulo), Cell::fromValue($valor, $style)]));
        $this->linhaAtual++;

        return $this;
    }

    /** Linha discreta para seção sem dados no período. */
    public function vazio(string $mensagem = 'Sem dados no período.'): self
    {
        $style = (new Style)->setFontItalic()->setFontColor('9CA3AF');
        $this->writer->addRow(Row::fromValues([$mensagem], $style));
        $this->linhaAtual++;

        return $this;
    }

    public function fechar(): void
    {
        $this->writer->close();
    }
}
