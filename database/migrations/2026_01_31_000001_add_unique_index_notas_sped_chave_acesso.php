<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Cria índice único parcial para evitar duplicatas de notas
     * com mesma chave de acesso dentro da mesma importação.
     */
    public function up(): void
    {
        // Índice único parcial: só aplica quando chave_acesso não é null
        DB::statement('
            CREATE UNIQUE INDEX IF NOT EXISTS notas_sped_unique_chave
            ON notas_sped (user_id, importacao_sped_id, chave_acesso)
            WHERE chave_acesso IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS notas_sped_unique_chave');
    }
};
