<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteCertidaoInfoSimples;

/**
 * Certidão unificada da Justiça Federal (todos os TRFs em 1 chamada, via CJF).
 * Params OBRIGATÓRIOS no endpoint (smoke lote 260 dava 606 sem eles): `tipo` (1 cível — o que
 * interessa pra regularidade PJ) e `email`. Doc: docs/infosimples/tribunais-certidoes-judiciais.md.
 * O e-mail é campo de solicitação da CJF — usamos um remetente de sistema (config), nunca o do
 * usuário, pra não vazar dado pessoal pra fonte externa.
 */
class CertidaoTrfFonte extends FonteCertidaoInfoSimples
{
    public function chave(): string
    {
        return 'certidao_trf';
    }

    public function slug(): string
    {
        return 'tribunal/trf/cert-unificada';
    }

    public function params(array $alvo): array
    {
        return parent::params($alvo) + [
            'tipo' => '1', // 1 Cível · 2 Criminal · 3 Eleitoral — cível é a regularidade da PJ
            'email' => (string) config('advocacia.email_solicitante', 'consultas@fiscaldock.com.br'),
        ];
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.certidao_trf', 1.00);
    }

    protected function mapearSucesso(array $data): array
    {
        // Contrato real: sem `tipo`/`data_validade` — o veredito é a conjunção dos TRFs em
        // detalhes_certidao.tribunais.trfN.conseguiu_emitir_certidao_negativa. Um único TRF que
        // não emite negativa (constam feitos) já torna a certidão Positiva.
        $detalhes = is_array($data['detalhes_certidao'] ?? null) ? $data['detalhes_certidao'] : [];
        $tribunais = is_array($detalhes['tribunais'] ?? null) ? $detalhes['tribunais'] : [];

        $status = null;
        $comFeitos = [];
        if ($tribunais !== []) {
            $todosNegativos = true;
            foreach ($tribunais as $sigla => $trf) {
                if (! (bool) ($trf['conseguiu_emitir_certidao_negativa'] ?? false)) {
                    $todosNegativos = false;
                    $comFeitos[] = strtoupper((string) $sigla);
                }
            }
            $status = $todosNegativos ? 'Negativa' : 'Positiva';
        }

        $mensagem = $data['mensagem'] ?? null;

        // Estado ASSÍNCRONO do CJF: o sistema unificado nem sempre emite na hora — às vezes aceita
        // o pedido e entrega a certidão SÓ por e-mail em até 6h (não há protocolo pra pollar nem
        // endpoint de "obter depois"). Sem `tribunais` no retorno e com a frase de "em andamento",
        // marcamos EM_ANDAMENTO (estado pendente, não emitida) em vez de status nulo/silencioso. A
        // mensagem original cita "por e-mail" (que é a caixa de SISTEMA, não a do usuário) — trocamos
        // por uma frase honesta pro usuário; o PDF chega no e-mail de sistema e é repassado (MVP).
        if ($status === null && $tribunais === [] && $this->ehEmAndamento($mensagem, $data, $detalhes)) {
            $status = \App\Support\CertidaoBadge::STATUS_EM_ANDAMENTO;
            $mensagem = 'Certidão solicitada ao sistema unificado do CJF. A emissão pode levar '
                .'algumas horas; assim que o CJF responder, a certidão é processada e disponibilizada.';
        }

        $emissao = trim((string) ($detalhes['normalizado_data_hora_emissao'] ?? ''));

        return [
            'status' => $status,
            'certidao_codigo' => $detalhes['numero_certidao'] ?? null,
            'codigo_validacao' => $detalhes['codigo_validacao'] ?? null,
            'emissao_data' => preg_match('#^(\d{2}/\d{2}/\d{4})#', $emissao, $m) ? $m[1] : null,
            'data_validade' => $data['validade_data'] ?? null,
            'tribunais_com_feitos' => $comFeitos,
            'mensagem' => $mensagem,
        ];
    }

    /**
     * True se o retorno indica pedido aceito mas ainda não emitido (entrega assíncrona por e-mail).
     *
     * DOIS filtros antes da frase, porque marcar como pendente uma certidão que de fato saiu é
     * caro: o usuário paga, o `CertidaoRegistro` pula o registro (EM ANDAMENTO está em
     * STATUS_NAO_EMITIDA), a certidão nunca ganha `valida_ate`/alerta e o card fica pendente para
     * sempre — não há polling no TRF que resolva depois.
     *   1. Qualquer marca de certidão EMITIDA (numero_certidao / codigo_validacao / data de
     *      emissão / conseguiu_emitir=true) desqualifica: emitiu, não está em andamento.
     *   2. A frase precisa dizer PENDÊNCIA. 'disponibiliza' saiu da lista: é a redação de SUCESSO
     *      do próprio CJF ("certidão disponibilizada para download") e capturava emissão real.
     */
    private function ehEmAndamento(?string $mensagem, array $data, array $detalhes): bool
    {
        if (($data['conseguiu_emitir'] ?? null) === true) {
            return false;
        }

        $emitiu = filled($detalhes['numero_certidao'] ?? null)
            || filled($detalhes['codigo_validacao'] ?? null)
            || filled($detalhes['normalizado_data_hora_emissao'] ?? null);

        if ($emitiu) {
            return false;
        }

        $m = mb_strtolower((string) $mensagem);

        return $m !== '' && (
            str_contains($m, 'andamento')
            || str_contains($m, 'aguarde')
            || str_contains($m, 'aguardando')
            || str_contains($m, 'em processamento')
        );
    }
}
