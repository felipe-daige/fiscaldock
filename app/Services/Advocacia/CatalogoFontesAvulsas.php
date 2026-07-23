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
    /** @var list<string>|null Memo das chaves disponíveis (registry não muda durante a request). */
    private ?array $chavesDisponiveisMemo = null;

    /** @var array<int, \Illuminate\Support\Collection<int, \App\Models\ConsultaKit>> Memo dos kits ativos por usuário. */
    private array $kitsAtivosMemo = [];

    /** @var \Illuminate\Support\Collection<string, \App\Models\FontePreco>|null Memo dos overrides de preço. */
    private ?\Illuminate\Support\Collection $precosDbMemo = null;

    public function __construct(private FonteRegistry $registry) {}

    /**
     * Kits GLOBAIS ativos VISÍVEIS pro usuário (publico='todos' + os 'selecionados' atribuídos a
     * ele), carregados UMA vez por usuário/instância. Fonte única da segmentação — vitrine
     * (kits) e precificação (kitPara/precificar) leem daqui, então quem não recebeu o kit
     * segmentado nem vê o card nem leva o preço/desconto dele.
     */
    private function kitsAtivosPara(int $userId): \Illuminate\Support\Collection
    {
        return $this->kitsAtivosMemo[$userId] ??= \App\Models\ConsultaKit::globais()->ativos()
            ->paraUsuario($userId)->get();
    }

    /** Overrides de preço/ativo por fonte (tabela fonte_precos), UMA query por instância. */
    private function precosDb(): \Illuminate\Support\Collection
    {
        return $this->precosDbMemo ??= \App\Models\FontePreco::all()->keyBy('chave');
    }

    /** True se o admin desativou a fonte comercialmente (linha fonte_precos com ativo=false). */
    private function desativadaNoAdmin(string $chave): bool
    {
        $row = $this->precosDb()->get($chave);

        return $row !== null && ! $row->ativo;
    }

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
                if (! $fonte
                    || ! $fonte->pronta()
                    || $this->registry->pausada($chave)      // pausa OPERACIONAL (origem fora do ar)
                    || $this->desativadaNoAdmin($chave)) {   // desligamento COMERCIAL (admin)
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

    /**
     * Preço de venda da fonte em R$. Hierarquia: override no admin (tabela fonte_precos) →
     * config('advocacia.precos.{chave}') → config('advocacia.preco_fonte_default').
     */
    public function precoDe(string $chave): float
    {
        $row = $this->precosDb()->get($chave);
        if ($row !== null) {
            return (float) $row->preco;
        }

        return (float) (config("advocacia.precos.{$chave}") ?? config('advocacia.preco_fonte_default', 1.00));
    }

    /** Chaves válidas para seleção avulsa (registradas + prontas). Memoizado por instância. */
    public function chavesDisponiveis(): array
    {
        if ($this->chavesDisponiveisMemo !== null) {
            return $this->chavesDisponiveisMemo;
        }

        $chaves = [];
        foreach ($this->grupos() as $grupo) {
            $chaves = array_merge($chaves, array_column($grupo['fontes'], 'chave'));
        }

        return $this->chavesDisponiveisMemo = $chaves;
    }

    /** Preço total (R$) de uma seleção para UM alvo, SEM desconto de kit (soma bruta). */
    public function precoSelecao(array $chaves): float
    {
        return round(array_sum(array_map(fn ($c) => $this->precoDe($c), array_unique($chaves))), 2);
    }

    /**
     * Custo do PROVEDOR (InfoSimples) por fonte, em R$ — o que pagamos por consulta. Fonte única:
     * config('consultas.fontes.{chave}'), a mesma que o gate de preço por fonte do admin usa; 0
     * quando a fonte não tem custo externo (cadastro/derivadas). Serve pra barrar venda abaixo do
     * custo (kit e fonte avulsa leem daqui — a regra do custo mora com quem conhece o preço).
     */
    public function custoDe(string $chave): float
    {
        return (float) config("consultas.fontes.{$chave}", 0);
    }

    /** Custo total do provedor (R$) de uma seleção de fontes — piso de preço de um kit. */
    public function custoSelecao(array $chaves): float
    {
        return round(array_sum(array_map(fn ($c) => $this->custoDe($c), array_unique($chaves))), 2);
    }

    /**
     * Resumo de preço de um kit para o PAINEL ADMIN: fontes válidas, preço cheio (bruto) e preço
     * final do kit (fixo ou com desconto%). INDEPENDE de segmentação/usuário — o admin vê o preço
     * do kit em si, não o preço escopado a um comprador.
     *
     * @return array{fontes: list<string>, bruto: float, total: float}
     */
    public function resumoKit(\App\Models\ConsultaKit $kit): array
    {
        $fontes = array_values(array_intersect((array) $kit->fontes, $this->chavesDisponiveis()));
        $bruto = $this->precoSelecao($fontes);

        return ['fontes' => $fontes, 'bruto' => $bruto, 'total' => $this->totalDoKit($kit, $bruto)];
    }

    /**
     * Kits da vitrine VISÍVEIS pro usuário: todos os globais ativos de `publico='todos'` mais os
     * `selecionados` atribuídos a ele. `ativo` (+`publico`) decide a visibilidade — `sistema` não
     * gate mais nada aqui (é só proteção de exclusão no admin).
     *
     * @return list<array{id: int, nome: string, slug: string, descricao: ?string, desconto_percentual: float, preco_fixo: ?float, fontes: list<string>, preco_bruto: float, preco_total: float}>
     */
    public function kits(int $userId): array
    {
        $out = [];
        foreach ($this->kitsAtivosPara($userId) as $kit) {
            // Preço do PRÓPRIO kit (resumoKit = totalDoKit), nunca via precificar/kitPara: dois kits
            // visíveis com o mesmo conjunto de fontes fariam o kitPara devolver o de menor total, e
            // o card do mais caro exibiria o preço do mais barato. O admin (resumoKit) já mostra o
            // preço de cada kit — a vitrine tem de bater com ele.
            $resumo = $this->resumoKit($kit);
            if ($resumo['fontes'] === []) {
                continue;
            }
            $out[] = [
                'id' => $kit->id,
                'nome' => $kit->nome,
                'slug' => $kit->slug,
                'descricao' => $kit->descricao,
                'desconto_percentual' => (float) $kit->desconto_percentual,
                'preco_fixo' => $kit->preco_fixo !== null ? (float) $kit->preco_fixo : null,
                'fontes' => $resumo['fontes'],
                'preco_bruto' => $resumo['bruto'],
                'preco_total' => $resumo['total'],
            ];
        }

        return $out;
    }

    /**
     * Presets PESSOAIS de um usuário (consulta_kits com user_id) para a tela: fontes restritas
     * às disponíveis, preço = SOMA (sem desconto — desconto é exclusivo de kit global do admin).
     *
     * @return list<array{id: int, nome: string, slug: string, descricao: ?string, fontes: list<string>, preco_total: float}>
     */
    public function presets(int $userId): array
    {
        $disponiveis = $this->chavesDisponiveis();

        $out = [];
        foreach (\App\Models\ConsultaKit::doUsuario($userId)->ativos()->get() as $preset) {
            $fontes = array_values(array_intersect((array) $preset->fontes, $disponiveis));
            if ($fontes === []) {
                continue;
            }
            $out[] = [
                'id' => $preset->id,
                'nome' => $preset->nome,
                'slug' => $preset->slug,
                'descricao' => $preset->descricao,
                'fontes' => $fontes,
                'preco_total' => $this->precoSelecao($fontes),
            ];
        }

        return $out;
    }

    /**
     * Kit VISÍVEL pro usuário cuja lista de fontes DISPONÍVEIS bate exatamente com a seleção
     * (menor preço efetivo pro comprador vence em empate de conjunto — cobre kit fixo e kit %).
     * Ajustou a seleção → sem kit: kit é preset, não bundle.
     */
    public function kitPara(array $chaves, int $userId): ?\App\Models\ConsultaKit
    {
        $selecao = array_values(array_unique($chaves));
        sort($selecao);

        $disponiveis = $this->chavesDisponiveis();
        $bruto = $this->precoSelecao($selecao);
        $match = null;
        $melhorTotal = null;
        foreach ($this->kitsAtivosPara($userId) as $kit) {
            $fontesKit = array_values(array_unique(array_intersect((array) $kit->fontes, $disponiveis)));
            sort($fontesKit);
            if ($fontesKit !== $selecao) {
                continue;
            }
            $total = $this->totalDoKit($kit, $bruto);
            if ($melhorTotal === null || $total < $melhorTotal) {
                $match = $kit;
                $melhorTotal = $total;
            }
        }

        return $match;
    }

    /** Preço final (R$) que o kit cobra por alvo: fixo quando definido, senão soma bruta com desconto%. */
    private function totalDoKit(\App\Models\ConsultaKit $kit, float $bruto): float
    {
        if ($kit->preco_fixo !== null) {
            return round((float) $kit->preco_fixo, 2);
        }

        return round($bruto * max(0, 100 - (float) $kit->desconto_percentual) / 100, 2);
    }

    /**
     * Precificação autoritativa de uma seleção para UM alvo. O preço de kit é aplicado POR FONTE
     * (o estorno de falha devolve o preço unitário efetivamente cobrado — precosVenda): kit por %
     * escala cada fonte pelo fator; kit por preço FIXO rateia o valor entre as fontes proporcional
     * ao unitário (resto de arredondamento cai na fonte mais cara).
     *
     * @return array{precos: array<string, float>, bruto: float, total: float, desconto_reais: float, kit: ?array{id: int, nome: string, desconto_percentual: float, preco_fixo: ?float}}
     */
    public function precificar(array $chaves, int $userId): array
    {
        $chaves = array_values(array_unique($chaves));
        $kit = $this->kitPara($chaves, $userId);

        $unitarios = [];
        $bruto = 0.0;
        foreach ($chaves as $chave) {
            $unitarios[$chave] = $this->precoDe($chave);
            $bruto += $unitarios[$chave];
        }
        $bruto = round($bruto, 2);

        if ($kit !== null && $kit->preco_fixo !== null) {
            $precos = $this->ratearFixo($unitarios, $bruto, round((float) $kit->preco_fixo, 2));
        } else {
            $fator = $kit !== null ? max(0, 100 - (float) $kit->desconto_percentual) / 100 : 1.0;
            $precos = [];
            foreach ($unitarios as $chave => $unitario) {
                $precos[$chave] = round($unitario * $fator, 2);
            }
        }
        $total = round(array_sum($precos), 2);

        return [
            'precos' => $precos,
            'bruto' => $bruto,
            'total' => $total,
            // Economia do kit (positiva). Kit por preço fixo ACIMA da soma é um acréscimo válido
            // (admin precifica como quer), então isto pode ser negativo — a UI só mostra a linha
            // quando > 0 (JS: `desconto_por_alvo_reais > 0`; vitrine: `preco_total < preco_bruto`).
            'desconto_reais' => round($bruto - $total, 2),
            'kit' => $kit !== null
                ? [
                    'id' => $kit->id,
                    'nome' => $kit->nome,
                    'desconto_percentual' => (float) $kit->desconto_percentual,
                    'preco_fixo' => $kit->preco_fixo !== null ? (float) $kit->preco_fixo : null,
                ]
                : null,
        ];
    }

    /**
     * Rateia um preço fixo de kit entre as fontes proporcional ao unitário, garantindo que a soma
     * dos preços por fonte fecha EXATO no alvo (o resto de arredondamento vai pra fonte mais cara).
     * Base bruta zero (só fontes grátis) → divide igual. Preserva o contrato de precosVenda usado
     * no estorno por fonte.
     *
     * @param  array<string, float>  $unitarios
     * @return array<string, float>
     */
    private function ratearFixo(array $unitarios, float $bruto, float $alvo): array
    {
        $precos = [];
        $n = count($unitarios);
        if ($n === 0) {
            return $precos;
        }

        if ($bruto <= 0.0) {
            $fatia = round($alvo / $n, 2);
            foreach ($unitarios as $chave => $_) {
                $precos[$chave] = $fatia;
            }
        } else {
            foreach ($unitarios as $chave => $unitario) {
                $precos[$chave] = round($unitario / $bruto * $alvo, 2);
            }
        }

        // Ajuste do resto de arredondamento na fonte mais cara (maior unitário) — comparação ESTRITA
        // (o 4º arg) pra casar o float exato e não pegar uma chave por coerção frouxa.
        $resto = round($alvo - array_sum($precos), 2);
        if ($resto !== 0.0) {
            $maisCara = array_keys($unitarios, max($unitarios), true)[0];
            $precos[$maisCara] = round($precos[$maisCara] + $resto, 2);
        }

        return $precos;
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
