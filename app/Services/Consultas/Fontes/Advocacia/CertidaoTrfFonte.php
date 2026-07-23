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
    /**
     * Frases que indicam pedido ACEITO mas ainda não emitido. Note o que NÃO está aqui:
     * "disponibiliza" é a redação de SUCESSO do CJF ("certidão disponibilizada para download") e,
     * como gatilho, marcava certidão real como pendente — o usuário pagava, o CertidaoRegistro
     * pulava o registro e o card ficava pendente pra sempre (não há polling no TRF).
     */
    private const FRASES_PENDENTE = ['andamento', 'aguarde', 'aguardando', 'em processamento'];

    /** Campos de `detalhes_certidao` cuja presença prova que a certidão SAIU. */
    private const CAMPOS_EMISSAO = ['numero_certidao', 'codigo_validacao', 'normalizado_data_hora_emissao'];

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

    public function aceitaPessoa(): array
    {
        return $this->tiposPessoaComPfValidado();
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
        if ($status === null && $tribunais === [] && $this->ehEmAndamento($data)) {
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
     * Emissão sempre vence a frase: marcar como pendente uma certidão que saiu é caro (cobra, não
     * registra em `certidoes`, não gera validade/alerta e o card fica pendente pra sempre).
     */
    private function ehEmAndamento(array $data): bool
    {
        if ($this->emitiu($data)) {
            return false;
        }

        $mensagem = mb_strtolower((string) ($data['mensagem'] ?? ''));

        foreach (self::FRASES_PENDENTE as $frase) {
            if ($mensagem !== '' && str_contains($mensagem, $frase)) {
                return true;
            }
        }

        return false;
    }

    /** Qualquer marca de certidão EMITIDA no retorno (flag da API ou dado do documento). */
    private function emitiu(array $data): bool
    {
        if (($data['conseguiu_emitir'] ?? null) === true) {
            return true;
        }

        $detalhes = is_array($data['detalhes_certidao'] ?? null) ? $data['detalhes_certidao'] : [];

        foreach (self::CAMPOS_EMISSAO as $campo) {
            if (filled($detalhes[$campo] ?? null)) {
                return true;
            }
        }

        return false;
    }
}
