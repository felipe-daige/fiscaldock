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
        'rollover_cap_multiplicador', 'capabilities', 'is_active', 'ordem',
    ];

    protected $casts = [
        'preco_mensal_centavos' => 'integer',
        'preco_anual_centavos' => 'integer',
        'creditos_inclusos' => 'integer',
        'limite_clientes' => 'integer',
        'limite_cnpjs_monitorados' => 'integer',
        'frequencia_padrao_dias' => 'integer',
        'assentos_inclusos' => 'integer',
        'rollover_cap_multiplicador' => 'float',
        'capabilities' => 'array',
        'is_active' => 'boolean',
        'ordem' => 'integer',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(AccountSubscription::class);
    }

    /** Plano padrão (Free) — fallback quando a conta não tem assinatura. */
    public static function free(): self
    {
        return static::where('codigo', 'free')->firstOrFail();
    }

    public function capability(string $key, mixed $default = null): mixed
    {
        return $this->capabilities[$key] ?? $default;
    }
}
