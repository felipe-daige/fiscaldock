<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $table = 'subscription_plans';

    protected $fillable = [
        'codigo', 'nome', 'preco_mensal_centavos', 'preco_anual_centavos',
        'creditos_inclusos', 'faixa_slug', 'limite_clientes', 'limite_cnpjs_monitorados',
        'frequencia_padrao_dias', 'profundidade_auto_monitor', 'assentos_inclusos',
        'preco_assento_extra_centavos', 'rollover_cap_multiplicador', 'capabilities', 'is_active', 'ordem',
        'mp_preapproval_plan_id_mensal', 'mp_preapproval_plan_id_anual',
    ];

    protected $casts = [
        'preco_mensal_centavos' => 'integer',
        'preco_anual_centavos' => 'integer',
        'creditos_inclusos' => 'float',
        'limite_clientes' => 'integer',
        'limite_cnpjs_monitorados' => 'integer',
        'frequencia_padrao_dias' => 'integer',
        'assentos_inclusos' => 'integer',
        'preco_assento_extra_centavos' => 'integer',
        'rollover_cap_multiplicador' => 'float',
        'capabilities' => 'array',
        'is_active' => 'boolean',
        'ordem' => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(AccountSubscription::class);
    }

    /**
     * Plano padrão (Free) — fallback quando a conta não tem assinatura.
     *
     * Resiliente: se a linha 'free' não existir no banco (ex.: seed ausente),
     * devolve um Free em memória a partir da definição canônica do seeder, em vez
     * de estourar ModelNotFoundException nos hot paths (getTierForUser/planFor).
     */
    public static function free(): self
    {
        $plan = static::where('codigo', 'free')->first();

        if ($plan !== null) {
            return $plan;
        }

        $definicao = collect(\Database\Seeders\SubscriptionPlanSeeder::definitions())
            ->firstWhere('codigo', 'free');

        return new self($definicao);
    }

    /**
     * Planos ativos ordenados — resiliente igual ao free(): se a tabela estiver
     * vazia (seed ausente em prod), devolve os tiers em memória a partir da
     * definição canônica do seeder, pra UI nunca renderizar vazio.
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function allActive(): \Illuminate\Support\Collection
    {
        $rows = static::where('is_active', true)->orderBy('ordem')->get();

        if ($rows->isNotEmpty()) {
            return $rows->toBase();
        }

        return collect(\Database\Seeders\SubscriptionPlanSeeder::definitions())
            ->filter(fn ($def) => ($def['is_active'] ?? true) === true)
            ->map(fn ($def) => new self($def))
            ->sortBy('ordem')
            ->values();
    }

    public function capability(string $key, mixed $default = null): mixed
    {
        return $this->capabilities[$key] ?? $default;
    }

    public function precoCentavos(string $ciclo): int
    {
        return $ciclo === 'anual' ? (int) $this->preco_anual_centavos : (int) $this->preco_mensal_centavos;
    }

    public function mpPlanId(string $ciclo): ?string
    {
        return $ciclo === 'anual' ? $this->mp_preapproval_plan_id_anual : $this->mp_preapproval_plan_id_mensal;
    }
}
