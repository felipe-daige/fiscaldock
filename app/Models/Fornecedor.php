<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    use HasFactory;

    protected $fillable = [
        'cnpj',
        'razao_social',
        'regime_tributario',
        'ultima_consulta_regime',
    ];

    protected $casts = [
        'ultima_consulta_regime' => 'datetime',
    ];
}
