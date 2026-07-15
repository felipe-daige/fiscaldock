<?php

namespace App\Services\Consultas\Fontes;

use App\Support\Cnpj;

class CndFederalFonte extends FonteCertidaoInfoSimples
{
    public function chave(): string
    {
        return 'cnd_federal';
    }

    public function slug(): string
    {
        return 'receita-federal/pgfn';
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.cnd_federal', 0.40);
    }

    public function params(array $alvo): array
    {
        $params = parent::params($alvo) + ['preferencia_emissao' => '2via'];

        // A CND RFB/PGFN é unificada por base e só é emitida para a MATRIZ (regra da Receita).
        // Consultar a filial retorna 620 ("A certidão deve ser emitida para o CNPJ da matriz"),
        // cobrado pela InfoSimples e irrecuperável por retry. A certidão da matriz vale para
        // todos os estabelecimentos; ResultadoDetalhePresenter::notaMatrizFederal explica no card.
        //
        // `matriz_filial` (quando presente) vem do `identificador_matriz_filial` da própria RFB,
        // propagado pelo cadastro — mais confiável que a ORDEM do CNPJ: já vimos empresa real
        // com ordem ≠ 0001 marcada MATRIZ pela Receita (e a filial "0001" correspondente sendo
        // outro estabelecimento). Usar só a ordem nesses casos troca pro CNPJ errado e a InfoSimples
        // nunca emite — reconsulta ilimitada (classe `retry`) que nunca teria sucesso.
        $ehFilial = isset($alvo['matriz_filial'])
            ? $alvo['matriz_filial'] === 'filial'
            : Cnpj::ehFilial($params['cnpj']);

        if ($ehFilial) {
            $params['cnpj'] = Cnpj::matriz($params['cnpj']);
        }

        return $params;
    }

    protected function mapearSucesso(array $data): array
    {
        return [
            // status = `tipo` (Negativa / Positiva com efeitos / Positiva); fallback p/ conseguiu_emitir.
            'status' => $this->statusCertidao($data),
            'certidao_codigo' => $data['certidao_codigo'] ?? null,
            'emissao_data' => $data['emissao_data'] ?? null,
            'data_validade' => $data['validade_data'] ?? ($data['validade'] ?? null),
            'conseguiu_emitir' => (bool) ($data['conseguiu_emitir_certidao_negativa'] ?? false),
            'debitos_pgfn' => (bool) ($data['debitos_pgfn'] ?? false),
            'debitos_rfb' => (bool) ($data['debitos_rfb'] ?? false),
            'mensagem' => $data['mensagem'] ?? null,
            'situacao' => $data['situacao'] ?? null,
        ];
    }
}
