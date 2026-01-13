<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RafConsultaPendente extends Model
{
    use HasFactory;

    protected $table = 'raf_consulta_pendente';

    protected $fillable = [
        'user_id',
        'tab_id',
        'tipo_efd',
        'tipo_consulta',
        'qtd_participantes',
        'valor_total_consulta',
        'custo_unitario',
        'resume_url',
        'status',
        'error_code',
        'error_message',
        'credits_refunded',
        'error_at',
        'n8n_received_at',
        'processing_started_at',
    ];

    protected function casts(): array
    {
        return [
            'valor_total_consulta' => 'decimal:2',
            'custo_unitario' => 'decimal:2',
            'qtd_participantes' => 'integer',
            'credits_refunded' => 'boolean',
            'n8n_received_at' => 'datetime',
            'processing_started_at' => 'datetime',
            'error_at' => 'datetime',
        ];
    }

    // Relacionamentos
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Scopes
    /**
     * Filtra relatórios pendentes (todos os registros são pendentes por padrão).
     * Mantido para compatibilidade futura caso seja necessário adicionar status.
     */
    public function scopePendentes($query)
    {
        return $query;
    }

    /**
     * Filtra relatórios de um usuário específico.
     */
    public function scopeDoUsuario($query, $userId)
    {
        return $query->where('user_id', (int) $userId);
    }

    // Accessors
    /**
     * Retorna o valor total formatado como moeda brasileira.
     */
    public function getValorTotalFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->valor_total_consulta, 2, ',', '.');
    }

    /**
     * Retorna o custo unitário formatado como moeda brasileira.
     */
    public function getCustoUnitarioFormatadoAttribute(): string
    {
        return 'R$ ' . number_format($this->custo_unitario, 2, ',', '.');
    }

    /**
     * Retorna a quantidade de participantes formatada.
     */
    public function getQtdParticipantesFormatadaAttribute(): string
    {
        return number_format($this->qtd_participantes, 0, ',', '.');
    }

    /**
     * Retorna o label do tipo de consulta.
     */
    public function getTipoConsultaLabelAttribute(): string
    {
        return match($this->tipo_consulta) {
            'gratuito' => 'Gratuita — Regime Tributário',
            'completa' => 'Completa — Regime + CND',
            default => $this->tipo_consulta,
        };
    }
}

