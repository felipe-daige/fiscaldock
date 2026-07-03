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

    public function custoCreditos(): int
    {
        return (int) config('consultas.fontes.cnd_federal', 2);
    }

    public function params(array $alvo): array
    {
        $params = parent::params($alvo) + ['preferencia_emissao' => '2via'];

        // A CND RFB/PGFN é unificada por base e só é emitida para a MATRIZ (regra da Receita).
        // Consultar a filial retorna 620 ("A certidão deve ser emitida para o CNPJ da matriz"),
        // cobrado pela InfoSimples e irrecuperável por retry. A certidão da matriz vale para
        // todos os estabelecimentos; ResultadoDetalhePresenter::notaMatrizFederal explica no card.
        if (Cnpj::ehFilial($params['cnpj'])) {
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
