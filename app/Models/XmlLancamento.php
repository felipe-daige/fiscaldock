<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XmlLancamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'xml_documento_id',
        'natureza_operacao',
        'conta_debito',
        'conta_credito',
        'valor',
        'data_competencia',
        'status',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_competencia' => 'date',
    ];

    // Relacionamentos
    public function xmlDocumento()
    {
        return $this->belongsTo(XmlDocumento::class);
    }
}
