<?php

namespace App\Actions\MercadoPago;

use App\Models\AccountSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\MercadoPago\MercadoPagoClient;
use RuntimeException;
use Throwable;

/**
 * Troca o plano da assinatura atual (upgrade/downgrade ou mudança de ciclo).
 *
 * O Mercado Pago não permite mudar o preapproval_plan de um preapproval existente —
 * cada tier tem seu próprio `mp_preapproval_plan_id`. Trocar de plano é, portanto,
 * "criar a nova preapproval + cancelar a antiga", nesta ordem:
 *
 *  1. Cria a preapproval do plano-destino (reusa a MESMA linha via `CriarAssinaturaMercadoPago`,
 *     que valida teto/plano/ciclo e persiste pendente). Falha aqui = restaura o estado anterior
 *     da linha e propaga o erro (não deixa o usuário sem assinatura local).
 *  2. Só depois de a nova nascer, cancela a preapproval ANTIGA no MP (best-effort). Ordem
 *     importa: se cancelássemos antes e a criação falhasse, o usuário ficaria sem cobrança.
 *
 * Sem assinatura viva → delega direto pro fluxo de assinatura nova.
 * Saldo já concedido é preservado (mesmo guardrail do cancelamento).
 */
class TrocarPlanoMercadoPago
{
    public function __construct(
        private CriarAssinaturaMercadoPago $criar = new CriarAssinaturaMercadoPago,
        private MercadoPagoClient $client = new MercadoPagoClient,
    ) {}

    public function execute(User $user, string $codigoPlano, string $ciclo, string $cardToken): AccountSubscription
    {
        $ciclo = $ciclo === 'anual' ? 'anual' : 'mensal';

        $atual = AccountSubscription::where('user_id', $user->id)
            ->whereIn('status', [
                AccountSubscription::STATUS_ATIVA,
                AccountSubscription::STATUS_INADIMPLENTE,
                AccountSubscription::STATUS_PENDENTE,
            ])
            ->first();

        // Sem assinatura viva → é uma assinatura nova, não uma troca.
        if ($atual === null) {
            return $this->criar->execute($user, $codigoPlano, $ciclo, $cardToken);
        }

        $destino = SubscriptionPlan::where('codigo', $codigoPlano)->first();
        if ($destino !== null
            && $destino->id === $atual->subscription_plan_id
            && $atual->ciclo === $ciclo) {
            throw new RuntimeException('Você já está neste plano e ciclo.');
        }

        // Snapshot pra restaurar caso a criação da nova preapproval falhe.
        $snapshot = $atual->only(['subscription_plan_id', 'status', 'ciclo', 'mp_preapproval_id', 'proration_pendente']);
        $preapprovalAntigo = $atual->mp_preapproval_id;

        // Proration de saldo: registra a fração do ciclo corrente ainda não
        // consumida. A concessão do tier destino (no webhook de ativação) usa esse marker pra
        // expirar o incluso antigo pro-rata e conceder o novo pro-rata. Sem risco de saldo
        // prematuro — nada é movimentado aqui, só o marker é persistido.
        $fracaoRestante = $this->fracaoCicloRestante($atual);
        if ($fracaoRestante > 0) {
            $atual->update(['proration_pendente' => [
                'fracao_restante' => $fracaoRestante,
                'origem_plano_id' => $atual->subscription_plan_id,
                'trocado_em' => now()->toIso8601String(),
            ]]);
        }

        try {
            $novo = $this->criar->execute($user, $codigoPlano, $ciclo, $cardToken);
        } catch (Throwable $e) {
            // Desfaz a mutação parcial que o Criar possa ter feito na linha.
            $atual->fresh()?->update($snapshot);
            throw $e;
        }

        // Nova preapproval nasceu — cancela a antiga no MP. Best-effort: id já
        // cancelado/expirado não deve quebrar a troca (a nova já é a vigente).
        if ($preapprovalAntigo !== null && $preapprovalAntigo !== $novo->mp_preapproval_id) {
            try {
                $this->client->cancelarPreapproval($preapprovalAntigo);
            } catch (Throwable $e) {
                report($e);
            }
        }

        return $novo;
    }

    /**
     * Fração [0..1] do ciclo de concessão corrente ainda NÃO consumida no momento da troca.
     * Janela = último grant → próximo grant (cadência mensal, mensal E anual). Clampa nas bordas.
     */
    private function fracaoCicloRestante(AccountSubscription $sub): float
    {
        $inicio = $sub->ultimo_grant_em ?? $sub->iniciada_em;
        if ($inicio === null) {
            return 0.0; // nunca concedido → não há incluso pra proratear
        }

        $inicio = \Illuminate\Support\Carbon::parse($inicio);
        $fim = $sub->proximo_grant_em
            ? \Illuminate\Support\Carbon::parse($sub->proximo_grant_em)
            : $inicio->copy()->addMonthNoOverflow();

        $total = $inicio->diffInSeconds($fim);
        if ($total <= 0) {
            return 0.0;
        }

        $restante = now()->diffInSeconds($fim, false); // negativo se já venceu
        $fracao = $restante / $total;

        return max(0.0, min(1.0, $fracao));
    }
}
