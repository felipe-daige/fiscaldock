<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaldoTransacao extends Model
{
    protected $table = 'credit_transactions';

    protected $fillable = [
        'user_id',
        'amount',
        'balance_after',
        'type',
        'description',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'amount' => 'float',
        'balance_after' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function source()
    {
        return $this->morphTo();
    }
}
