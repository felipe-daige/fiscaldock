<?php

namespace App\Services;

use App\Models\MonitoramentoPlano;
use App\Models\SaldoTransacao;
use App\Models\User;
use App\Support\Monitoramento\PlanoCatalog;

/**
 * Catálogo comercial. Todo valor monetário do produto — saldo, preço, custo, ledger —
 * é expresso diretamente em reais (R$). A unidade interna legada de "créditos"
 * (1 crédito = R$ 0,20) foi removida em 2026-07-14; `credit_transactions` e
 * `users.credits` armazenam R$ com 2 casas.
 */
class PricingCatalogService
{
    // Piso de depósito no sistema. Alinhado ao mínimo do provedor (InfoSimples ~R$100).
    public const MINIMUM_DEPOSIT = 100.00;

    public const FIRST_PURCHASE_LOCKED_PRODUCTS = ['compliance'];

    public function __construct(
        private \App\Services\Entitlements\EntitlementService $entitlements = new \App\Services\Entitlements\EntitlementService,
        private \App\Services\Admin\ComercialParametroService $comercial = new \App\Services\Admin\ComercialParametroService
    ) {}

    /**
     * Atalhos de recarga destacados. `creditos` = saldo em R$ liberado (1:1 com o preço).
     */
    public function getFeaturedOffers(): array
    {
        return [
            [
                'slug' => 'business',
                'nome' => 'Business',
                'creditos' => 200.00,
                'preco' => 200.00,
                'badge' => 'Recarga rápida',
                'usage_hint' => 'Para a rotina do mês',
                'featured' => true,
                'descricao' => 'Um atalho de recarga para manter a rotina fiscal em movimento, sem assinatura obrigatória.',
            ],
            [
                // slug 'enterprise' mantido por compatibilidade de rota; nome de exibição
                // é "Volume" para não colidir com o tier de assinatura Enterprise.
                'slug' => 'enterprise',
                'nome' => 'Volume',
                'creditos' => 1000.00,
                'preco' => 1000.00,
                'badge' => 'Maior volume',
                'usage_hint' => 'Para operação intensiva',
                'featured' => false,
                'descricao' => 'Recarga direta para operações com maior volume de consultas e validações.',
            ],
        ];
    }

    public function getPackages(): array
    {
        return $this->getFeaturedOffers();
    }

    public function getMinimumDeposit(): float
    {
        return (float) $this->comercial->valor('minimum_deposit', self::MINIMUM_DEPOSIT);
    }

    public function getFirstPurchaseLockedProducts(): array
    {
        return self::FIRST_PURCHASE_LOCKED_PRODUCTS;
    }

    public function userHasFirstPurchase(User $user): bool
    {
        return SaldoTransacao::query()
            ->where('user_id', $user->id)
            ->where('type', 'purchase')
            ->where('amount', '>', 0)
            ->exists();
    }

    public function productRequiresFirstPurchase(string $productCode): bool
    {
        return in_array($productCode, self::FIRST_PURCHASE_LOCKED_PRODUCTS, true);
    }

    public function userCanUseProduct(User $user, string $productCode): bool
    {
        if (! $this->productRequiresFirstPurchase($productCode)) {
            return true;
        }

        return $this->userHasFirstPurchase($user);
    }

    /**
     * Status do cap de consultas do plano Gratuito.
     * Sem a 1ª compra confirmada, o usuário tem no máximo `trial.limite_consultas_gratuito`
     * participantes consultados neste plano. Após a 1ª compra o cap desaparece.
     *
     * @return array{limite: int, usados: int, restantes: int, bloqueado: bool}
     */
    public function gratuitoCapStatus(User $user, int $novos = 0): array
    {
        $limite = (int) config('trial.limite_consultas_gratuito', 3);

        if ($this->userHasFirstPurchase($user)) {
            return ['limite' => $limite, 'usados' => 0, 'restantes' => $limite, 'bloqueado' => false];
        }

        $gratuitoId = \App\Models\MonitoramentoPlano::where('codigo', 'gratuito')->value('id');
        $usados = (int) \App\Models\ConsultaLote::query()
            ->where('user_id', $user->id)
            ->where('plano_id', $gratuitoId)
            ->where('status', '!=', \App\Models\ConsultaLote::STATUS_ERRO)
            ->sum('total_participantes');

        return [
            'limite' => $limite,
            'usados' => $usados,
            'restantes' => max(0, $limite - $usados),
            'bloqueado' => ($usados + $novos) > $limite,
        ];
    }

    public function getPackageBySlug(string $slug): ?array
    {
        foreach ($this->getFeaturedOffers() as $package) {
            if ($package['slug'] === $slug) {
                return $this->decorateOffer($package);
            }
        }

        return null;
    }

    public function buildCustomDeposit(float $amount): ?array
    {
        $normalizedAmount = round($amount, 2);

        if ($normalizedAmount < $this->getMinimumDeposit()) {
            return null;
        }

        return [
            'slug' => 'custom',
            'nome' => 'Recarga personalizada',
            'creditos' => $normalizedAmount,
            'preco' => $normalizedAmount,
            'badge' => 'Valor livre',
            'usage_hint' => 'Você escolhe quanto pagar',
            'featured' => false,
            'descricao' => 'Depósito customizado acima do mínimo operacional para comprar apenas o saldo que fizer sentido agora.',
            'is_custom' => true,
            'kind' => 'custom',
        ];
    }

    public function resolveCheckoutSelection(string $slug, mixed $amount = null): ?array
    {
        if ($slug === 'custom') {
            $normalizedAmount = $this->normalizeAmount($amount);

            if ($normalizedAmount === null) {
                return null;
            }

            return $this->buildCustomDeposit($normalizedAmount);
        }

        return $this->getPackageBySlug($slug);
    }

    /**
     * Fontes operacionais que compõem o produto Compliance.
     */
    public function getComplianceSources(): array
    {
        return [
            [
                'slug' => 'minha_receita',
                'nome' => 'Cadastro RFB (minhareceita.org)',
                'categoria' => 'Cadastral',
                'status' => 'ativo',
                'descricao_curta' => 'Situação cadastral, CNAEs, QSA, regime tributário.',
            ],
            [
                'slug' => 'cnd_federal',
                'nome' => 'CND Federal (PGFN/RFB)',
                'categoria' => 'Fiscal obrigatória',
                'status' => 'ativo',
                'descricao_curta' => 'Certidão Negativa de Débitos Federais e Dívida Ativa da União.',
            ],
            [
                'slug' => 'cnd_estadual',
                'nome' => 'CND Estadual (SEFAZ)',
                'categoria' => 'Fiscal obrigatória',
                'status' => 'ativo',
                'descricao_curta' => 'Certidão estadual nas 27 UFs via SEFAZ.',
            ],
            [
                'slug' => 'cnd_municipal',
                'nome' => 'CND Municipal (Prefeituras)',
                'categoria' => 'Fiscal obrigatória',
                'status' => 'ativo',
                'descricao_curta' => 'Certidão municipal por cidade do participante.',
            ],
            [
                'slug' => 'cndt',
                'nome' => 'CNDT (TST)',
                'categoria' => 'Trabalhista obrigatória',
                'status' => 'ativo',
                'descricao_curta' => 'Certidão Negativa de Débitos Trabalhistas — exigida em licitação.',
            ],
            [
                'slug' => 'crf_fgts',
                'nome' => 'CRF FGTS (Caixa)',
                'categoria' => 'FGTS obrigatória',
                'status' => 'ativo',
                'descricao_curta' => 'Certificado de Regularidade do FGTS.',
            ],
            [
                'slug' => 'sintegra',
                'nome' => 'SINTEGRA unificada',
                'categoria' => 'Cadastral estadual',
                'status' => 'ativo',
                'descricao_curta' => 'Inscrição estadual ativa — protege crédito de ICMS.',
            ],
        ];
    }

    /**
     * Catálogo comercial público dos produtos de consulta CNPJ ativos.
     *
     * A composição e a descrição vêm do PlanoCatalog para a landing não criar uma
     * segunda fonte de verdade. Due Diligence e Enterprise permanecem fora porque
     * estão inativos; Clearance tem catálogo e feature flags próprios.
     */
    public function getProductCatalog(): array
    {
        return collect(PlanoCatalog::definitions())
            ->filter(fn (array $plano) => ($plano['is_active'] ?? false) === true)
            ->map(fn (array $plano) => [
                'slug' => $plano['codigo'],
                'nome' => $plano['nome'],
                'descricao' => $plano['descricao'],
                'consultas_incluidas' => $plano['consultas_incluidas'],
                'is_gratuito' => $plano['is_gratuito'],
                'ordem' => $plano['ordem'],
            ])
            ->sortBy('ordem')
            ->values()
            ->all();
    }

    /**
     * Preço em reais para exibir e cobrar um produto de consulta.
     * Override admin opcional via comercial_parametros; default = custo do PlanoCatalog (R$).
     */
    public function getProductPriceByPlan(MonitoramentoPlano $plan): float
    {
        return round((float) $this->comercial->valor('preco_'.$plan->codigo, (float) $plan->custo_creditos), 2);
    }

    /**
     * Total em R$ que o usuário já comprou (compras menos estornos de compra).
     */
    public function getPaidCreditsForUser(User $user): float
    {
        $purchased = (float) SaldoTransacao::query()
            ->where('user_id', $user->id)
            ->where('type', 'purchase')
            ->sum('amount');

        $refunded = abs((float) SaldoTransacao::query()
            ->where('user_id', $user->id)
            ->where('type', 'purchase_refund')
            ->sum('amount'));

        return round(max(0, $purchased - $refunded), 2);
    }

    /**
     * Retorna os dados de pricing para a landing page.
     * Cada produto tem {slug, nome, descricao, price} — sem matriz de faixas.
     */
    public function getLandingPricingData(): array
    {
        $featuredOffers = array_map(fn (array $package) => $this->decorateOffer($package), $this->getFeaturedOffers());

        $products = array_map(function (array $product) {
            $plano = MonitoramentoPlano::where('codigo', $product['slug'])->first();
            $price = $plano ? $this->getProductPriceByPlan($plano) : 0.0;

            return [
                'slug' => $product['slug'],
                'nome' => $product['nome'],
                'descricao' => $product['descricao'],
                'consultas_incluidas' => $product['consultas_incluidas'],
                'is_gratuito' => $product['is_gratuito'],
                'price' => $price,
                'price_label' => \App\Support\Dinheiro::brl($price).'/consulta',
                'price_for_100' => $price * 100,
            ];
        }, $this->getProductCatalog());

        return [
            'minimum_deposit' => $this->getMinimumDeposit(),
            'featured_offers' => $featuredOffers,
            'packages' => $featuredOffers,
            'products' => $products,
            'compliance_sources' => $this->getComplianceSources(),
        ];
    }

    /**
     * Retorna o resumo comercial do usuário para as views autenticadas.
     * Preço único por produto em R$, sem faixas de volume.
     */
    public function getCommercialSummaryForUser(User $user): array
    {
        $featuredOffers = array_map(fn (array $package) => $this->decorateOffer($package), $this->getFeaturedOffers());

        $products = array_map(function (array $product) {
            $plano = MonitoramentoPlano::where('codigo', $product['slug'])->first();
            $price = $plano ? $this->getProductPriceByPlan($plano) : 0.0;

            return [
                'slug' => $product['slug'],
                'nome' => $product['nome'],
                'descricao' => $product['descricao'],
                'price' => $price,
                'price_label' => \App\Support\Dinheiro::brl($price).'/consulta',
            ];
        }, $this->getProductCatalog());

        return [
            'minimum_deposit' => $this->getMinimumDeposit(),
            'featured_offers' => $featuredOffers,
            'packages' => $featuredOffers,
            'products' => $products,
        ];
    }

    private function decorateOffer(array $package): array
    {
        $package['is_custom'] = false;
        $package['kind'] = 'featured';

        return $package;
    }

    private function normalizeAmount(mixed $amount): ?float
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        if (is_string($amount)) {
            $amount = str_replace(',', '.', trim($amount));
        }

        if (! is_numeric($amount)) {
            return null;
        }

        return round($amount, 2);
    }
}
