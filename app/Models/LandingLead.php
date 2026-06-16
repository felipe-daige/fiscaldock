<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Lead capturado no banner de contato da landing pública (tabela `landing_leads`).
 *
 * Criado pelo `LandingPageController` (captura) e marcado como convertido pelo
 * `AuthController` quando o mesmo e-mail finaliza o signup.
 */
class LandingLead extends Model
{
    protected $fillable = [
        'email',
        'origem',
        'user_agent',
        'ip',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'converted_at' => 'datetime',
        ];
    }

    /**
     * Marca como convertido o(s) lead(s) com este e-mail que ainda não converteram.
     */
    public static function markConvertedByEmail(string $email): void
    {
        static::where('email', mb_strtolower(trim($email)))
            ->whereNull('converted_at')
            ->update(['converted_at' => now()]);
    }
}
