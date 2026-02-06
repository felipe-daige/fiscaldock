<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Remove legacy RAF tables that are no longer used.
     * The new architecture uses consulta_lotes and consulta_lote_resultados tables.
     */
    public function up(): void
    {
        Schema::dropIfExists('raf_participantes');
        Schema::dropIfExists('raf_relatorio_processado');
        Schema::dropIfExists('raf_consulta_pendente');
    }

    /**
     * Reverse the migrations.
     *
     * Note: This down() method does NOT recreate the tables.
     * The legacy RAF system has been permanently removed.
     */
    public function down(): void
    {
        // Tables are not recreated - legacy system removed
    }
};
