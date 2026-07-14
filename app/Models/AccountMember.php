<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountMember extends Model
{
    public const PAPEL_OWNER = 'owner';

    public const PAPEL_ADMIN = 'admin';

    public const PAPEL_OPERADOR = 'operador';

    public const PAPEL_LEITURA = 'leitura';

    public const PAPEIS = [
        self::PAPEL_OWNER,
        self::PAPEL_ADMIN,
        self::PAPEL_OPERADOR,
        self::PAPEL_LEITURA,
    ];

    public const MODULOS = ['painel', 'clientes', 'documentos', 'consultas', 'relatorios'];

    protected $fillable = [
        'account_id', 'user_id', 'papel', 'permissoes', 'convidado_por', 'entrou_em',
    ];

    protected function casts(): array
    {
        return [
            'permissoes' => 'array',
            'entrou_em' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'convidado_por');
    }

    public function isOwner(): bool
    {
        return $this->papel === self::PAPEL_OWNER;
    }

    public function isAdmin(): bool
    {
        return in_array($this->papel, [self::PAPEL_OWNER, self::PAPEL_ADMIN], true);
    }

    public function isReadOnly(): bool
    {
        return $this->papel === self::PAPEL_LEITURA;
    }

    public function permits(string $modulo): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return (bool) data_get($this->permissoes, $modulo, false);
    }

    /** @return array<string, bool> */
    public static function permissoesPadrao(string $papel): array
    {
        return match ($papel) {
            self::PAPEL_OWNER, self::PAPEL_ADMIN => array_fill_keys(self::MODULOS, true),
            self::PAPEL_OPERADOR => [
                'painel' => true,
                'clientes' => true,
                'documentos' => true,
                'consultas' => true,
                'relatorios' => false,
            ],
            default => [
                'painel' => true,
                'clientes' => true,
                'documentos' => true,
                'consultas' => false,
                'relatorios' => true,
            ],
        };
    }
}
