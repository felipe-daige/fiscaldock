<?php

namespace App\Support;

use App\Models\Account;
use App\Models\AccountMember;
use App\Models\User;

class AccountContext
{
    public function __construct(
        private readonly User $actor,
        private readonly User $owner,
        private readonly Account $account,
        private readonly AccountMember $membership,
    ) {}

    public function actor(): User
    {
        return $this->actor;
    }

    public function owner(): User
    {
        return $this->owner;
    }

    public function account(): Account
    {
        return $this->account;
    }

    public function membership(): AccountMember
    {
        return $this->membership;
    }

    public function isOwner(): bool
    {
        return $this->membership->isOwner();
    }

    public function canManageTeam(): bool
    {
        return $this->membership->isAdmin();
    }

    public function permits(string $module): bool
    {
        return $this->membership->permits($module);
    }

    public function isReadOnly(): bool
    {
        return $this->membership->isReadOnly();
    }
}
