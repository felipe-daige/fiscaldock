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
        'tipo_efd',
        'tipo_consulta',
        'qtd_participantes',
        'valor_total_consulta',
        'custo_unitario',
        'resume_url',
        'n8n_received_at',
    ];

    protected function casts(): array
    {
        return [
            'valor_total_consulta' => 'decimal:2',
            'custo_unitario' => 'decimal:2',
            'qtd_participantes' => 'integer',
            'n8n_received_at' => 'datetime',
        ];
    }

    // Relacionamentos
    public function user()
    {
        return $this->belongsTo(User::class);
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
        // #region agent log
        try {
            $debugLogPath = '/opt/hub_contabil/.cursor/debug.log';
            $debugLogDir = dirname($debugLogPath);
            if (is_dir($debugLogDir) && is_writable($debugLogDir)) {
                file_put_contents($debugLogPath, json_encode([
                    'sessionId' => 'debug-session',
                    'runId' => 'run1',
                    'hypothesisId' => 'B',
                    'location' => 'RafConsultaPendente.php:52',
                    'message' => 'Scope doUsuario called',
                    'data' => [
                        'user_id_param' => $userId,
                        'user_id_param_type' => gettype($userId),
                        'user_id_casted' => (int) $userId,
                        'query_table' => $query->getModel()->getTable(),
                    ],
                    'timestamp' => time() * 1000
                ]) . "\n", FILE_APPEND);
            }
        } catch (\Throwable $e) {}
        // #endregion
        
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
            'regime' => 'Gratuita — Regime Tributário',
            'completa' => 'Completa — Regime + CND',
            default => $this->tipo_consulta,
        };
    }
}

