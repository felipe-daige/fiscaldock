<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RafRelatorioProcessado extends Model
{
    use HasFactory;

    protected $table = 'raf_relatorio_processado';

    protected $fillable = [
        'user_id',
        'cliente_id',
        'document_type',
        'consultant_type',
        'report_csv_base64',
        'resume_url',
        'total_participants',
        'total_price',
        'filename',
        'cnpj_empresa_analisada',
        'razao_social_empresa',
        'data_inicial_analisada',
        'data_final_analisada',
        'total_fornecedores',
        'qnt_fornecedores_cnpj',
        'qnt_fornecedores_cpf',
        'qnt_situacao_nula',
        'qnt_situacao_ativa',
        'qnt_situacao_suspensa',
        'qnt_situacao_inapta',
        'qnt_situacao_baixada',
        'qnt_simples',
        'qnt_presumido',
        'qnt_real',
        'qnt_regime_indeterminado',
        'qnt_cnd_regular',
        'qnt_cnd_pendencia',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'total_participants' => 'integer',
            'total_price' => 'decimal:2',
            'data_inicial_analisada' => 'date',
            'data_final_analisada' => 'date',
            'total_fornecedores' => 'integer',
            'qnt_fornecedores_cnpj' => 'integer',
            'qnt_fornecedores_cpf' => 'integer',
            'qnt_situacao_nula' => 'integer',
            'qnt_situacao_ativa' => 'integer',
            'qnt_situacao_suspensa' => 'integer',
            'qnt_situacao_inapta' => 'integer',
            'qnt_situacao_baixada' => 'integer',
            'qnt_simples' => 'integer',
            'qnt_presumido' => 'integer',
            'qnt_real' => 'integer',
            'qnt_regime_indeterminado' => 'integer',
            'qnt_cnd_regular' => 'integer',
            'qnt_cnd_pendencia' => 'integer',
            'processed_at' => 'datetime',
        ];
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
        return $this->hasMany(RafParticipante::class, 'raf_relatorio_processado_id');
    }
}


