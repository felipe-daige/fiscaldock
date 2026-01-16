<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Participante extends Model
{
    use HasFactory;

    protected $table = 'participantes';

    protected $fillable = [
        'user_id',
        'cliente_id',
        'cnpj',
        'razao_social',
        'nome_fantasia',
        'situacao_cadastral',
        'regime_tributario',
        'origem_tipo',
        'origem_ref',
        'ultima_consulta_em',
    ];

    protected $casts = [
        'origem_ref' => 'array',
        'ultima_consulta_em' => 'datetime',
    ];

    /**
     * Usuário dono do participante.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Cliente associado (opcional).
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Assinaturas de monitoramento do participante.
     */
    public function assinaturas(): HasMany
    {
        return $this->hasMany(MonitoramentoAssinatura::class);
    }

    /**
     * Consultas realizadas para o participante.
     */
    public function consultas(): HasMany
    {
        return $this->hasMany(MonitoramentoConsulta::class);
    }

    /**
     * Grupos aos quais o participante pertence.
     */
    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(ParticipanteGrupo::class, 'participantes_grupos_pivot', 'participante_id', 'participantes_grupo_id')
            ->withTimestamps();
    }

    /**
     * CNPJ formatado.
     */
    public function getCnpjFormatadoAttribute(): string
    {
        $cnpj = preg_replace('/[^0-9]/', '', $this->cnpj);
        if (strlen($cnpj) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
        }
        return $this->cnpj;
    }

    /**
     * Verifica se o participante tem assinatura ativa.
     */
    public function temAssinaturaAtiva(): bool
    {
        return $this->assinaturas()->where('status', 'ativo')->exists();
    }

    /**
     * Retorna a última consulta realizada.
     */
    public function ultimaConsulta(): ?MonitoramentoConsulta
    {
        return $this->consultas()->latest('executado_em')->first();
    }
}
