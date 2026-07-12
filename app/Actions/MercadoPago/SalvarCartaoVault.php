<?php

namespace App\Actions\MercadoPago;

use App\Models\RecargaAutomatica;
use App\Models\User;
use App\Services\MercadoPago\MercadoPagoClient;
use App\Services\PricingCatalogService;
use RuntimeException;

/**
 * Setup do auto top-up por saldo baixo: salva o cartão do usuário no vault do MP
 * (Customers + Cards) e grava a config exclusiva com gatilho=saldo.
 *
 * Regra dura: valor e saldo vêm do catálogo backend; a oferta tem que superar o
 * limite (senão a recarga cairia abaixo do próprio gatilho → loop). Exclusividade:
 * se havia recarga por tempo (preapproval), cancela antes.
 */
class SalvarCartaoVault
{
    public function __construct(
        private MercadoPagoClient $client = new MercadoPagoClient,
        private PricingCatalogService $catalog = new PricingCatalogService,
    ) {}

    public function execute(User $user, string $cardToken, string $pacoteSlug, int $limiteCreditos): RecargaAutomatica
    {
        $pacote = $this->catalog->resolveCheckoutSelection($pacoteSlug);

        if ($pacote === null) {
            throw new RuntimeException('Pacote de recarga inválido.');
        }

        $valor = round((float) $pacote['preco'], 2);
        $creditos = (int) $pacote['creditos'];

        if ($limiteCreditos < 1 || $creditos <= $limiteCreditos) {
            throw new RuntimeException('O pacote precisa ser maior que o limite de saldo.');
        }

        $teto = (int) config('services.mercadopago.preapproval_teto_centavos', 400000);
        if ((int) round($valor * 100) > $teto) {
            throw new RuntimeException('Valor acima do limite de cobrança automática. Fale com o atendimento.');
        }

        // Vault: reusa customer por email; senão cria.
        $busca = $this->client->buscarCustomerPorEmail($user->email);
        $customerId = $busca['results'][0]['id'] ?? null;
        if ($customerId === null) {
            $customerId = $this->client->criarCustomer($user->email)['id'] ?? null;
        }
        if ($customerId === null) {
            throw new RuntimeException('Não foi possível registrar o cartão (customer).');
        }

        $cartao = $this->client->salvarCartao((string) $customerId, $cardToken);
        $cardId = $cartao['id'] ?? null;
        if ($cardId === null) {
            throw new RuntimeException('Não foi possível salvar o cartão: '.json_encode($cartao));
        }

        // Exclusividade: cancela preapproval de uma recarga por tempo anterior.
        $anterior = RecargaAutomatica::where('user_id', $user->id)->first();
        if ($anterior && $anterior->ehTempo() && $anterior->mp_preapproval_id) {
            $this->client->cancelarPreapproval($anterior->mp_preapproval_id);
        }

        return RecargaAutomatica::updateOrCreate(
            ['user_id' => $user->id],
            [
                'gatilho' => RecargaAutomatica::GATILHO_SALDO,
                'limite_creditos' => $limiteCreditos,
                'pacote' => $pacote['slug'],
                'creditos' => $creditos,
                'valor' => $valor,
                'frequencia_meses' => null,
                'mp_preapproval_id' => null,
                'mp_customer_id' => (string) $customerId,
                'mp_card_id' => (string) $cardId,
                'cobranca_em_andamento' => false,
                'ultima_tentativa_em' => null,
                'status' => RecargaAutomatica::STATUS_ATIVA,
            ],
        )->fresh();
    }
}
