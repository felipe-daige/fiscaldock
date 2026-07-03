<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoramentoAssinatura extends Model
{
    use HasFactory;

    protected $table = 'monitoramento_assinaturas';

    protected $fillable = [
        'user_id',
        'participante_id',
        'cliente_id',
        'grupo_id',
        'plano_id',
        'status',
        'frequencia_dias',
        'proxima_execucao_em',
        'ultima_execucao_em',
    ];

    protected $casts = [
        'frequencia_dias' => 'integer',
        'proxima_execucao_em' => 'datetime',
        'ultima_execucao_em' => 'datetime',
    ];

    /**
     * Converte frequencia_dias para texto legível.
     */
    public function getFrequenciaAttribute(): string
    {
        return match (true) {
            $this->frequencia_dias <= 1 => 'diario',
            $this->frequencia_dias <= 7 => 'semanal',
            $this->frequencia_dias <= 15 => 'quinzenal',
            default => 'mensal',
        };
    }

    /**
     * Usuário dono da assinatura.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Participante monitorado.
     */
    public function participante(): BelongsTo
    {
        return $this->belongsTo(Participante::class);
    }

    /**
     * Cliente monitorado (alternativa ao participante).
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Grupo monitorado (3º tipo de alvo — dinâmico: cada ciclo consulta os membros atuais).
     */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(ParticipanteGrupo::class, 'grupo_id');
    }

    /**
     * Tipo do alvo monitorado: 'grupo', 'cliente' ou 'participante'.
     */
    public function alvoTipo(): string
    {
        if ($this->grupo_id) {
            return 'grupo';
        }

        return $this->cliente_id ? 'cliente' : 'participante';
    }

    /**
     * Membros ATUAIS do grupo monitorado (dinâmico), só CNPJ. Vazia quando o alvo não é grupo.
     */
    public function membrosDoGrupo(): \Illuminate\Support\Collection
    {
        if (! $this->grupo_id || ! $this->grupo) {
            return collect();
        }

        return $this->grupo->participantes()->somenteCnpj()->get();
    }

    /**
     * Custo de UM ciclo desta assinatura. Grupo = N membros atuais × custo do plano — é este
     * valor que o freio §6.2 e a checagem de saldo avaliam antes do disparo.
     */
    public function custoCiclo(): int
    {
        $unit = (int) ($this->plano->custo_creditos ?? 0);

        if ($this->grupo_id) {
            return $this->membrosDoGrupo()->count() * $unit;
        }

        return $unit;
    }

    /**
     * Modelo monitorado (Cliente ou Participante), de forma genérica.
     */
    public function alvo(): ?\Illuminate\Database\Eloquent\Model
    {
        return $this->cliente_id ? $this->cliente : $this->participante;
    }

    /**
     * Plano de monitoramento.
     */
    public function plano(): BelongsTo
    {
        return $this->belongsTo(MonitoramentoPlano::class, 'plano_id');
    }

    /**
     * Consultas realizadas por esta assinatura.
     */
    public function consultas(): HasMany
    {
        return $this->hasMany(MonitoramentoConsulta::class, 'assinatura_id');
    }

    /**
     * Verifica se a assinatura está ativa.
     */
    public function isAtiva(): bool
    {
        return $this->status === 'ativo';
    }

    /**
     * Verifica se está pendente de execução.
     */
    public function isPendenteExecucao(): bool
    {
        return $this->isAtiva()
            && $this->proxima_execucao_em
            && $this->proxima_execucao_em->isPast();
    }

    /**
     * Agenda próxima execução.
     */
    public function agendarProximaExecucao(): void
    {
        $this->update([
            'ultima_execucao_em' => now(),
            'proxima_execucao_em' => now()->addDays($this->frequencia_dias),
        ]);
    }

    /**
     * Pausa a assinatura.
     */
    public function pausar(): void
    {
        $this->update(['status' => 'pausado']);
    }

    /**
     * Reativa a assinatura.
     */
    public function reativar(): void
    {
        $this->update([
            'status' => 'ativo',
            'proxima_execucao_em' => now(),
        ]);
    }

    /**
     * Cancela a assinatura.
     */
    public function cancelar(): void
    {
        $this->update(['status' => 'cancelado']);
    }

    /**
     * Retorna assinaturas pendentes de execução.
     */
    public static function pendentesExecucao()
    {
        return static::where('status', 'ativo')
            ->whereNotNull('proxima_execucao_em')
            ->where('proxima_execucao_em', '<=', now())
            ->get();
    }
}
