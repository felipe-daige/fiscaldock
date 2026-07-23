<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pedido de certidão de 2 ETAPAS (docs/advocacia/consultas-certidoes.md fase 4). Os TJs cadastram
 * o pedido (etapa 1) e emitem o PDF dias úteis depois (etapa 2 = conferência/obter). Esta é a
 * máquina de estados: o follow-up job (VerificarCertidaoPedidoJob) polla a etapa 2 até concluir.
 *
 * `correlacao` (jsonb) guarda as chaves que divergem por tribunal (TJMS numero_pedido+data_pedido;
 * TJRJ numero_requerimento; TRF3 numero_certidao). Ao concluir, grava em `certidoes` (CertidaoRegistro).
 */
class CertidaoPedido extends Model
{
    protected $table = 'certidao_pedidos';

    // Máquina de estados.
    public const SOLICITADA = 'solicitada';    // etapa 1 feita, aguardando o tribunal processar

    public const PROCESSANDO = 'processando';  // etapa 2 tentada, tribunal ainda não emitiu

    public const DISPONIVEL = 'disponivel';    // etapa 2 emitiu — veredito + PDF capturados

    public const BAIXADA = 'baixada';          // PDF arquivado em Meus Arquivos + gravado em certidoes

    public const FALHOU = 'falhou';            // erro definitivo ou tentativas esgotadas

    // Estados ainda em andamento (o sweep continua processando). DISPONIVEL entra: a etapa 2 já
    // emitiu (veredito+PDF em resultado_bloco) mas a persistência final ainda não concluiu — o
    // sweep retoma o arquivamento SEM re-consultar (barreira contra re-pagar o obter-certidao).
    public const ABERTOS = [self::SOLICITADA, self::PROCESSANDO, self::DISPONIVEL];

    protected $fillable = [
        'user_id', 'cliente_id', 'participante_id', 'alvo_tipo', 'alvo_documento',
        'tipo', 'slug_obter', 'estado', 'correlacao', 'tentativas', 'tentativas_tecnicas', 'proxima_verificacao_em',
        'status_certidao', 'certidao_codigo', 'arquivo_path', 'resultado_bloco', 'erro', 'consulta_lote_id',
        'solicitado_em', 'concluido_em',
    ];

    protected function casts(): array
    {
        return [
            'correlacao' => 'array',
            'resultado_bloco' => 'array',
            'proxima_verificacao_em' => 'datetime',
            'solicitado_em' => 'datetime',
            'concluido_em' => 'datetime',
        ];
    }

    /** Pedidos abertos cuja verificação já venceu — alvo do sweep do scheduler. */
    public function scopeVencidos(Builder $query): Builder
    {
        return $query->whereIn('estado', self::ABERTOS)
            ->where(function (Builder $q) {
                $q->whereNull('proxima_verificacao_em')
                    ->orWhere('proxima_verificacao_em', '<=', now());
            });
    }

    public function estaAberto(): bool
    {
        return in_array($this->estado, self::ABERTOS, true);
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
}
