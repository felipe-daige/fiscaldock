<?php

namespace App\Services\Subscription;

use App\Models\Account;
use App\Models\AccountSubscription;
use App\Models\User;
use App\Notifications\AddonSuspensoNotification;
use App\Services\Accounts\AccountService;
use App\Services\Admin\ComercialParametroService;
use App\Services\Entitlements\EntitlementService;
use App\Services\SaldoService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Add-ons recorrentes cobrados do saldo pré-pago em R$ (nunca do preapproval MP):
 * assento extra (por plano) e pacote de espaço adicional (config + override comercial).
 *
 * - Compra self-service (owner): debita pró-rata do ciclo corrente na hora e incrementa o
 *   campo na MESMA transação. Saldo insuficiente → RuntimeException, nada muda.
 * - Renovação: cobrada por ConcederSaldoService no fim do grant mensal (rollover), nunca na
 *   proration de troca (evita cobrança dupla). Falha de débito ZERA o add-on (sem apagar
 *   membros/arquivos) e notifica.
 * - Redução/cancelamento: efeito imediato, sem reembolso da fração já paga.
 */
class AddonService
{
    public function __construct(
        private SaldoService $saldo = new SaldoService,
        private EntitlementService $entitlements = new EntitlementService,
        private AccountService $accounts = new AccountService,
        private ComercialParametroService $comercial = new ComercialParametroService,
    ) {}

    /** Preço mensal de 1 assento extra, em R$ (0 = cortesia do plano). */
    public function precoAssentoReais(User $owner): float
    {
        $plan = $this->entitlements->planFor($owner);

        return round(((int) $plan->preco_assento_extra_centavos) / 100, 2);
    }

    /** Preço mensal de 1 pacote de espaço adicional, em R$ (override comercial > config). */
    public function precoPacoteEspacoReais(): float
    {
        $centavos = (int) $this->comercial->valor(
            'arquivos_pacote_extra_preco_centavos',
            (int) config('arquivos.pacote_extra.preco_centavos', 1990),
        );

        return round($centavos / 100, 2);
    }

    /** Tamanho de 1 pacote de espaço adicional, em MB (override comercial > config). */
    public function pacoteEspacoMb(): int
    {
        return (int) $this->comercial->valor(
            'arquivos_pacote_extra_mb',
            (int) config('arquivos.pacote_extra.mb', 5 * 1024),
        );
    }

    // ── Compra / redução ──────────────────────────────────────────────────────

    /** Define o total de assentos extras da conta (owner). Cobra pró-rata o incremento. */
    public function definirAssentosExtras(User $user, int $alvo): void
    {
        $owner = $user->accountOwner();
        $alvo = max(0, $alvo);

        // Tudo sob lock da assinatura pra evitar lost-update (dois cliques cobrariam 2× e
        // gravariam 1). deduct faz seu próprio lock do saldo — os dois compõem atomicamente.
        DB::transaction(function () use ($owner, $alvo) {
            $sub = $this->assinaturaAtivaLocked($owner);
            $atual = (int) $sub->assentos_extras;

            if ($alvo === $atual) {
                return;
            }
            if ($alvo < $atual) {
                $this->assertReducaoAssentosPermitida($owner, $sub, $alvo);
                $sub->assentos_extras = $alvo;
                $sub->save();

                return;
            }

            $delta = $alvo - $atual;
            $this->cobrarProRata($owner, $sub, $this->precoAssentoReais($owner) * $delta, $delta.' assento(s) extra(s)');
            $sub->assentos_extras = $alvo;
            $sub->save();
        });
    }

    /** Define o total de pacotes de espaço extra da conta (owner). Cobra pró-rata o incremento. */
    public function definirEspacoExtraPacotes(User $user, int $alvo): void
    {
        $owner = $user->accountOwner();
        $alvo = max(0, $alvo);

        DB::transaction(function () use ($owner, $alvo) {
            $sub = $this->assinaturaAtivaLocked($owner);
            $atual = (int) $sub->espaco_extra_pacotes;

            if ($alvo === $atual) {
                return;
            }
            if ($alvo < $atual) {
                // Reduzir espaço é sempre permitido: nada se apaga, upload novo acima da quota trava.
                $sub->espaco_extra_pacotes = $alvo;
                $sub->save();

                return;
            }

            $delta = $alvo - $atual;
            $this->cobrarProRata($owner, $sub, $this->precoPacoteEspacoReais() * $delta, $delta.' pacote(s) de espaço adicional');
            $sub->espaco_extra_pacotes = $alvo;
            $sub->save();
        });
    }

    // ── Renovação (chamada por ConcederSaldoService no grant mensal) ───────────

    /** Debita a mensalidade dos add-ons ativos. Falha por tipo ZERA aquele add-on + notifica. */
    public function cobrarRenovacaoAddons(AccountSubscription $sub): void
    {
        $owner = User::find($sub->user_id);
        if (! $owner) {
            return;
        }

        // Preço do plano DESTE sub (não re-consulta via planFor: o plano já vem no sub).
        $precoAssento = round(((int) ($sub->plan?->preco_assento_extra_centavos ?? 0)) / 100, 2);

        $extras = (int) $sub->assentos_extras;
        if ($extras > 0) {
            $valor = round($precoAssento * $extras, 2);
            if ($valor > 0 && ! $this->saldo->deduct($owner, $valor, 'addon_renewal', "Renovação mensal: {$extras} assento(s) extra(s).", $sub)) {
                $sub->assentos_extras = 0;
                $sub->save();
                $owner->notify(new AddonSuspensoNotification('assento extra'));
            }
        }

        $pacotes = (int) $sub->espaco_extra_pacotes;
        if ($pacotes > 0) {
            $valor = round($this->precoPacoteEspacoReais() * $pacotes, 2);
            if ($valor > 0 && ! $this->saldo->deduct($owner, $valor, 'addon_renewal', "Renovação mensal: {$pacotes} pacote(s) de espaço adicional.", $sub)) {
                $sub->espaco_extra_pacotes = 0;
                $sub->save();
                $owner->notify(new AddonSuspensoNotification('espaço adicional'));
            }
        }
    }

    // ── Internos ──────────────────────────────────────────────────────────────

    /**
     * Fração [0..1] do ciclo corrente ainda não decorrida — proporção que a compra paga hoje.
     * Sem âncora de ciclo (recém-ativado) → 1.0 (cobra o mês cheio).
     */
    public function fracaoRestante(AccountSubscription $sub): float
    {
        $inicio = $sub->ultimo_grant_em ?? $sub->iniciada_em;
        $fim = $sub->proximo_grant_em;
        if ($inicio === null || $fim === null) {
            return 1.0;
        }

        $inicio = Carbon::parse($inicio);
        $fim = Carbon::parse($fim);
        $total = $inicio->diffInSeconds($fim);
        if ($total <= 0) {
            return 1.0;
        }

        $restante = now()->diffInSeconds($fim, false);

        return max(0.0, min(1.0, $restante / $total));
    }

    /** Debita a fração corrente do valor mensal. Chamado DENTRO da transação com o sub travado. */
    private function cobrarProRata(User $owner, AccountSubscription $sub, float $valorMensalCheio, string $rotulo): void
    {
        $valor = round($valorMensalCheio * $this->fracaoRestante($sub), 2);
        if ($valor > 0 && ! $this->saldo->deduct($owner, $valor, 'addon_purchase', "Contratação pró-rata: {$rotulo}.", $sub)) {
            throw new RuntimeException('Saldo insuficiente para contratar '.$rotulo.'. Adicione saldo e tente novamente.');
        }
    }

    private function assinaturaAtivaLocked(User $owner): AccountSubscription
    {
        $sub = $owner->subscription()
            ->where('status', AccountSubscription::STATUS_ATIVA)
            ->lockForUpdate()
            ->first();
        if (! $sub) {
            throw new RuntimeException('Add-ons exigem uma assinatura ativa. Faça upgrade de plano primeiro.');
        }

        return $sub;
    }

    /** Não deixa reduzir assentos abaixo do que já está ocupado (membros + convites). */
    private function assertReducaoAssentosPermitida(User $owner, AccountSubscription $sub, int $alvoExtras): void
    {
        $account = $owner->ownedAccount()->first();
        if (! $account instanceof Account) {
            return;
        }

        $plan = $this->entitlements->planFor($owner);
        $capacidadeAlvo = max(1, (int) $plan->assentos_inclusos + $alvoExtras);
        $ocupados = $this->accounts->seatsUsed($account);

        if ($ocupados > $capacidadeAlvo) {
            throw new RuntimeException(
                "Remova membros ou convites antes de reduzir assentos: {$ocupados} em uso, "
                ."capacidade alvo de {$capacidadeAlvo}."
            );
        }
    }
}
