<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingLead extends Model
{
    protected $table = 'landing_leads';

    protected $fillable = [
        'email',
        'origem',
        'user_agent',
        'ip',
        'converted_at',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
    ];

    public static function markConvertedByEmail(string $email): void
    {
        static::where('email', mb_strtolower(trim($email)))
            ->whereNull('converted_at')
            ->update(['converted_at' => now()]);
    }
}
