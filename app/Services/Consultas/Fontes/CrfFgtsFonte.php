<?php

namespace App\Services\Consultas\Fontes;

class CrfFgtsFonte extends FonteCertidaoInfoSimples
{
    public function chave(): string
    {
        return 'crf_fgts';
    }

    public function slug(): string
    {
        return 'caixa/regularidade';
    }

    public function custoCreditos(): int
    {
        return (int) config('consultas.fontes.crf_fgts', 2);
    }

    /**
     * A Caixa recusa o CRF FGTS com código 620 quando há IMPEDIMENTOS REAIS (débitos com a
     * PGFN/CAIXA) — não é falha passageira, é o próprio resultado da consulta. A doc oficial
     * da InfoSimples (docs/infosimples.md) descreve 620 como "o site emitiu um erro que
     * provavelmente não mudará em breve — leia-o para saber mais" (cobrado, não repetir em
     * curto prazo) — diferente de 608/619, que são sobre PARÂMETRO recusado/alterado no site
     * de origem (mais perto de um problema de requisição nosso do que de um resultado sobre a
     * empresa). Restrito a 620 até validarmos o comportamento real de 608/619 nessa fonte.
     *
     * Sem isso, o app tratava 620 como falha técnica e oferecia reconsulta ilimitada que nunca
     * teria êxito (lote #220: 15 chamadas pagas em sequência com a MESMA mensagem).
     *
     * Vira `indeterminado` (não "Irregular") pela mesma regra canônica da CND Federal
     * (CndFederal::analisar): sem certidão de fato EMITIDA (nº/data), a recusa sozinha não
     * comprova irregularidade — só que aqui a mensagem já vem específica e conclusiva da
     * Caixa/PGFN, então preservá-la (via `mensagem()`) é mais informativo que descartar tudo.
     */
    public function normalizar(array $raw, string $status = 'sucesso'): array
    {
        if ($status === 'erro_participante' && (int) ($raw['code'] ?? 0) === 620) {
            return parent::normalizar($raw, 'indeterminado');
        }

        return parent::normalizar($raw, $status);
    }

    protected function mapearSucesso(array $data): array
    {
        return [
            'status' => $data['situacao'] ?? ($data['tipo'] ?? null), // Regular / Irregular
            'certidao_codigo' => $data['numero_certificado'] ?? null,
            'emissao_data' => $data['emissao_data'] ?? null,
            'data_validade' => $data['validade_data'] ?? null,
            'conseguiu_emitir' => (bool) ($data['conseguiu_emitir_certidao_negativa'] ?? false),
            'mensagem' => $data['mensagem'] ?? null,
        ];
    }
}
