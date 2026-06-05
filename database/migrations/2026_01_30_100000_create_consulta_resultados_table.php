<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the consulta_resultados table to store individual consultation
     * results per participante. This enables on-demand report generation
     * (CSV/PDF) in Laravel instead of receiving pre-generated CSV from n8n.
     */
    public function up(): void
    {
        Schema::create('consulta_resultados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_lote_id')->constrained('consulta_lotes')->cascadeOnDelete();
            // Consulta serve DOIS escopos: participante (contraparte) OU cliente (empresa
            // gerida, incl. empresa própria). Exatamente um dos dois é preenchido.
            $table->foreignId('participante_id')->nullable()->constrained('participantes')->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->cascadeOnDelete();
            $table->jsonb('resultado_dados')->nullable(); // All consultation data unified
            $table->string('status', 20)->default('pendente'); // pendente, sucesso, erro, timeout
            $table->text('error_message')->nullable();
            $table->timestamp('consultado_em')->nullable();
            $table->timestamps();

            // Index for filtering by status within a lote
            $table->index(['consulta_lote_id', 'status']);
        });

        // Unicidade por escopo (parcial): cada participante/cliente uma vez por lote.
        \Illuminate\Support\Facades\DB::statement('CREATE UNIQUE INDEX consulta_resultados_lote_participante_uq ON consulta_resultados (consulta_lote_id, participante_id) WHERE participante_id IS NOT NULL');
        \Illuminate\Support\Facades\DB::statement('CREATE UNIQUE INDEX consulta_resultados_lote_cliente_uq ON consulta_resultados (consulta_lote_id, cliente_id) WHERE cliente_id IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consulta_resultados');
    }
};
