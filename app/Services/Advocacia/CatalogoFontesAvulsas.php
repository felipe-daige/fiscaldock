<?php

namespace App\Services\Advocacia;

use App\Services\Consultas\FonteRegistry;

/**
 * Catálogo da consulta à la carte (vertical advocacia): quais fontes podem ser selecionadas
 * avulsas, com preço de venda por fonte (config advocacia.php, default R$ 1,00) e grupo de
 * apresentação. O lote avulso reusa o motor inteiro (ProcessarConsultaJob + FecharLoteService):
 * a seleção vira `consultasIncluidas` = união dos fornece() das fontes escolhidas + cadastro.
 *
 * Spec: docs/advocacia/consultas-certidoes.md (fase 1).
 */
class CatalogoFontesAvulsas
{
    public function __construct(private FonteRegistry $registry) {}

    /**
     * Fontes selecionáveis (registradas + prontas), agrupadas para a tela.
     *
     * @return array<string, array{label: string, fontes: list<array{chave: string, nome: string, preco: float}>}>
     */
    public function grupos(): array
    {
        $out = [];
        foreach ((array) config('advocacia.grupos', []) as $chaveGrupo => $grupo) {
            $fontes = [];
            foreach ((array) ($grupo['fontes'] ?? []) as $chave) {
                $fonte = $this->registry->get($chave);
                if (! $fonte || ! $fonte->pronta()) {
                    continue;
                }
                $fontes[] = [
                    'chave' => $chave,
                    'nome' => (string) config("consultas.fonte_nome.{$chave}", $chave),
                    'preco' => $this->precoDe($chave),
                ];
            }
            if ($fontes !== []) {
                $out[$chaveGrupo] = ['label' => (string) ($grupo['label'] ?? $chaveGrupo), 'fontes' => $fontes];
            }
        }

        return $out;
    }

    /** Preço de venda da fonte em R$ (override em advocacia.precos, senão o default). */
    public function precoDe(string $chave): float
    {
        return (float) (config("advocacia.precos.{$chave}") ?? config('advocacia.preco_fonte_default', 1.00));
    }

    /** Chaves válidas para seleção avulsa (registradas + prontas). */
    public function chavesDisponiveis(): array
    {
        $chaves = [];
        foreach ($this->grupos() as $grupo) {
            $chaves = array_merge($chaves, array_column($grupo['fontes'], 'chave'));
        }

        return $chaves;
    }

    /** Preço total (R$) de uma seleção para UM alvo, SEM desconto de kit (soma bruta). */
    public function precoSelecao(array $chaves): float
    {
        return round(array_sum(array_map(fn ($c) => $this->precoDe($c), array_unique($chaves))), 2);
    }

    /**
     * Kits ativos para a tela: fontes restritas às disponíveis, com preço bruto e com desconto.
     *
     * @return list<array{id: int, nome: string, slug: string, descricao: ?string, desconto_percentual: float, fontes: list<string>, preco_bruto: float, preco_total: float}>
     */
    public function kits(): array
    {
        $disponiveis = $this->chavesDisponiveis();

        $out = [];
        foreach (\App\Models\ConsultaKit::ativos()->get() as $kit) {
            $fontes = array_values(array_intersect((array) $kit->fontes, $disponiveis));
            if ($fontes === []) {
                continue;
            }
            $preco = $this->precificar($fontes);
            $out[] = [
                'id' => $kit->id,
                'nome' => $kit->nome,
                'slug' => $kit->slug,
                'descricao' => $kit->descricao,
                'desconto_percentual' => (float) $kit->desconto_percentual,
                'fontes' => $fontes,
                'preco_bruto' => $preco['bruto'],
                'preco_total' => $preco['total'],
            ];
        }

        return $out;
    }

    /**
     * Kit ativo cuja lista de fontes DISPONÍVEIS bate exatamente com a seleção (maior desconto
     * vence em empate de conjunto). Ajustou a seleção → sem desconto: kit é preset, não bundle.
     */
    public function kitPara(array $chaves): ?\App\Models\ConsultaKit
    {
        $selecao = array_values(array_unique($chaves));
        sort($selecao);

        $disponiveis = $this->chavesDisponiveis();
        $match = null;
        foreach (\App\Models\ConsultaKit::ativos()->get() as $kit) {
            $fontesKit = array_values(array_unique(array_intersect((array) $kit->fontes, $disponiveis)));
            sort($fontesKit);
            if ($fontesKit === $selecao
                && ($match === null || (float) $kit->desconto_percentual > (float) $match->desconto_percentual)) {
                $match = $kit;
            }
        }

        return $match;
    }

    /**
     * Precificação autoritativa de uma seleção para UM alvo, com desconto de kit aplicado POR
     * FONTE (o estorno de falha devolve o preço unitário efetivamente cobrado — precosVenda).
     *
     * @return array{precos: array<string, float>, bruto: float, total: float, desconto_reais: float, kit: ?array{id: int, nome: string, desconto_percentual: float}}
     */
    public function precificar(array $chaves): array
    {
        $chaves = array_values(array_unique($chaves));
        $kit = $this->kitPara($chaves);
        $fator = $kit !== null ? max(0, 100 - (float) $kit->desconto_percentual) / 100 : 1.0;

        $precos = [];
        $bruto = 0.0;
        foreach ($chaves as $chave) {
            $unitario = $this->precoDe($chave);
            $bruto += $unitario;
            $precos[$chave] = round($unitario * $fator, 2);
        }
        $total = round(array_sum($precos), 2);

        return [
            'precos' => $precos,
            'bruto' => round($bruto, 2),
            'total' => $total,
            'desconto_reais' => round($bruto - $total, 2),
            'kit' => $kit !== null
                ? ['id' => $kit->id, 'nome' => $kit->nome, 'desconto_percentual' => (float) $kit->desconto_percentual]
                : null,
        ];
    }

    /**
     * Atributos de consultas_incluidas equivalentes à seleção — é o que o ProcessarConsultaJob
     * consome (deriva as fontes de volta via FonteRegistry::fontesDe). O cadastro (grátis)
     * entra SEMPRE: fornece UF/município autoritativos às fontes UF-dependentes e mantém a
     * ficha cadastral fresca.
     */
    public function atributosDe(array $chaves): array
    {
        $atributos = $this->registry->get('cadastro')?->fornece() ?? [];
        foreach (array_unique($chaves) as $chave) {
            $fonte = $this->registry->get($chave);
            if ($fonte) {
                $atributos = array_merge($atributos, $fonte->fornece());
            }
        }

        return array_values(array_unique($atributos));
    }

    /**
     * Etapas do progresso (contrato SSE) derivadas DINAMICAMENTE dos grupos de etapa das
     * fontes selecionadas — substitui o seeder fixo por plano só neste fluxo. Sempre inclui
     * `inicializacao` e `cadastrais` (o cadastro sempre roda).
     */
    public function etapasDe(array $chaves): array
    {
        // Ordem canônica dos grupos (espelha a progressão dos planos do seeder; os grupos
        // do vertical advocacia — judiciais/integridade/passivo — só existem em lote avulso).
        $ordem = ['cadastrais' => 'Dados cadastrais', 'certidoes_federais' => 'Certidões Federais',
            'certidoes_estaduais' => 'Certidões Estaduais/Municipais', 'sancoes' => 'Sanções',
            'certidoes_judiciais' => 'Certidões Judiciais', 'integridade' => 'Integridade e Sanções',
            'passivo' => 'Passivo e Insolvência'];

        $presentes = ['cadastrais' => true];
        foreach (array_unique($chaves) as $chave) {
            $grupo = (string) config("consultas.fonte_etapa.{$chave}", 'cadastrais');
            $presentes[$grupo] = true;
        }

        $etapas = [['numero' => 1, 'chave' => 'inicializacao', 'label' => 'Preparando consulta']];
        $n = 1;
        foreach ($ordem as $grupo => $label) {
            if (isset($presentes[$grupo])) {
                $etapas[] = ['numero' => ++$n, 'chave' => $grupo, 'label' => $label];
            }
        }

        return $etapas;
    }
}
