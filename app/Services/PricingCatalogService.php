<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\MonitoramentoPlano;
use App\Models\User;

class PricingCatalogService
{
    public const CREDIT_UNIT_PRICE = 0.20;

    /**
     * Pacotes avulsos de compra.
     */
    public function getPackages(): array
    {
        return [
            [
                'slug' => 'starter',
                'nome' => 'Starter',
                'creditos' => 100,
                'preco' => 20.00,
                'descricao' => 'Pacote de entrada para validar o fluxo e iniciar as primeiras consultas.',
            ],
            [
                'slug' => 'growth',
                'nome' => 'Growth',
                'creditos' => 500,
                'preco' => 100.00,
                'descricao' => 'Bom ponto de entrada para escritórios com rotina recorrente de consultas.',
            ],
            [
                'slug' => 'business',
                'nome' => 'Business',
                'creditos' => 1000,
                'preco' => 200.00,
                'descricao' => 'Faixa Base de 100 consultas de compliance por R$ 200.',
            ],
            [
                'slug' => 'enterprise',
                'nome' => 'Enterprise',
                'creditos' => 5000,
                'preco' => 1000.00,
                'descricao' => 'Escala o saldo e acelera a chegada às faixas com melhor economia.',
            ],
        ];
    }

    public function getPackageBySlug(string $slug): ?array
    {
        foreach ($this->getPackages() as $package) {
            if ($package['slug'] === $slug) {
                return $package;
            }
        }

        return null;
    }

    /**
     * Faixas comerciais por histórico líquido de créditos pagos.
     */
    public function getTiers(): array
    {
        return [
            [
                'slug' => 'base',
                'nome' => 'Base',
                'min_paid_credits' => 0,
                'max_paid_credits' => 999,
            ],
            [
                'slug' => 'x',
                'nome' => 'Faixa X',
                'min_paid_credits' => 1000,
                'max_paid_credits' => 4999,
            ],
            [
                'slug' => 'y',
                'nome' => 'Faixa Y',
                'min_paid_credits' => 5000,
                'max_paid_credits' => 19999,
            ],
            [
                'slug' => 'z',
                'nome' => 'Faixa Z',
                'min_paid_credits' => 20000,
                'max_paid_credits' => null,
            ],
        ];
    }

    /**
     * Catálogo comercial público.
     */
    public function getProductCatalog(): array
    {
        return [
            [
                'slug' => 'compliance',
                'nome' => 'Compliance',
                'descricao' => 'Consulta premium de regularidade fiscal por CNPJ, incluindo o pacote de certidões e checagens que sustentam a oferta de compliance.',
                'credits_by_tier' => [
                    'base' => 10,
                    'x' => 9,
                    'y' => 8,
                    'z' => 7,
                ],
            ],
            [
                'slug' => 'clearance',
                'nome' => 'Clearance',
                'descricao' => 'Validação premium de notas fiscais com custo mais alto por consulta, preservando o posicionamento premium do produto.',
                'credits_by_tier' => [
                    'base' => 14,
                    'x' => 12,
                    'y' => 10,
                    'z' => 8,
                ],
            ],
        ];
    }

    public function getLandingPricingData(): array
    {
        $tiers = $this->getTiers();
        $products = array_map(function (array $product) use ($tiers) {
            $rows = [];
            foreach ($tiers as $tier) {
                $credits = $product['credits_by_tier'][$tier['slug']];
                $rows[] = [
                    'tier_slug' => $tier['slug'],
                    'tier_name' => $tier['nome'],
                    'credits' => $credits,
                    'price' => $this->creditsToCurrency($credits),
                    'price_for_100' => $this->creditsToCurrency($credits * 100),
                ];
            }

            return array_merge($product, ['rows' => $rows]);
        }, $this->getProductCatalog());

        return [
            'credit_unit_price' => self::CREDIT_UNIT_PRICE,
            'packages' => $this->getPackages(),
            'tiers' => $tiers,
            'products' => $products,
        ];
    }

    public function getPaidCreditsForUser(User $user): int
    {
        $purchased = (int) CreditTransaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'purchase')
            ->sum('amount');

        $refunded = (int) abs(CreditTransaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'purchase_refund')
            ->sum('amount'));

        return max(0, $purchased - $refunded);
    }

    public function getTierForUser(User $user): array
    {
        return $this->getTierForPaidCredits($this->getPaidCreditsForUser($user));
    }

    public function getTierForPaidCredits(int $paidCredits): array
    {
        foreach (array_reverse($this->getTiers()) as $tier) {
            if ($paidCredits >= $tier['min_paid_credits']) {
                return $tier;
            }
        }

        return $this->getTiers()[0];
    }

    public function getNextTierForUser(User $user): ?array
    {
        return $this->getNextTierForPaidCredits($this->getPaidCreditsForUser($user));
    }

    public function getNextTierForPaidCredits(int $paidCredits): ?array
    {
        foreach ($this->getTiers() as $tier) {
            if ($tier['min_paid_credits'] > $paidCredits) {
                return $tier;
            }
        }

        return null;
    }

    public function getTierProgressForUser(User $user): array
    {
        $paidCredits = $this->getPaidCreditsForUser($user);
        $currentTier = $this->getTierForPaidCredits($paidCredits);
        $nextTier = $this->getNextTierForPaidCredits($paidCredits);

        if ($nextTier === null) {
            return [
                'paid_credits' => $paidCredits,
                'current_tier' => $currentTier,
                'next_tier' => null,
                'credits_remaining' => 0,
                'progress_percent' => 100,
            ];
        }

        $rangeStart = $currentTier['min_paid_credits'];
        $rangeEnd = $nextTier['min_paid_credits'];
        $creditsRemaining = max(0, $rangeEnd - $paidCredits);
        $progressPercent = (int) min(
            100,
            max(0, (($paidCredits - $rangeStart) / max(1, ($rangeEnd - $rangeStart))) * 100)
        );

        return [
            'paid_credits' => $paidCredits,
            'current_tier' => $currentTier,
            'next_tier' => $nextTier,
            'credits_remaining' => $creditsRemaining,
            'progress_percent' => $progressPercent,
        ];
    }

    public function getProductCreditsForUser(string $productSlug, User $user, ?MonitoramentoPlano $legacyPlan = null): int
    {
        $currentTier = $this->getTierForUser($user);

        foreach ($this->getProductCatalog() as $product) {
            if ($product['slug'] === $productSlug) {
                return (int) $product['credits_by_tier'][$currentTier['slug']];
            }
        }

        return (int) ($legacyPlan?->custo_creditos ?? 0);
    }

    public function getProductCreditsByPlan(MonitoramentoPlano $plan, User $user): int
    {
        $mappedProduct = match ($plan->codigo) {
            'compliance' => 'compliance',
            'clearance' => 'clearance',
            default => null,
        };

        if ($mappedProduct === null) {
            return (int) $plan->custo_creditos;
        }

        return $this->getProductCreditsForUser($mappedProduct, $user, $plan);
    }

    public function getCommercialSummaryForUser(User $user): array
    {
        $progress = $this->getTierProgressForUser($user);
        $tier = $progress['current_tier'];

        $products = array_map(function (array $product) use ($tier) {
            $credits = $product['credits_by_tier'][$tier['slug']];

            return [
                'slug' => $product['slug'],
                'nome' => $product['nome'],
                'descricao' => $product['descricao'],
                'credits' => $credits,
                'price' => $this->creditsToCurrency($credits),
            ];
        }, $this->getProductCatalog());

        return array_merge($progress, [
            'credit_unit_price' => self::CREDIT_UNIT_PRICE,
            'products' => $products,
            'packages' => $this->getPackages(),
        ]);
    }

    public function creditsToCurrency(int $credits): float
    {
        return round($credits * self::CREDIT_UNIT_PRICE, 2);
    }
}
