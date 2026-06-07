<?php

namespace App\Services\Clearance\Sefaz;

/**
 * Snapshot achatado de uma consulta SEFAZ por chave. `colunas` mapeia exatamente as
 * colunas de nfe_consultas/cte_consultas (menos o contexto: user_id/cliente_id/lote/custo,
 * que o SnapshotPersister adiciona). `payload` vai pra coluna jsonb `payload`.
 */
final class DocumentoSnapshot
{
    public function __construct(
        public readonly string $tipoDocumento,   // NFE | NFCE | CTE
        public readonly string $chaveAcesso,
        public readonly string $status,          // AUTORIZADA|CANCELADA|DENEGADA|INUTILIZADA|NAO_ENCONTRADA|INDETERMINADO|ERRO_PARAMETRO|TIMEOUT|ERRO_INTEGRACAO
        public readonly array $colunas,
        public readonly array $payload,
        public readonly bool $persistivel,       // status grava linha? (sucesso/611/612)
        public readonly bool $estornavel,        // doc gera estorno?
        public readonly bool $billable,          // header.billable do InfoSimples
        public readonly ?string $errorCode = null,
        public readonly ?string $errorMessage = null,
        public readonly ?string $progressoMensagem = null,
    ) {}

    public function tabela(): string
    {
        return $this->tipoDocumento === 'CTE' ? 'cte_consultas' : 'nfe_consultas';
    }
}
