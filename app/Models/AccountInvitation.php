<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountInvitation extends Model
{
    protected $fillable = [
        'account_id', 'email', 'papel', 'permissoes', 'token_hash', 'convidado_por',
        'expira_em', 'aceito_em', 'revogado_em',
    ];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return [
            'permissoes' => 'array',
            'expira_em' => 'datetime',
            'aceito_em' => 'datetime',
            'revogado_em' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'convidado_por');
    }

    public function isPending(): bool
    {
        return $this->aceito_em === null
            && $this->revogado_em === null
            && $this->expira_em?->isFuture();
    }
}
