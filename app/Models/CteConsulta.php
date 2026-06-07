<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CteConsulta extends Model
{
    protected $table = 'cte_consultas';

    protected $guarded = [];

    protected $casts = [
        'valor_prestacao' => 'float',
        'valor_carga' => 'float',
        'custo' => 'float',
        'nfes_referenciadas_count' => 'integer',
        'cte_completa' => 'boolean',
        'consulta_sem_certificado' => 'boolean',
        'xml_completo' => 'boolean',
        'eventos' => 'array',
        'componentes' => 'array',
        'nfes_referenciadas' => 'array',
        'totais' => 'array',
        'rodoviario' => 'array',
        'aquaviario' => 'array',
        'payload' => 'array',
        'consultado_em' => 'datetime',
    ];
}
