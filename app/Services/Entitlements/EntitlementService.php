<?php

namespace App\Services\Entitlements;

use App\Models\SubscriptionPlan;
use App\Models\User;

class EntitlementService
{
    /** Capabilities tratadas como booleanas por can(). */
    private const BOOLEAN_CAPS = ['pdf_executivo', 'clearance_lote', 'clearance_full', 'score_historico'];

    public function planFor(User $user): SubscriptionPlan
    {
        $subscription = $user->relationLoaded('subscription')
            ? $user->subscription
            : $user->subscription()->with('plan')->first();

        return $subscription?->plan ?? SubscriptionPlan::free();
    }

    public function can(User $user, string $cap): bool
    {
        return $this->planFor($user)->capability($cap, false) === true;
    }

    public function capability(User $user, string $key, mixed $default = null): mixed
    {
        return $this->planFor($user)->capability($key, $default);
    }

    /** @return array<int, string> */
    public function exportFormats(User $user): array
    {
        $formats = $this->capability($user, 'export', []);

        return is_array($formats) ? $formats : [];
    }

    public function limit(User $user, string $key): ?int
    {
        $value = $this->planFor($user)->{$key};

        return $value === null ? null : (int) $value;
    }

    public function faixaFor(User $user): string
    {
        return $this->planFor($user)->faixa_slug;
    }

    public function consumptionCap(User $user): int
    {
        $subscription = $user->relationLoaded('subscription')
            ? $user->subscription
            : $user->subscription()->first();

        if ($subscription !== null && $subscription->limite_consumo_automatico !== null) {
            return (int) $subscription->limite_consumo_automatico;
        }

        return (int) $this->planFor($user)->creditos_inclusos;
    }
}
