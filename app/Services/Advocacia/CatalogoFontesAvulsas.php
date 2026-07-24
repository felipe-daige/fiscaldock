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
    /** @var array<string, list<string>> Memo por tipo (TODOS|PF|PJ). */
    private array $chavesDisponiveisMemo = [];

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
     * Catálogo público agrupado. Fontes futuras/pausadas continuam visíveis como manutenção, mas
     * `selecionavel=false` impede checkbox, preço no carrinho e execução. `fonte_precos.ativo`
     * controla a PUBLICAÇÃO: false oculta tanto fonte pronta quanto futura.
     *
     * @return array<string, array{label: string, fontes: list<array<string, mixed>>}>
     */
    public function grupos(?string $tipoPessoa = null): array
    {
        $tipoPessoa = $tipoPessoa !== null ? strtoupper($tipoPessoa) : null;
        $out = [];
        foreach ((array) config('advocacia.grupos', []) as $chaveGrupo => $grupo) {
            $fontes = [];
            foreach ((array) ($grupo['fontes'] ?? []) as $chave) {
                $fonte = $this->registry->get($chave);
                if ($this->desativadaNoAdmin($chave)) {
                    continue;
                }

                $meta = (array) config("advocacia.catalogo_fontes.{$chave}", []);
                $tiposOperacionais = $fonte
                    ? array_values(array_unique(array_map('strtoupper', $fonte->aceitaPessoa())))
                    : [];
                $tiposPlanejados = array_values(array_unique(array_map(
                    'strtoupper',
                    (array) ($meta['tipos_pessoa'] ?? $tiposOperacionais),
                )));

                // Filtro da vitrine: uma fonte em construção continua aparecendo para o tipo que
                // ela planeja atender. O checkbox só será habilitado quando o tipo também estiver
                // no contrato OPERACIONAL da classe.
                if ($tipoPessoa !== null && ! in_array($tipoPessoa, $tiposPlanejados, true)) {
                    continue;
                }

                $pausada = $this->registry->pausada($chave);
                $pronta = $fonte !== null && $fonte->pronta() && ! $pausada;
                $selecionavel = $pronta
                    && ($tipoPessoa === null || in_array($tipoPessoa, $tiposOperacionais, true));
                $tiposEmManutencao = array_values(array_diff($tiposPlanejados, $tiposOperacionais));
                $labelDocumentos = (string) ($meta['documentos_label']
                    ?? $this->rotuloDocumentosAceitos(
                        $tiposOperacionais !== [] ? $tiposOperacionais : $tiposPlanejados
                    ));

                $fontes[] = [
                    'chave' => $chave,
                    'nome' => (string) config("consultas.fonte_nome.{$chave}", $chave),
                    'preco' => $this->precoDe($chave),
                    'tipos_pessoa' => $tiposOperacionais,
                    'tipos_pessoa_planejados' => $tiposPlanejados,
                    'tipos_pessoa_em_manutencao' => $tiposEmManutencao,
                    'documentos_aceitos_label' => $labelDocumentos,
                    'selecionavel' => $selecionavel,
                    'em_manutencao' => ! $pronta,
                    'pausada' => $pausada,
                    'requer_autenticacao' => (bool) ($meta['requer_autenticacao'] ?? false),
                    'motivo_manutencao' => $this->motivoManutencao($chave, $fonte, $meta, $pausada),
                    'descricao' => $meta['descricao'] ?? null,
                    'requisitos_pf' => (array) config("advocacia.requisitos_pf.{$chave}", []),
                    'requisitos_alvo' => (array) config("advocacia.requisitos_alvo.{$chave}", []),
                    'sensivel' => in_array($chave, $this->chavesSensiveis(), true),
                ];
            }
            if ($fontes !== []) {
                $out[$chaveGrupo] = ['label' => (string) ($grupo['label'] ?? $chaveGrupo), 'fontes' => $fontes];
            }
        }

        return $out;
    }

    /**
     * Rótulo público do contrato efetivamente liberado no FiscalDock. Não representa apenas a
     * capacidade teórica do endpoint: uma fonte só vira "CPF e CNPJ" depois de ambos os fluxos
     * estarem implementados e validados.
     *
     * @param  list<string>  $tiposPessoa
     */
    public function rotuloDocumentosAceitos(array $tiposPessoa): string
    {
        $tipos = array_values(array_unique(array_map('strtoupper', $tiposPessoa)));
        $aceitaPf = in_array('PF', $tipos, true);
        $aceitaPj = in_array('PJ', $tipos, true);

        if ($aceitaPf && $aceitaPj) {
            return 'CPF e CNPJ';
        }

        if ($aceitaPf) {
            return 'CPF';
        }

        return $aceitaPj ? 'CNPJ' : 'Identificador próprio';
    }

    /**
     * Mensagem curta da vitrine. O detalhe técnico fica no admin; o usuário só precisa saber por
     * que ainda não consegue marcar a consulta.
     *
     * @param  array<string, mixed>  $meta
     */
    private function motivoManutencao(
        string $chave,
        ?\App\Services\Consultas\Contracts\Fonte $fonte,
        array $meta,
        bool $pausada,
    ): ?string {
        if ($fonte !== null && $fonte->pronta() && ! $pausada) {
            return null;
        }

        if (filled($meta['motivo_manutencao'] ?? null)) {
            return (string) $meta['motivo_manutencao'];
        }

        if ($pausada) {
            return 'A fonte oficial está temporariamente indisponível.';
        }

        if ((bool) ($meta['requer_autenticacao'] ?? false)) {
            return 'Integração com GOV.BR, certificado digital ou conta externa prevista para a próxima etapa.';
        }

        return 'Integração em desenvolvimento.';
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

    /** Chaves de fonte marcadas como sensíveis (dado pessoal sensível LGPD art. 11). */
    public function chavesSensiveis(): array
    {
        return array_keys((array) config('advocacia.fontes_sensiveis', []));
    }

    /** A seleção contém alguma fonte sensível? (dispara a exigência da declaração LGPD). */
    public function selecaoTemSensivel(array $chaves): bool
    {
        return array_intersect(array_unique($chaves), $this->chavesSensiveis()) !== [];
    }

    /**
     * A fonte aceita este tipo de pessoa como alvo? (PF | PJ). Fonte inexistente/não registrada
     * => false. Autoridade única do gate PF x PJ, lida pela validação do lote avulso e pela tela.
     */
    public function aceitaTipo(string $chave, string $tipoPessoa): bool
    {
        return $this->registry->aceitaTipo($chave, $tipoPessoa);
    }

    /**
     * Fontes da seleção que NÃO aceitam o tipo do alvo — o lote avulso rejeita (422) quando
     * não-vazio. Ex.: alvo CPF com `sintegra`/`cadastro` (PJ-only) na seleção.
     *
     * @return list<string>
     */
    public function fontesIncompativeis(array $chaves, string $tipoPessoa): array
    {
        return array_values(array_filter(
            array_unique($chaves),
            fn (string $c) => ! $this->aceitaTipo($c, $tipoPessoa),
        ));
    }

    /**
     * Campos complementares exigidos pelas fontes PF selecionadas. Esses dados viajam apenas no
     * payload do lote; não criam uma segunda entidade de pessoa física.
     *
     * @return list<string>
     */
    public function requisitosPf(array $chaves): array
    {
        $requisitos = [];
        foreach (array_unique($chaves) as $chave) {
            $requisitos = array_merge(
                $requisitos,
                (array) config("advocacia.requisitos_pf.{$chave}", []),
            );
        }

        return array_values(array_unique($requisitos));
    }

    /**
     * Campos complementares exigidos de qualquer alvo pelas fontes selecionadas. Ex.: `ano` na
     * consulta de autuações do IBAMA, independentemente de o documento ser CPF ou CNPJ.
     *
     * @return list<string>
     */
    public function requisitosAlvo(array $chaves): array
    {
        $requisitos = [];
        foreach (array_unique($chaves) as $chave) {
            $requisitos = array_merge(
                $requisitos,
                (array) config("advocacia.requisitos_alvo.{$chave}", []),
            );
        }

        return array_values(array_unique($requisitos));
    }

    /** Chaves válidas para seleção avulsa (registradas + prontas). Memoizado por instância. */
    public function chavesDisponiveis(?string $tipoPessoa = null): array
    {
        $memo = $tipoPessoa !== null ? strtoupper($tipoPessoa) : 'TODOS';
        if (isset($this->chavesDisponiveisMemo[$memo])) {
            return $this->chavesDisponiveisMemo[$memo];
        }

        $chaves = [];
        foreach ($this->grupos($tipoPessoa) as $grupo) {
            foreach ($grupo['fontes'] as $fonte) {
                if ($fonte['selecionavel']) {
                    $chaves[] = $fonte['chave'];
                }
            }
        }

        return $this->chavesDisponiveisMemo[$memo] = $chaves;
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
    public function atributosDe(array $chaves, string $tipoPessoa = 'PJ'): array
    {
        // Cadastral grátis auto-injetado SÓ em alvo PJ (minhareceita é CNPJ-only + grátis). Em alvo
        // PF não há cadastral grátis: o `cadastro_pf` (receita-federal/cpf) é pago e só roda se o
        // usuário o selecionar — não forçamos uma chamada paga como fazemos com o cadastro PJ.
        $atributos = strtoupper($tipoPessoa) === 'PJ'
            ? ($this->registry->get('cadastro')?->fornece() ?? [])
            : [];
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
     * `inicializacao`; `cadastrais` é fixa em PJ e só entra em PF quando uma fonte desse grupo
     * foi selecionada.
     */
    public function etapasDe(array $chaves, string $tipoPessoa = 'PJ'): array
    {
        // Ordem canônica dos grupos (espelha a progressão dos planos do seeder; os grupos
        // do vertical advocacia — judiciais/integridade/passivo — só existem em lote avulso).
        $ordem = ['cadastrais' => 'Dados cadastrais', 'certidoes_federais' => 'Certidões Federais',
            'certidoes_estaduais' => 'Certidões Estaduais/Municipais', 'sancoes' => 'Sanções',
            'certidoes_judiciais' => 'Certidões Judiciais', 'integridade' => 'Integridade e Sanções',
            'ambiental' => 'Regularidade Ambiental', 'passivo' => 'Passivo e Insolvência',
            'patrimonio' => 'Patrimônio e Ativos', 'imoveis' => 'Imóveis e Propriedade Rural',
            'processual' => 'Consultas Processuais'];

        // PJ: cadastral sempre roda => etapa 'cadastrais' fixa. PF: só aparece se uma fonte
        // selecionada mapear pra 'cadastrais' (ex.: cadastro_pf), já que nada é auto-injetado.
        $presentes = strtoupper($tipoPessoa) === 'PJ' ? ['cadastrais' => true] : [];
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
