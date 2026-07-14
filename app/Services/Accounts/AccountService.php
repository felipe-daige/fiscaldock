<?php

namespace App\Services\Accounts;

use App\Models\Account;
use App\Models\AccountActivityLog;
use App\Models\AccountInvitation;
use App\Models\AccountMember;
use App\Models\AccountSubscription;
use App\Models\User;
use App\Services\Entitlements\EntitlementService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AccountService
{
    public function ensureForOwner(User $user): AccountMember
    {
        $existing = AccountMember::with('account.owner')->where('user_id', $user->id)->first();
        if ($existing !== null) {
            return $existing;
        }

        return DB::transaction(function () use ($user) {
            $locked = User::lockForUpdate()->findOrFail($user->id);
            $existing = AccountMember::with('account.owner')->where('user_id', $locked->id)->first();
            if ($existing !== null) {
                return $existing;
            }

            $account = Account::firstOrCreate(
                ['owner_user_id' => $locked->id],
                ['nome' => $locked->empresa ?: trim($locked->name.' '.$locked->sobrenome)],
            );

            return AccountMember::create([
                'account_id' => $account->id,
                'user_id' => $locked->id,
                'papel' => AccountMember::PAPEL_OWNER,
                'permissoes' => AccountMember::permissoesPadrao(AccountMember::PAPEL_OWNER),
                'entrou_em' => now(),
            ])->load('account.owner');
        });
    }

    public function seatsIncluded(Account $account): int
    {
        $owner = $account->owner;
        $plan = app(EntitlementService::class)->planFor($owner);
        $subscription = $owner->subscription;
        $extras = $subscription?->status === AccountSubscription::STATUS_ATIVA
            ? (int) $subscription->assentos_extras
            : 0;

        return max(1, (int) $plan->assentos_inclusos + $extras);
    }

    public function seatsUsed(Account $account): int
    {
        $members = $account->members()->count();
        $pending = $account->invitations()
            ->whereNull('aceito_em')
            ->whereNull('revogado_em')
            ->where('expira_em', '>', now())
            ->count();

        return $members + $pending;
    }

    public function assertHasSeat(Account $account): void
    {
        if ($this->seatsUsed($account) >= $this->seatsIncluded($account)) {
            throw new RuntimeException('Todos os assentos da conta estão ocupados. Faça upgrade ou contrate assentos extras.');
        }
    }

    /** @param array<string,mixed> $detalhes */
    public function log(Account $account, ?User $actor, string $acao, mixed $subject = null, array $detalhes = [], ?string $ip = null): void
    {
        AccountActivityLog::create([
            'account_id' => $account->id,
            'actor_user_id' => $actor?->id,
            'acao' => $acao,
            'subject_type' => is_object($subject) ? get_class($subject) : null,
            'subject_id' => is_object($subject) && isset($subject->id) ? $subject->id : null,
            'detalhes' => $detalhes ?: null,
            'ip' => $ip,
            'created_at' => now(),
        ]);
    }

    public function revokePendingInvitationsForEmail(Account $account, string $email): void
    {
        AccountInvitation::where('account_id', $account->id)
            ->whereRaw('lower(email) = ?', [mb_strtolower(trim($email))])
            ->whereNull('aceito_em')
            ->whereNull('revogado_em')
            ->update(['revogado_em' => now()]);
    }
}
