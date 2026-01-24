<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XmlChaveProcessada extends Model
{
    protected $table = 'xml_chaves_processadas';

    protected $fillable = [
        'user_id',
        'chave_acesso',
        'tipo_documento',
        'importacao_xml_id',
        'participante_id',
        'processado_em',
    ];

    protected function casts(): array
    {
        return [
            'processado_em' => 'datetime',
        ];
    }

    // Relacionamentos

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function importacao(): BelongsTo
    {
        return $this->belongsTo(ImportacaoXml::class, 'importacao_xml_id');
    }

    public function participante(): BelongsTo
    {
        return $this->belongsTo(Participante::class);
    }

    // Scopes

    public function scopeDoUsuario($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_documento', $tipo);
    }

    /**
     * Verifica se uma chave já foi processada para o usuário.
     */
    public static function jaProcessada(int $userId, string $chaveAcesso): bool
    {
        return self::where('user_id', $userId)
            ->where('chave_acesso', $chaveAcesso)
            ->exists();
    }
}
