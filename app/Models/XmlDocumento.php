<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XmlDocumento extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'chave_acesso',
        'cnpj_emitente',
        'cnpj_destinatario',
        'data_emissao',
        'valor_total',
        'cfop',
        'tipo_documento',
        'status',
        'arquivo_path',
        'dados_extrados',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'valor_total' => 'decimal:2',
        'dados_extrados' => 'array',
    ];

    // Relacionamentos
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function lancamentos()
    {
        return $this->hasMany(XmlLancamento::class);
    }
}
