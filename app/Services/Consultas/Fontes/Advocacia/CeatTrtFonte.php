<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteCertidaoInfoSimples;

/**
 * CEAT — Certidão Eletrônica de Ações Trabalhistas do TRT da REGIÃO do alvo.
 * ≠ CNDT (TST): a CNDT atesta débitos; a CEAT lista AÇÕES em andamento na região.
 * Slug dinâmico: `tribunal/trt{n}/ceat` resolvido pela UF do alvo (cadastro roda antes e
 * injeta a UF autoritativa). Limitação MVP: SP usa TRT2 (capital/baixada) — o interior é
 * TRT15/Campinas; refinamento por município fica pra fase posterior (documentado na spec).
 */
class CeatTrtFonte extends FonteCertidaoInfoSimples
{
    /** UF → nº do TRT regional. */
    public const UF_TRT = [
        'RJ' => 1, 'SP' => 2, 'MG' => 3, 'RS' => 4, 'BA' => 5, 'PE' => 6, 'CE' => 7,
        'PA' => 8, 'AP' => 8, 'PR' => 9, 'DF' => 10, 'TO' => 10, 'AM' => 11, 'RR' => 11,
        'SC' => 12, 'PB' => 13, 'RO' => 14, 'AC' => 14, 'MA' => 16, 'ES' => 17, 'GO' => 18,
        'AL' => 19, 'SE' => 20, 'RN' => 21, 'PI' => 22, 'MT' => 23, 'MS' => 24,
    ];

    public function chave(): string
    {
        return 'ceat_trt';
    }

    public function slug(): string
    {
        return 'tribunal/trt{n}/ceat';
    }

    public function slugPara(array $alvo): string
    {
        $trt = self::UF_TRT[strtoupper(trim((string) ($alvo['uf'] ?? '')))] ?? null;

        return $trt !== null ? "tribunal/trt{$trt}/ceat" : $this->slug();
    }

    public function aplicavelPara(array $alvo): bool
    {
        // `nome` é OBRIGATÓRIO no endpoint (smoke lote 260: sem ele a chamada dá 606 BILLABLE).
        // A razão social vem do banco no alvo inicial e é sobrescrita pela RFB via cadastro.
        return isset(self::UF_TRT[strtoupper(trim((string) ($alvo['uf'] ?? '')))])
            && trim((string) ($alvo['razao_social'] ?? '')) !== '';
    }

    public function motivoIndisponivel(array $alvo): string
    {
        if (trim((string) ($alvo['razao_social'] ?? '')) === '') {
            return 'CEAT exige o nome/razão social do consultado — indisponível no cadastro deste CNPJ.';
        }

        return 'CEAT exige a UF da sede para resolver o TRT regional — UF indisponível no cadastro deste CNPJ.';
    }

    public function params(array $alvo): array
    {
        // Doc oficial (docs/infosimples/tribunais-certidoes-judiciais.md): `nome` obrigatório +
        // cnpj. O resultado confiável pro CNPJ é processos_encontrados_cpf_cnpj (nome pega homônimo).
        return parent::params($alvo) + ['nome' => trim((string) ($alvo['razao_social'] ?? ''))];
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.ceat_trt', 1.00);
    }

    protected function mapearSucesso(array $data): array
    {
        // Contrato real (doc detalhada): conseguiu_emitir_certidao_negativa/nada_consta,
        // numero_certidao, normalizado_expedicao_datahora ("d/m/Y H:i:s"), total_processos e
        // processos_encontrados_cpf_cnpj. A busca nominal pega homônimos — o dado confiável
        // pro CNPJ é processos_encontrados_cpf_cnpj.
        $expedicao = trim((string) ($data['normalizado_expedicao_datahora'] ?? ''));
        $doCnpj = is_array($data['processos_encontrados_cpf_cnpj'] ?? null)
            ? $data['processos_encontrados_cpf_cnpj']
            : [];

        return [
            'status' => $this->statusCertidao($data),
            'certidao_codigo' => $data['numero_certidao'] ?? null,
            'emissao_data' => preg_match('#^(\d{2}/\d{2}/\d{4})#', $expedicao, $m) ? $m[1] : null,
            'data_validade' => $data['validade_data'] ?? null,
            'nada_consta' => $data['nada_consta'] ?? null,
            'total_processos' => $data['total_processos'] ?? null,
            'processos_cnpj_quantidade' => $doCnpj['quantidade'] ?? null,
            'processos_cnpj' => array_slice((array) ($doCnpj['lista_processos'] ?? []), 0, 20),
            'mensagem' => $data['mensagem'] ?? null,
        ];
    }
}
