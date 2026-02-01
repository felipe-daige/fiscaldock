<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Renames RAF tables to Consulta nomenclature:
     * - raf_lotes -> consulta_lotes
     * - raf_lote_participantes -> consulta_lote_participantes
     * - raf_lote_resultados -> consulta_resultados
     */
    public function up(): void
    {
        // Step 1: Drop existing foreign key constraints
        Schema::table('raf_lote_participantes', function (Blueprint $table) {
            $table->dropForeign(['raf_lote_id']);
        });

        Schema::table('raf_lote_resultados', function (Blueprint $table) {
            $table->dropForeign(['raf_lote_id']);
        });

        // Step 2: Rename tables
        Schema::rename('raf_lotes', 'consulta_lotes');
        Schema::rename('raf_lote_participantes', 'consulta_lote_participantes');
        Schema::rename('raf_lote_resultados', 'consulta_resultados');

        // Step 3: Rename columns in pivot and results tables
        Schema::table('consulta_lote_participantes', function (Blueprint $table) {
            $table->renameColumn('raf_lote_id', 'consulta_lote_id');
        });

        Schema::table('consulta_resultados', function (Blueprint $table) {
            $table->renameColumn('raf_lote_id', 'consulta_lote_id');
        });

        // Step 4: Recreate foreign key constraints with new names
        Schema::table('consulta_lote_participantes', function (Blueprint $table) {
            $table->foreign('consulta_lote_id')
                ->references('id')
                ->on('consulta_lotes')
                ->onDelete('cascade');
        });

        Schema::table('consulta_resultados', function (Blueprint $table) {
            $table->foreign('consulta_lote_id')
                ->references('id')
                ->on('consulta_lotes')
                ->onDelete('cascade');
        });

        // Step 5: Rename indexes (PostgreSQL)
        // Primary keys are automatically renamed with table rename
        // Rename custom indexes
        DB::statement('ALTER INDEX IF EXISTS raf_lotes_user_id_status_index RENAME TO consulta_lotes_user_id_status_index');
        DB::statement('ALTER INDEX IF EXISTS raf_lotes_tab_id_index RENAME TO consulta_lotes_tab_id_index');
        DB::statement('ALTER INDEX IF EXISTS raf_lote_resultados_raf_lote_id_status_index RENAME TO consulta_resultados_consulta_lote_id_status_index');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Drop foreign key constraints
        Schema::table('consulta_lote_participantes', function (Blueprint $table) {
            $table->dropForeign(['consulta_lote_id']);
        });

        Schema::table('consulta_resultados', function (Blueprint $table) {
            $table->dropForeign(['consulta_lote_id']);
        });

        // Step 2: Rename columns back
        Schema::table('consulta_lote_participantes', function (Blueprint $table) {
            $table->renameColumn('consulta_lote_id', 'raf_lote_id');
        });

        Schema::table('consulta_resultados', function (Blueprint $table) {
            $table->renameColumn('consulta_lote_id', 'raf_lote_id');
        });

        // Step 3: Rename tables back
        Schema::rename('consulta_lotes', 'raf_lotes');
        Schema::rename('consulta_lote_participantes', 'raf_lote_participantes');
        Schema::rename('consulta_resultados', 'raf_lote_resultados');

        // Step 4: Recreate original foreign keys
        Schema::table('raf_lote_participantes', function (Blueprint $table) {
            $table->foreign('raf_lote_id')
                ->references('id')
                ->on('raf_lotes')
                ->onDelete('cascade');
        });

        Schema::table('raf_lote_resultados', function (Blueprint $table) {
            $table->foreign('raf_lote_id')
                ->references('id')
                ->on('raf_lotes')
                ->onDelete('cascade');
        });

        // Step 5: Rename indexes back
        DB::statement('ALTER INDEX IF EXISTS consulta_lotes_user_id_status_index RENAME TO raf_lotes_user_id_status_index');
        DB::statement('ALTER INDEX IF EXISTS consulta_lotes_tab_id_index RENAME TO raf_lotes_tab_id_index');
        DB::statement('ALTER INDEX IF EXISTS consulta_resultados_consulta_lote_id_status_index RENAME TO raf_lote_resultados_raf_lote_id_status_index');
    }
};
