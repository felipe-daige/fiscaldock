<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteGovBrInfoSimples;

/**
 * INCRA/SIGEF — parcelas georreferenciadas certificadas em nome do alvo (imóvel rural).
 * Exige login GOV.BR do solicitante (ver FonteGovBrInfoSimples). Alvo por cpf/cnpj; a doc do
 * painel aceita também `codigo_imovel` opcional (busca direta por parcela) — não usado no MVP
 * por CPF/CNPJ. Resposta lenta na origem (~99s) — dentro do timeout padrão (120s).
 *
 * Contrato confirmado só após smoke pago com credencial real — params abaixo vêm da doc do painel.
 */
class SigefParcelasFonte extends FonteGovBrInfoSimples
{
    public function chave(): string
    {
        return 'sigef_parcelas';
    }

    public function slug(): string
    {
        return 'incra/sigef/parcelas';
    }

    public function aceitaPessoa(): array
    {
        return $this->tiposPessoaComPfValidado();
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.sigef_parcelas', 1.00);
    }

    public function normalizar(array $raw, string $status = 'sucesso'): array
    {
        // 612 do SIGEF = "não foram encontradas parcelas" = nada consta = Negativa (o alvo não
        // tem imóvel rural certificado). Não é falha nem indeterminação — confirmado no smoke
        // 2026-07-24 (CPF sem imóvel: 612 billable). Sem isto cairia em NAO_ENCONTRADA (ambíguo).
        if ($status === 'nao_encontrado') {
            return $this->bloco([
                'status' => 'Negativa',
                'nada_consta' => true,
                'total_registros' => 0,
                'parcelas' => [],
                'mensagem' => $this->mensagem($raw),
            ]);
        }

        return parent::normalizar($raw, $status);
    }

    protected function mapearSucesso(array $data): array
    {
        // Shape a confirmar quando um alvo COM parcela aparecer; `parcelas` costuma vir como lista.
        $parcelas = is_array($data['parcelas'] ?? null) ? $data['parcelas'] : [];

        return [
            'status' => $parcelas === [] ? 'Negativa' : 'Positiva',
            'nada_consta' => $parcelas === [],
            'total_registros' => $data['total_registros'] ?? count($parcelas),
            'parcelas' => array_slice($parcelas, 0, 20),
            'mensagem' => $data['mensagem'] ?? null,
        ];
    }
}
