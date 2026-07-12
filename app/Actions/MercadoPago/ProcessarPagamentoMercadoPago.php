<?php

namespace App\Actions\MercadoPago;

use App\Mail\RecargaAutomaticaPausada;
use App\Models\MercadoPagoPayment;
use App\Models\RecargaAutomatica;
use App\Notifications\CompraConfirmadaNotification;
use App\Notifications\RecargaAutomaticaConfirmadaNotification;
use App\Services\SaldoService;
use App\Services\MercadoPago\MercadoPagoClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Processa uma notificação de pagamento do Mercado Pago.
 *
 * Fonte de verdade: consulta o pagamento na API do MP (nunca confia no corpo do
 * webhook). Se `approved` e ainda não processado, libera o saldo da oferta
 * de forma IDEMPOTENTE — o webhook pode chegar N vezes e movimenta o saldo 1×.
 */
class ProcessarPagamentoMercadoPago
{
    public function __construct(
        private MercadoPagoClient $client = new MercadoPagoClient,
        private SaldoService $credits = new SaldoService,
    ) {}

    public function execute(string $mpPaymentId): ?MercadoPagoPayment
    {
        $dadosMp = $this->client->buscarPagamento($mpPaymentId);

        $status = $dadosMp['status'] ?? null;
        $externalReference = $dadosMp['external_reference'] ?? null;

        $emailPausado = null;
        $reciboCreditado = null;

        $payment = DB::transaction(function () use ($mpPaymentId, $dadosMp, $status, $externalReference, &$emailPausado, &$reciboCreditado) {
            // Localiza nossa linha pelo id do MP ou pela external_reference (nosso id).
            $query = MercadoPagoPayment::query()->lockForUpdate();

            $payment = (clone $query)->where('mp_payment_id', $mpPaymentId)->first()
                ?? ($externalReference ? (clone $query)->whereKey($externalReference)->first() : null);

            if ($payment === null) {
                return null;
            }

            $payment->fill([
                'mp_payment_id' => $mpPaymentId,
                'status' => $status ?? $payment->status,
                'status_detail' => $dadosMp['status_detail'] ?? $payment->status_detail,
                'payment_method' => $dadosMp['payment_method_id'] ?? $payment->payment_method,
                'payload' => $dadosMp,
            ]);

            // Libera saldo só uma vez, somente quando aprovado.
            if ($status === MercadoPagoPayment::STATUS_APPROVED && ! $payment->jaCreditado()) {
                $this->credits->add(
                    $payment->user,
                    (float) $payment->creditos,
                    'purchase',
                    "Compra de saldo — pacote {$payment->pacote} (Mercado Pago #{$mpPaymentId})",
                    $payment,
                );

                $payment->credited_at = now();

                // Marca o recibo p/ disparar DEPOIS do commit (só quando creditamos nesta
                // execução — idempotente: re-entrega do webhook não reenvia e-mail).
                $reciboCreditado = [
                    'user' => $payment->user,
                    'tipo' => $payment->tipo,
                    'pacote' => (string) $payment->pacote,
                    'valor' => (float) $payment->valor,
                ];
            }

            // Auto top-up por saldo: reflete o resultado na config de recarga (exclusiva).
            if ($payment->tipo === 'auto_topup') {
                $recarga = RecargaAutomatica::where('user_id', $payment->user_id)
                    ->where('gatilho', RecargaAutomatica::GATILHO_SALDO)
                    ->lockForUpdate()
                    ->first();

                if ($recarga !== null) {
                    if ($status === MercadoPagoPayment::STATUS_APPROVED) {
                        $recarga->update([
                            'status' => RecargaAutomatica::STATUS_ATIVA,
                            'cobranca_em_andamento' => false,
                            'ultima_cobranca_em' => now(),
                        ]);
                    } elseif (in_array($status, [MercadoPagoPayment::STATUS_REJECTED, MercadoPagoPayment::STATUS_CANCELLED], true)) {
                        $recarga->update([
                            'status' => RecargaAutomatica::STATUS_INADIMPLENTE,
                            'cobranca_em_andamento' => false,
                        ]);
                        $emailPausado = $payment->user;
                    }
                }
            }

            $payment->save();

            return $payment;
        });

        if ($emailPausado !== null) {
            Mail::to($emailPausado->email)->queue(new RecargaAutomaticaPausada($emailPausado, 'cartão recusado'));
        }

        if ($reciboCreditado !== null && $reciboCreditado['user'] !== null) {
            $notificacao = $reciboCreditado['tipo'] === 'auto_topup'
                ? new RecargaAutomaticaConfirmadaNotification($reciboCreditado['pacote'], $reciboCreditado['valor'], $mpPaymentId)
                : new CompraConfirmadaNotification($reciboCreditado['pacote'], $reciboCreditado['valor'], $mpPaymentId);

            $reciboCreditado['user']->notify($notificacao);
        }

        return $payment;
    }
}
