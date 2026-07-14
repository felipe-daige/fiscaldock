<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = ['owner_user_id', 'nome'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(AccountMember::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(AccountInvitation::class);
    }

    public function activeInvitations(): HasMany
    {
        return $this->invitations()
            ->whereNull('aceito_em')
            ->whereNull('revogado_em')
            ->where('expira_em', '>', now());
    }
}
