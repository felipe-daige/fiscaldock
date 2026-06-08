<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountSubscription extends Model
{
    protected $table = 'account_subscriptions';

    protected $fillable = [
        'user_id', 'subscription_plan_id', 'status', 'ciclo', 'iniciada_em',
        'renova_em', 'creditos_inclusos_saldo', 'limite_consumo_automatico', 'assentos_extras',
    ];

    protected $casts = [
        'iniciada_em' => 'datetime',
        'renova_em' => 'datetime',
        'creditos_inclusos_saldo' => 'integer',
        'limite_consumo_automatico' => 'integer',
        'assentos_extras' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }
}
