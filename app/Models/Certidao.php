<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Registro canônico da certidão EMITIDA mais recente por (user, documento, fonte) — fiscal e
 * judicial (vertical advocacia). Alimenta os alertas de vencimento (valida_ate) e aponta o PDF
 * arquivado em Meus Arquivos (arquivo_path). Histórico completo vive em consulta_resultados.
 *
 * Spec: docs/advocacia/consultas-certidoes.md (fase 2).
 */
class Certidao extends Model
{
    protected $table = 'certidoes';

    protected $fillable = [
        'user_id', 'cliente_id', 'participante_id', 'alvo_tipo', 'alvo_documento',
        'tipo', 'orgao', 'status', 'certidao_codigo', 'emitida_em', 'valida_ate',
        'validade_origem', 'arquivo_path', 'consulta_lote_id',
    ];

    protected function casts(): array
    {
        return [
            'emitida_em' => 'date',
            'valida_ate' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function participante(): BelongsTo
    {
        return $this->belongsTo(Participante::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(ConsultaLote::class, 'consulta_lote_id');
    }
}
