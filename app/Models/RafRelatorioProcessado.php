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
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'total_participants' => 'integer',
            'total_price' => 'decimal:2',
        ];
    }

    // Relacionamentos
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

