<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'account_id', 'actor_user_id', 'acao', 'subject_type', 'subject_id',
        'detalhes', 'ip', 'created_at',
    ];

    protected function casts(): array
    {
        return ['detalhes' => 'array', 'created_at' => 'datetime'];
    }
}
