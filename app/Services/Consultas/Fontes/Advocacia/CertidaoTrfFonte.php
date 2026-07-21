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
        if ($tribunais !== []) {
            $todosNegativos = true;
            $comFeitos = [];
            foreach ($tribunais as $sigla => $trf) {
                if (! (bool) ($trf['conseguiu_emitir_certidao_negativa'] ?? false)) {
                    $todosNegativos = false;
                    $comFeitos[] = strtoupper((string) $sigla);
                }
            }
            $status = $todosNegativos ? 'Negativa' : 'Positiva';
        }

        $emissao = trim((string) ($detalhes['normalizado_data_hora_emissao'] ?? ''));

        return [
            'status' => $status,
            'certidao_codigo' => $detalhes['numero_certidao'] ?? null,
            'codigo_validacao' => $detalhes['codigo_validacao'] ?? null,
            'emissao_data' => preg_match('#^(\d{2}/\d{2}/\d{4})#', $emissao, $m) ? $m[1] : null,
            'data_validade' => $data['validade_data'] ?? null,
            'tribunais_com_feitos' => $comFeitos ?? [],
            'mensagem' => $data['mensagem'] ?? null,
        ];
    }
}
