<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EfdImportacao extends Model
{
    protected $table = 'efd_importacoes';

    protected $fillable = [
        'user_id',
        'cliente_id',
        'tipo_efd',
        'cnpj',
        'periodo_inicio',
        'periodo_fim',
        'arquivo_hash',
        'filename',
        'arquivo_base64',
        'total_participantes',
        'total_cnpjs_unicos',
        'total_cpfs_unicos',
        'novos',
        'duplicados',
        'status',
        'total_notas',
        'notas_extraidas',
        'creditos_cobrados',
        'participante_ids',
        'iniciado_em',
        'concluido_em',
        'tempo_processamento_segundos',
        'resumo_final',
    ];

    protected function casts(): array
    {
        return [
            'total_participantes' => 'integer',
            'total_cnpjs_unicos' => 'integer',
            'total_cpfs_unicos' => 'integer',
            'novos' => 'integer',
            'duplicados' => 'integer',
            'total_notas' => 'integer',
            'notas_extraidas' => 'integer',
            'creditos_cobrados' => 'float',
            'participante_ids' => 'array',
            'periodo_inicio' => 'date',
            'periodo_fim' => 'date',
            'iniciado_em' => 'datetime',
            'concluido_em' => 'datetime',
            'tempo_processamento_segundos' => 'integer',
            'resumo_final' => 'array',
        ];
    }

    // SPED bruto retido

    /**
     * Codifica o SPED bruto para persistir em `arquivo_base64`. base64 é UTF-8-safe
     * (o EFD é tipicamente ISO-8859-1, e `json_encode` retorna false em byte não-UTF-8),
     * casa com o nome da coluna, com o `base64_decode` do download e com o cálculo de
     * quota `LENGTH*3/4`. Fonte única de escrita — use nos dois motores.
     */
    public static function encodeConteudoSped(string $conteudo): string
    {
        return base64_encode($conteudo);
    }

    /**
     * SPED bruto decodificado de `arquivo_base64`. Fonte única de LEITURA (Job, resolver,
     * auditoria). Tolera três formas históricas: base64 (canônico), string JSON-encoded
     * (imports da transição) e texto cru (fixtures). '' quando ausente.
     */
    public function conteudoSped(): string
    {
        $raw = $this->arquivo_base64;
        if (! $raw) {
            return '';
        }

        // Canônico: base64. O SPED sempre começa com '|0000'; '|' não é alfabeto base64,
        // então um texto cru ou JSON falha o decode estrito e cai nos ramos legados.
        $b64 = base64_decode($raw, true);
        if ($b64 !== false && str_starts_with(ltrim($b64), '|')) {
            return $b64;
        }

        // Transição: json_encode(string).
        $json = json_decode($raw, true);
        if (is_string($json)) {
            return $json;
        }

        // Legado cru.
        return (string) $raw;
    }

    // Acessores

    /**
     * Competência da EFD no formato "Jan/2026", derivada do período extraído do SPED.
     * Ex.: periodo_inicio 01.01.2026 → "Jan/2026". Null se o período não foi salvo.
     */
    public function getCompetenciaAttribute(): ?string
    {
        if (! $this->periodo_inicio) {
            return null;
        }

        $meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];

        return $meses[$this->periodo_inicio->month - 1].'/'.$this->periodo_inicio->year;
    }

    // Relacionamentos

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function participantes(): HasMany
    {
        return $this->hasMany(Participante::class, 'importacao_efd_id');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(EfdNota::class, 'importacao_id');
    }

    public function catalogoItens(): HasMany
    {
        return $this->hasMany(EfdCatalogoItem::class, 'importacao_id');
    }

    public function apuracaoContribuicao(): HasOne
    {
        return $this->hasOne(EfdApuracaoContribuicao::class, 'importacao_id');
    }

    public function apuracaoIcms(): HasOne
    {
        return $this->hasOne(EfdApuracaoIcms::class, 'importacao_id');
    }

    public function retencoesFonte(): HasMany
    {
        return $this->hasMany(EfdRetencaoFonte::class, 'importacao_id');
    }

    // Acessores

    /**
     * Total de participantes processados, priorizando o contrato atual do n8n.
     */
    public function getTotalProcessadosAttribute(): int
    {
        if (($this->total_participantes ?? 0) > 0) {
            return (int) $this->total_participantes;
        }

        if (is_array($this->participante_ids) && count($this->participante_ids) > 0) {
            return count($this->participante_ids);
        }

        return (int) $this->novos + (int) $this->duplicados;
    }

    /**
     * Tempo de processamento formatado (ex: "2m 34s").
     */
    public function getTempoProcessamentoAttribute(): string
    {
        $seconds = $this->tempo_processamento_segundos;

        // Fallback: calcular a partir dos timestamps (importações antigas)
        if ($seconds === null) {
            if (! $this->iniciado_em || ! $this->concluido_em) {
                return '—';
            }
            $seconds = (int) $this->iniciado_em->diffInSeconds($this->concluido_em);
        }

        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;

        if ($h > 0) {
            return $h.'h '.$m.'m';
        }
        if ($m > 0) {
            return $m.'m '.$s.'s';
        }
        if ($s > 0) {
            return $s.'s';
        }

        return '< 1s';
    }

    // Scopes

    public function scopeDoUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePendentes($query)
    {
        return $query->where('status', 'pendente');
    }

    public function scopeProcessando($query)
    {
        return $query->where('status', 'processando');
    }

    public function scopeConcluidas($query)
    {
        return $query->where('status', 'concluido');
    }

    public function scopeTravadas($query)
    {
        return $query->where('status', 'processando')
            ->where('updated_at', '<', now()->subMinutes((int) config('importacao.stale_minutos')));
    }

    public function marcarComoTravada(): void
    {
        $this->update(['status' => 'erro']);
    }
}
