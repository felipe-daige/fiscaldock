<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Usar SQL raw para alterar colunas para nullable (não requer doctrine/dbal)
        DB::statement('ALTER TABLE raf_consulta_pendente ALTER COLUMN tipo_efd DROP NOT NULL');
        DB::statement('ALTER TABLE raf_consulta_pendente ALTER COLUMN tipo_consulta DROP NOT NULL');
        DB::statement('ALTER TABLE raf_consulta_pendente ALTER COLUMN qtd_participantes DROP NOT NULL');
        DB::statement('ALTER TABLE raf_consulta_pendente ALTER COLUMN valor_total_consulta DROP NOT NULL');
        DB::statement('ALTER TABLE raf_consulta_pendente ALTER COLUMN custo_unitario DROP NOT NULL');
        DB::statement('ALTER TABLE raf_consulta_pendente ALTER COLUMN resume_url DROP NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter: tornar colunas NOT NULL novamente
        DB::statement('ALTER TABLE raf_consulta_pendente ALTER COLUMN tipo_efd SET NOT NULL');
        DB::statement('ALTER TABLE raf_consulta_pendente ALTER COLUMN tipo_consulta SET NOT NULL');
        DB::statement('ALTER TABLE raf_consulta_pendente ALTER COLUMN qtd_participantes SET NOT NULL');
        DB::statement('ALTER TABLE raf_consulta_pendente ALTER COLUMN valor_total_consulta SET NOT NULL');
        DB::statement('ALTER TABLE raf_consulta_pendente ALTER COLUMN custo_unitario SET NOT NULL');
        DB::statement('ALTER TABLE raf_consulta_pendente ALTER COLUMN resume_url SET NOT NULL');
    }
};
