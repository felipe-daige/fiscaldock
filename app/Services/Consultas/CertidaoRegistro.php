<?php

namespace App\Services\Consultas;

use App\Models\Certidao;
use App\Support\DataBr;

/**
 * Grava/atualiza o registro canônico da certidão emitida (tabela `certidoes`) a partir do
 * bloco normalizado da fonte. 1 linha por (user, documento, fonte) — a emissão mais recente
 * vence; status "não emitida" (INDISPONIVEL/NAO_ENCONTRADA/INDETERMINADO) NÃO sobrescreve um
 * registro anterior: o usuário ainda tem em mãos a última certidão de fato emitida.
 *
 * valida_ate: parse do `data_validade` da resposta; fallback = emissão + regra fixa por órgão
 * (config certidoes.validade_default_dias). Alimenta os alertas de vencimento (fase 5).
 */
class CertidaoRegistro
{
    // 'EM ANDAMENTO' = strtoupper de CertidaoBadge::STATUS_EM_ANDAMENTO (pedido aceito mas ainda
    // não emitido — TRF/CJF assíncrono, TJMS 2 etapas). Não é certidão em mãos → não vira registro
    // canônico nem dispara alerta de validade. Comparação uppercase em registrar() (linha ~34).
    private const STATUS_NAO_EMITIDA = ['INDISPONIVEL', 'NAO_ENCONTRADA', 'INDETERMINADO', 'EM ANDAMENTO'];

    public function registrar(
        string $chaveFonte,
        array $bloco,
        int $userId,
        string $alvoTipo,
        int $alvoId,
        string $documento,
        int $loteId,
    ): ?Certidao {
        $status = trim((string) ($bloco['status'] ?? ''));
        $documento = preg_replace('/\D/', '', $documento) ?? '';
        if ($status === '' || in_array(strtoupper($status), self::STATUS_NAO_EMITIDA, true) || strlen($documento) !== 14) {
            return null;
        }

        // Certidão emitida na hora: sem emissao_data na resposta, a data da consulta É a emissão.
        $emitidaEm = DataBr::parse((string) ($bloco['emissao_data'] ?? ''))?->startOfDay() ?? now()->startOfDay();

        $validaAte = DataBr::parse((string) ($bloco['data_validade'] ?? ''))?->startOfDay();
        $origem = $validaAte !== null ? 'resposta' : null;
        if ($validaAte === null) {
            $dias = config("certidoes.validade_default_dias.{$chaveFonte}");
            if (is_numeric($dias)) {
                $validaAte = $emitidaEm->copy()->addDays((int) $dias);
                $origem = 'regra_orgao';
            }
        }

        // Órgão emissor: frase canônica do presenter, sem o artigo inicial ("A Receita..." → "Receita...").
        $orgao = ResultadoDetalhePresenter::ORGAO[$chaveFonte] ?? null;
        $orgao = $orgao !== null ? (preg_replace('/^[AO]\s+/u', '', $orgao) ?: $orgao) : null;

        // Chave inclui alvo_tipo: o MESMO CNPJ pode existir como participante (contraparte) E
        // cliente (empresa gerida) do mesmo usuário — sem isso as duas consultas colidiriam na
        // mesma linha e o alerta/re-emissão apontaria pro alvo errado.
        return Certidao::updateOrCreate(
            ['user_id' => $userId, 'alvo_tipo' => $alvoTipo, 'alvo_documento' => $documento, 'tipo' => $chaveFonte],
            [
                'participante_id' => $alvoTipo === 'participante' ? $alvoId : null,
                'cliente_id' => $alvoTipo === 'cliente' ? $alvoId : null,
                'orgao' => $orgao,
                'status' => mb_substr($status, 0, 40),
                'certidao_codigo' => isset($bloco['certidao_codigo']) && $bloco['certidao_codigo'] !== ''
                    ? mb_substr((string) $bloco['certidao_codigo'], 0, 120)
                    : null,
                'emitida_em' => $emitidaEm,
                'valida_ate' => $validaAte,
                'validade_origem' => $origem,
                'arquivo_path' => $bloco['comprovante_arquivo'] ?? null,
                'consulta_lote_id' => $loteId > 0 ? $loteId : null,
            ],
        );
    }
}
