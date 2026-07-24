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

    protected function mapearSucesso(array $data): array
    {
        // Shape a confirmar no smoke; guarda a lista de parcelas e o resumo mínimo sem inventar
        // campos. `parcelas` costuma vir como lista; total_registros para o rollup.
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
