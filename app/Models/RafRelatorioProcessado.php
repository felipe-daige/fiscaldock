<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RafRelatorioProcessado extends Model
{
    use HasFactory;

    protected $table = 'raf_relatorio_processado';

    protected $fillable = [
        'user_id',
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
        'qtd_ativos',
        'qtd_inaptos',
        'qtd_simples',
        'qtd_presumido',
        'qtd_real',
        'qtd_regime_indeterminado',
        'qtd_cnd_regular',
        'qtd_cnd_pendencia',
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
            'qtd_ativos' => 'integer',
            'qtd_inaptos' => 'integer',
            'qtd_simples' => 'integer',
            'qtd_presumido' => 'integer',
            'qtd_real' => 'integer',
            'qtd_regime_indeterminado' => 'integer',
            'qtd_cnd_regular' => 'integer',
            'qtd_cnd_pendencia' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    // Relacionamentos
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


