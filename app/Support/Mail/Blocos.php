<?php

namespace App\Support\Mail;

use Illuminate\Support\HtmlString;

/**
 * Blocos de conteúdo dos e-mails transacionais.
 *
 * Por que HTML cru e não Blade/Markdown: `MailMessage::line()` é a API que todas as
 * notifications usam, e a view raiz (`vendor/notifications/email.blade.php`) imprime
 * `HtmlString` sem escapar. Assim dá pra ter hierarquia visual de verdade (hero, passos,
 * placar, chip) sem trocar cada notification por uma view customizada.
 *
 * **Estilo SEMPRE inline** (`style="..."`), nunca classe: cliente de e-mail descarta
 * `<style>` (Gmail corta, Outlook reescreve). Mesma regra dura do design system: cor de
 * fundo é hex literal.
 *
 * **Tudo em `<table>`**: Outlook (motor Word) ignora `div` com flex/grid. Layout de
 * e-mail é tabela, não é escolha estética.
 */
class Blocos
{
    // Paleta. Navy da marca + um acento DOURADO (brass) que aquece o azul frio e dá
    // identidade — usado com parcimônia (kicker, fios, marcas), nunca em bloco grande.
    public const NAVY = '#173e6b';           // navy mais fundo/saturado que o antigo (#1f4679) — dá punch

    public const NAVY_TOPO = '#102c4d';      // topo do header, degradê "chapado" via 2 faixas

    public const NAVY_CLARO = '#9db8d6';

    public const OURO = '#d19a2e';           // acento brass

    public const OURO_CLARO = '#e7c473';

    public const TEXTO = '#0f172a';          // quase-preto (mais contraste que o slate antigo)

    public const TEXTO_SUAVE = '#475569';

    public const BORDA = '#d3d9e2';

    public const FUNDO_SUAVE = '#f5f7fa';

    public const VERMELHO = '#c02626';

    public const AMBAR = '#b45309';

    public const VERDE = '#0a8060';

    /** @var array<string, array{0: string, 1: string}> severidade => [label, cor] */
    private const SEVERIDADES = [
        'alta' => ['Severidade alta', self::VERMELHO],
        'media' => ['Severidade média', self::AMBAR],
        'baixa' => ['Severidade baixa', '#6b7280'],
    ];

    /**
     * CID da logo por mensagem — o build renderiza a view mais de uma vez, e cada
     * `embed()` cria um anexo; sem memo a logo ia 2× no MIME.
     *
     * `WeakMap` keyed pelo próprio `$message` (não por `spl_object_id`): no worker
     * (`queue:work`, processo longo) os ids são RECICLADOS após o GC do objeto
     * anterior — um array keyed-por-id devolveria o CID de uma mensagem já coletada
     * para outra que reusou o id, quebrando a logo de forma intermitente. O WeakMap
     * só tem entrada enquanto o objeto vive; some com ele, sem risco de colisão.
     */
    private static ?\WeakMap $cidPorMensagem = null;

    /**
     * `src` da logo. Num envio real, `$message` existe e a imagem vai por CID (anexo
     * inline) — nada de host externo, nada de asset que dependa de bind-mount
     * (`public/binary_files/` vem da imagem Docker, não é montado).
     *
     * Memoizado por mensagem: o Mailer renderiza a view mais de uma vez ao montar o
     * e-mail, e cada `embed()` cria um anexo — sem isso a logo ia 2× no MIME.
     */
    public static function logoSrc(mixed $message = null): string
    {
        $arquivo = resource_path('mail-assets/fiscaldock-logo.png');

        if ($message === null || ! method_exists($message, 'embed') || ! is_file($arquivo)) {
            return asset('binary_files/logo/Logo FiscalDock.png');
        }

        self::$cidPorMensagem ??= new \WeakMap;

        return self::$cidPorMensagem[$message] ??= $message->embed($arquivo);
    }

    /**
     * Micro-label acima do título ("kicker") — contexto instantâneo, antes da 1ª frase.
     * Renderizado pela view raiz a partir de `viewData['etiqueta']`; use `comEtiqueta()`
     * pra setar, não chame isto direto na notification (senão sai DEPOIS do título).
     */
    // Default NAVY casa com comEtiqueta() e o fallback do email.blade — o kicker sai da
    // MESMA cor por qualquer caminho de chamada (antes divergia: aqui OURO, lá NAVY).
    public static function etiqueta(string $texto, string $cor = self::NAVY): HtmlString
    {
        return new HtmlString(
            '<table cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 0 14px 0;"><tr>'
            .'<td style="width: 22px; padding-right: 9px; vertical-align: middle;">'
            .'<div style="height: 2px; width: 22px; '.self::bgSolido($cor).' font-size: 0; line-height: 2px;">&nbsp;</div>'
            .'</td>'
            .'<td style="vertical-align: middle; font-size: 11px; font-weight: 700; letter-spacing: 0.16em; '
            .'text-transform: uppercase; color: '.$cor.';">'.e($texto).'</td>'
            .'</tr></table>'
        );
    }

    /**
     * Põe a etiqueta acima do título. Vai por `viewData` porque a ordem de render é
     * fixa (etiqueta → título → linhas): uma `line()` nunca conseguiria subir do título.
     */
    public static function comEtiqueta(mixed $mail, string $texto, string $cor = self::NAVY): mixed
    {
        $mail->viewData['etiqueta'] = $texto;
        $mail->viewData['etiquetaCor'] = $cor;

        return $mail;
    }

    /** Chip sólido de severidade do alerta. */
    public static function chipSeveridade(string $severidade): HtmlString
    {
        [$label, $cor] = self::SEVERIDADES[$severidade] ?? self::SEVERIDADES['baixa'];

        return new HtmlString(
            '<table cellpadding="0" cellspacing="0" role="presentation" style="margin: 0 0 16px 0;"><tr>'
            .'<td style="padding: 6px 13px; '.self::bgSolido($cor).' border-radius: 3px; '
            .'font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; '
            .'color: #ffffff;">'.e($label).'</td>'
            .'</tr></table>'
        );
    }

    /** Por fundo do hero: [tinta do rótulo, cor do fio de topo]. */
    private const HERO_ESTILO = [
        self::NAVY => [self::OURO_CLARO, self::OURO],
        self::VERMELHO => ['#f0b9b9', '#e88b8b'],
        self::AMBAR => ['#f0cf9f', self::OURO_CLARO],
        self::VERDE => ['#93d4c1', '#4fb89b'],
    ];

    /**
     * Cartão-herói: um número que É a mensagem do e-mail (saldo liberado, valor cobrado,
     * exposição a glosa). Fio de topo colorido dá acabamento; muito mais forte que a
     * mesma cifra correndo no meio do texto.
     */
    public static function hero(string $valor, string $rotulo, ?string $nota = null, string $fundo = self::NAVY): HtmlString
    {
        [$tinta, $fio] = self::HERO_ESTILO[$fundo] ?? self::HERO_ESTILO[self::NAVY];

        $notaHtml = $nota
            ? '<div style="margin-top: 11px; font-size: 13px; color: '.$tinta.';">'.$nota.'</div>'
            : '';

        return new HtmlString(
            '<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 26px 0;">'
            .'<tr><td style="height: 4px; line-height: 4px; font-size: 0; '.self::bgSolido($fio).' '
            .'border-radius: 5px 5px 0 0;">&nbsp;</td></tr>'
            .'<tr><td align="center" style="padding: 30px 20px; '.self::bgSolido($fundo).' border-radius: 0 0 5px 5px;">'
            .'<div style="font-size: 11px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: '.$tinta.';">'.e($rotulo).'</div>'
            .'<div style="margin-top: 10px; font-size: 38px; font-weight: 700; letter-spacing: -0.02em; color: #ffffff; line-height: 1.05;">'.e($valor).'</div>'
            .$notaHtml
            .'</td></tr></table>'
        );
    }

    /**
     * Abertura executiva do resumo semanal. Reúne status, período, volume de alertas
     * e exposição numa única peça; evita que a informação mais importante fique
     * espalhada em título, parágrafo e cartões com escalas visuais diferentes.
     */
    public static function panoramaSemanal(
        int $totalAlertas,
        int $altas,
        int $medias,
        float $exposicao,
        string $periodo
    ): HtmlString {
        if ($altas > 0) {
            $fundo = self::VERMELHO;
            $status = 'Ação prioritária';
            $descricao = $altas.' '.($altas === 1 ? 'ocorrência exige' : 'ocorrências exigem')
                .' revisão imediata.';
        } elseif ($medias > 0) {
            $fundo = self::AMBAR;
            $status = 'Pontos de atenção';
            $descricao = $medias.' '.($medias === 1 ? 'ocorrência merece' : 'ocorrências merecem')
                .' acompanhamento.';
        } elseif ($totalAlertas > 0) {
            $fundo = self::NAVY;
            $status = 'Acompanhamento';
            $descricao = 'Há somente ocorrências informativas no período.';
        } else {
            $fundo = self::VERDE;
            $status = 'Semana em ordem';
            $descricao = 'Nenhuma nova ocorrência foi detectada na sua carteira.';
        }

        [$tinta, $fio] = self::HERO_ESTILO[$fundo] ?? self::HERO_ESTILO[self::NAVY];
        $rotuloAlertas = $totalAlertas === 1 ? 'alerta novo' : 'alertas novos';

        $exposicaoHtml = '';
        if ($exposicao > 0) {
            $exposicaoHtml = '<tr><td style="padding: 18px 26px; border-top: 1px solid '.$fio.'; '
                .self::bgSolido($fundo).'">'
                .'<table width="100%" cellpadding="0" cellspacing="0" role="presentation"><tr>'
                .'<td valign="middle" style="font-size: 10px; font-weight: 700; letter-spacing: 0.12em; '
                .'text-transform: uppercase; color: '.$tinta.'; line-height: 1.4;">Exposição mapeada</td>'
                .'<td align="right" valign="middle" style="font-size: 18px; font-weight: 700; white-space: nowrap; '
                .'letter-spacing: -0.01em; color: #ffffff; line-height: 1.2;">'.e(self::brl($exposicao)).'</td>'
                .'</tr></table>'
                .'<div style="margin-top: 7px; font-size: 12px; color: '.$tinta.'; line-height: 1.5;">'
                .'Valor associado às prioridades exibidas neste resumo.</div>'
                .'</td></tr>';
        }

        return new HtmlString(
            '<table width="100%" cellpadding="0" cellspacing="0" role="presentation" '
            .'style="margin: 22px 0 30px 0; border-collapse: separate;">'
            .'<tr><td style="height: 4px; line-height: 4px; font-size: 0; '.self::bgSolido($fio).' '
            .'border-radius: 6px 6px 0 0;">&nbsp;</td></tr>'
            .'<tr><td style="padding: 24px 26px 22px 26px; '.self::bgSolido($fundo).'">'
            .'<table width="100%" cellpadding="0" cellspacing="0" role="presentation"><tr>'
            .'<td valign="middle"><span style="display: inline-block; padding: 5px 9px; border: 1px solid '.$fio.'; '
            .'border-radius: 3px; font-size: 9px; font-weight: 700; letter-spacing: 0.13em; '
            .'text-transform: uppercase; color: #ffffff;">'.e($status).'</span></td>'
            .'<td align="right" valign="middle" style="font-size: 11px; font-weight: 600; color: '.$tinta.';">'
            .e($periodo).'</td>'
            .'</tr></table>'
            .'<div style="margin-top: 20px; color: #ffffff; line-height: 1;">'
            .'<span style="font-size: 40px; font-weight: 700; letter-spacing: -0.03em;">'.$totalAlertas.'</span>'
            .'<span style="font-size: 18px; font-weight: 600;"> '.e($rotuloAlertas).'</span>'
            .'</div>'
            .'<div style="margin-top: 10px; font-size: 14px; color: '.$tinta.'; line-height: 1.55;">'
            .e($descricao).'</div>'
            .'</td></tr>'
            .$exposicaoHtml
            .'<tr><td style="height: 1px; line-height: 1px; font-size: 0; '.self::bgSolido($fundo).' '
            .'border-radius: 0 0 6px 6px;">&nbsp;</td></tr>'
            .'</table>'
        );
    }

    /** Cabeçalho numerado de seção, com escala tipográfica única no resumo. */
    public static function tituloSecao(string $numero, string $titulo, string $descricao): HtmlString
    {
        return new HtmlString(
            '<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 30px 0 14px 0;">'
            .'<tr>'
            .'<td width="38" valign="top" style="padding-right: 12px;">'
            .'<table cellpadding="0" cellspacing="0" role="presentation"><tr>'
            .'<td width="30" height="30" align="center" valign="middle" style="width: 30px; height: 30px; '
            .self::bgSolido(self::NAVY).' border-radius: 15px; color: #ffffff; font-size: 11px; '
            .'font-weight: 700; letter-spacing: 0.04em; line-height: 30px;">'.e($numero).'</td>'
            .'</tr></table>'
            .'</td>'
            .'<td valign="top">'
            .'<div style="font-size: 17px; font-weight: 700; letter-spacing: -0.01em; color: '.self::TEXTO.'; '
            .'line-height: 1.3;">'.e($titulo).'</div>'
            .'<div style="margin-top: 4px; font-size: 13px; color: '.self::TEXTO_SUAVE.'; line-height: 1.55;">'
            .e($descricao).'</div>'
            .'</td>'
            .'</tr></table>'
        );
    }

    /**
     * Distribuição dos alertas com três células idênticas. Os microtextos traduzem
     * severidade em decisão, em vez de deixar o usuário interpretar apenas cores.
     *
     * @param  array{alta:int, media:int, baixa:int}  $severidades
     */
    public static function severidades(array $severidades): HtmlString
    {
        $itens = [
            ['alta', 'Alta', 'Agir agora', self::VERMELHO],
            ['media', 'Média', 'Acompanhar', self::AMBAR],
            ['baixa', 'Baixa', 'Informativo', '#6b7280'],
        ];
        $celulas = '';

        foreach ($itens as $i => [$chave, $rotulo, $orientacao, $cor]) {
            $borda = $i === 0 ? '' : 'border-left: 1px solid '.self::BORDA.';';
            $celulas .= '<td width="33.33%" align="center" valign="top" style="padding: 0; '.$borda.'">'
                .'<div style="height: 4px; line-height: 4px; font-size: 0; '.self::bgSolido($cor).'">&nbsp;</div>'
                .'<div style="padding: 16px 7px 15px 7px;">'
                .'<div style="font-size: 27px; font-weight: 700; color: '.$cor.'; line-height: 1;">'
                .(int) ($severidades[$chave] ?? 0).'</div>'
                .'<div style="margin-top: 7px; font-size: 11px; font-weight: 700; color: '.self::TEXTO.'; '
                .'line-height: 1.3;">'.e($rotulo).'</div>'
                .'<div style="margin-top: 3px; font-size: 10px; color: '.self::TEXTO_SUAVE.'; line-height: 1.3;">'
                .e($orientacao).'</div>'
                .'</div></td>';
        }

        return new HtmlString(
            '<table width="100%" cellpadding="0" cellspacing="0" role="presentation" '
            .'style="margin: 16px 0 24px 0; border: 1px solid '.self::BORDA.'; border-radius: 5px; '
            .'border-collapse: separate; overflow: hidden; background-color: #ffffff;"><tr>'.$celulas.'</tr></table>'
        );
    }

    /** Aviso editorial com título, explicação e tom semântico. */
    public static function aviso(string $titulo, string $texto, string $tom = 'info'): HtmlString
    {
        $estilos = [
            'critico' => [self::VERMELHO, '#fef2f2', '#fecaca', '!'],
            'atencao' => [self::AMBAR, '#fffbeb', '#fde68a', '!'],
            'sucesso' => [self::VERDE, '#ecfdf5', '#a7f3d0', '✓'],
            'info' => [self::NAVY, '#f1f5f9', '#cbd5e1', 'i'],
        ];
        [$cor, $fundo, $borda, $icone] = $estilos[$tom] ?? $estilos['info'];

        return new HtmlString(
            '<table width="100%" cellpadding="0" cellspacing="0" role="presentation" '
            .'style="margin: 18px 0 24px 0; border: 1px solid '.$borda.'; border-radius: 5px; '
            .'background-color: '.$fundo.';"><tr>'
            .'<td width="46" valign="top" style="padding: 17px 0 17px 16px;">'
            .'<table cellpadding="0" cellspacing="0" role="presentation"><tr>'
            .'<td width="28" height="28" align="center" valign="middle" style="width: 28px; height: 28px; '
            .self::bgSolido($cor).' border-radius: 14px; color: #ffffff; font-size: 14px; font-weight: 700; '
            .'line-height: 28px;">'.$icone.'</td>'
            .'</tr></table></td>'
            .'<td valign="top" style="padding: 16px 16px 16px 10px;">'
            .'<div style="font-size: 14px; font-weight: 700; color: '.self::TEXTO.'; line-height: 1.4;">'
            .e($titulo).'</div>'
            .'<div style="margin-top: 4px; font-size: 13px; color: '.self::TEXTO_SUAVE.'; line-height: 1.55;">'
            .e($texto).'</div>'
            .'</td></tr></table>'
        );
    }

    /** Nota de preferências com link secundário, visualmente separada do conteúdo. */
    public static function preferenciasResumo(string $url): HtmlString
    {
        return new HtmlString(
            '<table width="100%" cellpadding="0" cellspacing="0" role="presentation" '
            .'style="margin: 28px 0 0 0; border-top: 1px solid '.self::BORDA.';"><tr>'
            .'<td style="padding: 18px 0 0 0; font-size: 12px; color: #64748b; line-height: 1.6;">'
            .'<strong style="color: '.self::TEXTO.';">Você controla este resumo.</strong> '
            .'Altere a frequência ou desative o envio em '
            .'<a href="'.e($url).'" style="color: '.self::NAVY.'; font-weight: 700; text-decoration: underline;">'
            .'Configurações de notificações</a>.'
            .'</td></tr></table>'
        );
    }

    /**
     * Placar de números (atividade da semana). Colunas separadas por espaço real,
     * não por borda — fica mais leve que uma grade de caixas cinzas.
     *
     * @param  array<string, string|int>  $kpis  rótulo => valor
     */
    public static function placar(array $kpis): HtmlString
    {
        $celulas = '';
        $largura = (int) round(100 / max(count($kpis), 1));
        $i = 0;

        foreach ($kpis as $rotulo => $valor) {
            $borda = $i++ === 0 ? '' : 'border-left: 1px solid '.self::BORDA.';';
            $celulas .= '<td width="'.$largura.'%" align="center" valign="top" '
                .'style="padding: 18px 8px 17px 8px; '.$borda.'">'
                .'<div style="font-size: 27px; font-weight: 700; color: '.self::NAVY.'; line-height: 1;">'
                .e((string) $valor).'</div>'
                .'<div style="margin-top: 7px; font-size: 10px; font-weight: 700; letter-spacing: 0.06em; '
                .'text-transform: uppercase; color: #64748b; line-height: 1.4;">'.e($rotulo).'</div>'
                .'</td>';
        }

        return new HtmlString(
            '<table width="100%" cellpadding="0" cellspacing="0" role="presentation" '
            .'style="margin: 16px 0 24px 0; border: 1px solid '.self::BORDA.'; border-radius: 5px; '
            .'border-collapse: separate; overflow: hidden; background-color: '.self::FUNDO_SUAVE.';">'
            .'<tr><td colspan="'.count($kpis).'" style="height: 3px; line-height: 3px; font-size: 0; '
            .self::bgSolido(self::OURO).'">&nbsp;</td></tr>'
            .'<tr>'.$celulas.'</tr></table>'
        );
    }

    /**
     * Passos numerados (onboarding). Bolinha navy + título + explicação — a lista
     * "1. 2. 3." corrida em markdown vira um bloco de texto ilegível.
     *
     * @param  array<int, array{0: string, 1: string}>  $passos  [título, descrição]
     */
    public static function passos(array $passos): HtmlString
    {
        $linhas = '';

        foreach ($passos as $i => [$titulo, $descricao]) {
            $ultimo = $i === count($passos) - 1;

            $linhas .= '<tr>'
                .'<td width="34" valign="top" style="padding: 0 12px '.($ultimo ? '0' : '18px').' 0;">'
                .'<table cellpadding="0" cellspacing="0" role="presentation"><tr>'
                .'<td width="26" height="26" align="center" valign="middle" style="width: 26px; height: 26px; '
                .self::bgSolido(self::NAVY).' border-radius: 13px; color: #ffffff; font-size: 13px; '
                .'font-weight: 700; text-align: center; line-height: 26px;">'.($i + 1).'</td>'
                .'</tr></table>'
                .'</td>'
                .'<td valign="top" style="padding: 0 0 '.($ultimo ? '0' : '18px').' 0;">'
                .'<div style="font-size: 15px; font-weight: 600; color: '.self::TEXTO.'; line-height: 1.4;">'.e($titulo).'</div>'
                .'<div style="margin-top: 3px; font-size: 14px; color: '.self::TEXTO_SUAVE.'; line-height: 1.55;">'.$descricao.'</div>'
                .'</td>'
                .'</tr>';
        }

        return new HtmlString(
            '<table width="100%" cellpadding="0" cellspacing="0" role="presentation" '
            .'style="margin: 22px 0;">'.$linhas.'</table>'
        );
    }

    /**
     * Ficha rótulo → valor (comprovante, dados do alerta). Zebrada, com cabeçalho
     * micro-label. É o bloco que o usuário guarda/imprime.
     *
     * @param  array<string, string>  $pares
     */
    public static function ficha(array $pares, string $titulo): HtmlString
    {
        $linhas = '';
        $i = 0;

        foreach ($pares as $rotulo => $valor) {
            $fundo = $i % 2 === 0 ? '#ffffff' : self::FUNDO_SUAVE;
            $i++;

            $linhas .= '<tr>'
                .'<td style="padding: 11px 16px; font-size: 13px; color: '.self::TEXTO_SUAVE.'; '
                .'background-color: '.$fundo.'; white-space: nowrap;">'.e($rotulo).'</td>'
                .'<td align="right" style="padding: 11px 16px; font-size: 13px; font-weight: 600; '
                .'color: '.self::TEXTO.'; background-color: '.$fundo.'; text-align: right;">'.e($valor).'</td>'
                .'</tr>';
        }

        return new HtmlString(
            '<table width="100%" cellpadding="0" cellspacing="0" role="presentation" '
            .'style="margin: 22px 0; border: 1px solid '.self::BORDA.'; border-radius: 4px; '
            .'border-collapse: separate; overflow: hidden;">'
            .'<tr><td colspan="2" style="padding: 10px 16px; background-color: #eef1f5; '
            .'border-bottom: 1px solid '.self::BORDA.'; font-size: 10px; font-weight: 700; '
            .'letter-spacing: 0.1em; text-transform: uppercase; color: #56616f;">'.e($titulo).'</td></tr>'
            .$linhas
            .'</table>'
        );
    }

    /** Caixa de contexto: o "por que isso importa" / consequência. */
    public static function destaque(string $texto, string $cor = self::NAVY): HtmlString
    {
        return new HtmlString(
            '<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin: 22px 0;">'
            .'<tr><td style="padding: 16px 18px; border-left: 3px solid '.$cor.'; '
            .'background-color: '.self::FUNDO_SUAVE.'; border-radius: 0 4px 4px 0; font-size: 14px; '
            .'color: '.self::TEXTO_SUAVE.'; line-height: 1.6;">'.$texto.'</td></tr></table>'
        );
    }

    /**
     * Lista priorizada de alertas: faixa de severidade, contexto, título e exposição.
     *
     * @param  array<int, array{titulo: string, severidade: string, valor_risco: float}>  $itens
     */
    public static function listaAlertas(array $itens, bool $numerar = false): HtmlString
    {
        $linhas = '';
        $total = count($itens);
        $rotulos = [
            'alta' => 'Risco alto',
            'media' => 'Risco médio',
            'baixa' => 'Risco baixo',
        ];

        foreach ($itens as $i => $item) {
            [, $cor] = self::SEVERIDADES[$item['severidade']] ?? self::SEVERIDADES['baixa'];
            $borda = $i === $total - 1 ? 'none' : '1px solid #e8ebef';
            $contexto = $rotulos[$item['severidade']] ?? $rotulos['baixa'];
            if ($numerar) {
                $contexto .= ' · prioridade '.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
            }

            $valor = $item['valor_risco'] > 0
                ? '<div style="margin-top: 8px; font-size: 11px; color: '.self::TEXTO_SUAVE.'; line-height: 1.4;">'
                    .'<span style="font-size: 9px; font-weight: 700; letter-spacing: 0.08em; '
                    .'text-transform: uppercase; color: '.self::TEXTO_SUAVE.';">Exposição mapeada</span>'
                    .'<span style="padding-left: 6px; font-size: 13px; font-weight: 700; color: '.self::VERMELHO.';">'
                    .e(self::brl($item['valor_risco'])).'</span></div>'
                : '';

            $linhas .= '<tr>'
                .'<td width="5" style="'.self::bgSolido($cor).'"></td>'
                .'<td style="padding: 15px 17px 16px 17px; background-color: #ffffff; border-bottom: '.$borda.';">'
                .'<div style="font-size: 9px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; '
                .'color: '.$cor.'; line-height: 1.3;">'.e($contexto).'</div>'
                .'<div style="margin-top: 5px; font-size: 14px; font-weight: 700; color: '.self::TEXTO.'; '
                .'line-height: 1.45;">'.e($item['titulo']).'</div>'
                .$valor
                .'</td>'
                .'</tr>';
        }

        return new HtmlString(
            '<table width="100%" cellpadding="0" cellspacing="0" role="presentation" '
            .'style="margin: 16px 0 18px 0; border: 1px solid '.self::BORDA.'; border-radius: 5px; '
            .'border-collapse: separate; overflow: hidden; background-color: #ffffff;">'.$linhas.'</table>'
        );
    }

    /** Formata valores monetários do produto em R$. */
    public static function brl(float $valor): string
    {
        return 'R$ '.number_format($valor, 2, ',', '.');
    }

    /**
     * Fundo sólido que SOBREVIVE ao dark mode do iOS Mail. Usar **só em fundo
     * SATURADO com texto claro em cima** (navy, dourado, vermelho, verde) — nunca em
     * fundo branco/claro.
     *
     * iOS Mail (iPhone) às vezes ignora `color-scheme: only light` e transforma o
     * render no dark mode: `background-color` é clareado (navy → azul-bebê), mas
     * `background-image` NÃO. Declarar a cor também como gradiente sólido (mesma cor
     * nas duas pontas) segura o iOS; o Outlook (ignora gradiente) cai no
     * `background-color`; os demais veem sólido idêntico.
     *
     * **Por que não em fundo claro:** o texto ESCURO desses blocos o iOS clareia (sem
     * escape equivalente ao bg-image). Se travássemos o fundo branco, o texto clareado
     * ficaria branco-no-branco (invisível). Deixando o fundo claro seguir o iOS, ele
     * escurece junto com o texto que clareia → continua legível. Fundo saturado tem
     * texto branco, que o iOS mantém claro → branco sobre navy = ok nos dois casos.
     */
    public static function bgSolido(string $cor): string
    {
        return 'background-color: '.$cor.'; background-image: linear-gradient('.$cor.', '.$cor.');';
    }
}
