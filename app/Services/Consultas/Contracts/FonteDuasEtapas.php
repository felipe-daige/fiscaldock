<?php

namespace App\Services\Consultas\Contracts;

/**
 * Fonte de certidão em 2 ETAPAS (docs/advocacia/consultas-certidoes.md fase 4): a etapa 1
 * (slug/params/normalizar do contrato Fonte) CADASTRA o pedido; a etapa 2 (aqui) CONFERE/obtém a
 * certidão dias úteis depois. O motor detecta via `instanceof` e cria um `CertidaoPedido` (máquina
 * de estados) em vez de gravar direto. TJs divergem nas chaves de correlação → `extrairCorrelacao`
 * abstrai o que persistir entre as etapas.
 */
interface FonteDuasEtapas extends Fonte
{
    /** Slug da etapa 2 (conferência/obter) resolvido pro alvo. Ex.: tribunal/tjms/obter-certidao. */
    public function slugObter(array $alvo): string;

    /**
     * Chaves de correlação a persistir da resposta da etapa 1 (numero_pedido, data_pedido,
     * numero_requerimento, ...). Vazio = etapa 1 não devolveu o que liga à etapa 2 (tratar como falha).
     */
    public function extrairCorrelacao(array $data): array;

    /** Params da etapa 2 a partir do alvo + correlação persistida. */
    public function paramsObter(array $alvo, array $correlacao): array;

    /**
     * Interpreta a resposta da etapa 2. Retorna:
     *   ['pronta' => bool, 'bloco' => array]  — pronta=false: tribunal ainda não emitiu (repetir);
     *   pronta=true: `bloco` no mesmo shape das certidões single-call (status, certidao_codigo,
     *   nada_consta, comprovante, mensagem) p/ gravar em `certidoes` e arquivar o PDF.
     */
    public function mapearObter(array $data): array;

    /** Minutos até a 1ª verificação da etapa 2 (o tribunal precisa de tempo pra processar). */
    public function prazoInicialMinutos(): int;

    /**
     * Minutos até a PRÓXIMA verificação, dado o nº de verificações já feitas. Backoff ESCALONADO:
     * cada `obter` é uma chamada PAGA ao provedor, então o intervalo cresce para o pior caso não
     * estourar a margem da fonte (ver docs/advocacia/consultas-certidoes.md fase 4).
     */
    public function intervaloVerificacaoMinutos(int $tentativa): int;

    /** Máximo de verificações ao TRIBUNAL antes de desistir (marca o pedido como falhou). */
    public function maxVerificacoes(): int;
}
