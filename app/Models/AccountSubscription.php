<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountSubscription extends Model
{
    public const STATUS_PENDENTE = 'pendente';

    public const STATUS_ATIVA = 'ativa';

    public const STATUS_INADIMPLENTE = 'inadimplente';

    public const STATUS_CANCELADA = 'cancelada';

    protected $table = 'account_subscriptions';

    protected $fillable = [
        'user_id', 'subscription_plan_id', 'status', 'ciclo', 'iniciada_em',
        'renova_em', 'creditos_inclusos_saldo', 'limite_consumo_automatico', 'assentos_extras',
        'mp_preapproval_id', 'proximo_grant_em', 'ultimo_grant_em', 'proration_pendente',
    ];

    protected $casts = [
        'iniciada_em' => 'datetime',
        'renova_em' => 'datetime',
        'creditos_inclusos_saldo' => 'integer',
        'limite_consumo_automatico' => 'integer',
        'assentos_extras' => 'integer',
        'proximo_grant_em' => 'datetime',
        'ultimo_grant_em' => 'datetime',
        'proration_pendente' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function pagamentos(): HasMany
    {
        return $this->hasMany(MercadoPagoPayment::class);
    }
}
