<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RafParticipante extends Model
{
    use HasFactory;

    protected $table = 'raf_participantes';

    protected $fillable = [
        'raf_relatorio_processado_id',
        'user_id',
        'cliente_id',
        'tipo_efd',
        'modalidade',
        'consultante_cnpj',
        'consultante_razao_social',
        'cnpj',
        'cnpj_matriz',
        'razao_social',
        'situacao_cadastral',
        'regime_tributario',
        'cnae_descricao',
        'cnd_situacao',
        'cnd_tipo',
        'cnd_data_emissao',
        'cnd_data_validade',
        'cnd_codigo_controle',
        'cnd_informacoes_adicionais',
        'data_inicio',
        'data_final',
    ];

    protected function casts(): array
    {
        return [
            'raf_relatorio_processado_id' => 'integer',
            'user_id' => 'integer',
            'cliente_id' => 'integer',
            'cnd_data_emissao' => 'date',
            'cnd_data_validade' => 'date',
            'data_inicio' => 'date',
            'data_final' => 'date',
        ];
    }

    // Relacionamentos

    public function relatorio(): BelongsTo
    {
        return $this->belongsTo(RafRelatorioProcessado::class, 'raf_relatorio_processado_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    // Scopes

    public function scopeCompleta($query)
    {
        return $query->where('modalidade', 'completa');
    }

    public function scopeGratuita($query)
    {
        return $query->where('modalidade', 'gratuito');
    }

    public function scopeFiscal($query)
    {
        return $query->where('tipo_efd', 'EFD Fiscal');
    }

    public function scopeContribuicoes($query)
    {
        return $query->where('tipo_efd', 'EFD Contribuições');
    }

    public function scopeDoUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDoRelatorio($query, int $relatorioId)
    {
        return $query->where('raf_relatorio_processado_id', $relatorioId);
    }

    // Accessors

    public function getCnpjFormatadoAttribute(): string
    {
        $cnpj = preg_replace('/\D/', '', $this->cnpj);

        if (strlen($cnpj) !== 14) {
            return $this->cnpj;
        }

        return sprintf(
            '%s.%s.%s/%s-%s',
            substr($cnpj, 0, 2),
            substr($cnpj, 2, 3),
            substr($cnpj, 5, 3),
            substr($cnpj, 8, 4),
            substr($cnpj, 12, 2)
        );
    }

    public function getConsultanteCnpjFormatadoAttribute(): string
    {
        $cnpj = preg_replace('/\D/', '', $this->consultante_cnpj);

        if (strlen($cnpj) !== 14) {
            return $this->consultante_cnpj;
        }

        return sprintf(
            '%s.%s.%s/%s-%s',
            substr($cnpj, 0, 2),
            substr($cnpj, 2, 3),
            substr($cnpj, 5, 3),
            substr($cnpj, 8, 4),
            substr($cnpj, 12, 2)
        );
    }

    public function getTemCndAttribute(): bool
    {
        return $this->modalidade === 'completa' && !is_null($this->cnd_situacao);
    }

    public function getCndVigente(): bool
    {
        if (!$this->tem_cnd || !$this->cnd_data_validade) {
            return false;
        }

        return $this->cnd_data_validade->isFuture();
    }
}
