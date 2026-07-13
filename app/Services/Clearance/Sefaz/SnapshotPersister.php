<?php

namespace App\Services\Clearance\Sefaz;

use App\Models\CteConsulta;
use App\Models\NfeConsulta;
use App\Services\Consultas\ComprovanteArquivador;

class SnapshotPersister
{
    public function __construct(
        private readonly ComprovanteArquivador $comprovanteArquivador,
    ) {}

    /** UPSERT por (user_id, chave_acesso) na tabela certa por tipo de documento. */
    public function upsert(DocumentoSnapshot $s, ContextoPersistencia $ctx): void
    {
        $model = $s->tipoDocumento === 'CTE' ? CteConsulta::class : NfeConsulta::class;
        $existente = $model::query()
            ->where('user_id', $ctx->userId)
            ->where('chave_acesso', $s->chaveAcesso)
            ->first();
        $arquivos = (array) data_get($existente?->payload, 'comprovantes_arquivos', []);

        foreach ([
            'html' => $s->colunas['url_html'] ?? null,
            'xml' => $s->colunas['url_xml'] ?? null,
            'site_receipt' => $s->colunas['url_site_receipt'] ?? null,
        ] as $tipo => $url) {
            $arquivo = $this->comprovanteArquivador->arquivar(
                $url,
                $ctx->userId,
                ComprovanteArquivador::rotuloDocumento($s->tipoDocumento, $s->chaveAcesso, $tipo),
            );
            if ($arquivo !== null) {
                $arquivos[$tipo] = $arquivo['path'];
            }
        }

        $payload = $s->payload;
        if ($arquivos !== []) {
            $payload['comprovantes_arquivos'] = $arquivos;
        }

        $model::updateOrCreate(
            ['user_id' => $ctx->userId, 'chave_acesso' => $s->chaveAcesso],
            array_merge($s->colunas, [
                'tipo_documento' => $s->tipoDocumento,
                'cliente_id' => $ctx->clienteId,
                'consulta_lote_id' => $ctx->consultaLoteId,
                'credit_transaction_id' => $ctx->creditTransactionId,
                'correlation_id' => $ctx->correlationId,
                'custo' => $ctx->custo,
                'error_code' => $s->errorCode,
                'error_message' => $s->errorMessage,
                'payload' => $payload,
                'consultado_em' => now(),
            ])
        );
    }
}
