<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Remove a coluna `score_compliance` — as fontes CGU CNC e CNJ Improbidade foram
 * descontinuadas (2026-07-07). O escopo de fontes InfoSimples ficou fixo em 6
 * (CND Federal/Estadual/Municipal, CRF FGTS, SINTEGRA) e o score de risco
 * nunca ponderou essa categoria. Nada mais lê/escreve a coluna.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('participante_scores', 'score_compliance')) {
            Schema::table('participante_scores', function (Blueprint $table) {
                $table->dropColumn('score_compliance');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('participante_scores', 'score_compliance')) {
            Schema::table('participante_scores', function (Blueprint $table) {
                $table->smallInteger('score_compliance')->nullable()->after('score_trabalhista');
            });
        }
    }
};
