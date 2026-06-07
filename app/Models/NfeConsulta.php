<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NfeConsulta extends Model
{
    protected $table = 'nfe_consultas';

    protected $guarded = [];

    protected $casts = [
        'valor_total' => 'float',
        'custo' => 'float',
        'nfe_completa' => 'boolean',
        'consulta_sem_certificado' => 'boolean',
        'xml_completo' => 'boolean',
        'eventos' => 'array',
        'totais' => 'array',
        'produtos' => 'array',
        'payload' => 'array',
        'consultado_em' => 'datetime',
    ];
}
