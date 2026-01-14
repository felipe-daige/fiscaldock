<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoramentoConsulta extends Model
{
    use HasFactory;

    protected $table = 'monitoramento_consultas';

    protected $fillable = [
        'user_id',
        'participante_id',
        'plano_id',
        'assinatura_id',
        'tipo',
        'status',
        'resultado',
        'creditos_cobrados',
        'error_code',
        'error_message',
        'executado_em',
    ];

    protected $casts = [
        'resultado' => 'array',
        'creditos_cobrados' => 'integer',
        'executado_em' => 'datetime',
    ];

    /**
     * Usuário que solicitou a consulta.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Participante consultado.
     */
    public function participante(): BelongsTo
    {
        return $this->belongsTo(Participante::class);
    }

    /**
     * Plano usado na consulta.
     */
    public function plano(): BelongsTo
    {
        return $this->belongsTo(MonitoramentoPlano::class, 'plano_id');
    }

    /**
     * Assinatura associada (se for consulta de assinatura).
     */
    public function assinatura(): BelongsTo
    {
        return $this->belongsTo(MonitoramentoAssinatura::class, 'assinatura_id');
    }

    /**
     * Verifica se a consulta está pendente.
     */
    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    /**
     * Verifica se a consulta está processando.
     */
    public function isProcessando(): bool
    {
        return $this->status === 'processando';
    }

    /**
     * Verifica se a consulta foi concluída com sucesso.
     */
    public function isSucesso(): bool
    {
        return $this->status === 'sucesso';
    }

    /**
     * Verifica se a consulta teve erro.
     */
    public function isErro(): bool
    {
        return $this->status === 'erro';
    }

    /**
     * Marca como processando.
     */
    public function marcarProcessando(): void
    {
        $this->update(['status' => 'processando']);
    }

    /**
     * Marca como sucesso com resultado.
     */
    public function marcarSucesso(array $resultado): void
    {
        $this->update([
            'status' => 'sucesso',
            'resultado' => $resultado,
            'executado_em' => now(),
        ]);
    }

    /**
     * Marca como erro.
     */
    public function marcarErro(string $code, string $message): void
    {
        $this->update([
            'status' => 'erro',
            'error_code' => $code,
            'error_message' => $message,
            'executado_em' => now(),
        ]);
    }

    /**
     * Consultas do usuário.
     */
    public static function doUsuario(int $userId)
    {
        return static::where('user_id', $userId)->orderBy('created_at', 'desc');
    }
}
