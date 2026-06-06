<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('participante_scores', function (Blueprint $table) {
            $table->id();
            // Alvo do score: participante (contraparte) OU cliente (empresa gerida/própria).
            // Exatamente um dos dois é preenchido. Postgres trata NULL como distinto em UNIQUE,
            // então as duas uniques parciais convivem sem conflito.
            $table->foreignId('participante_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Scores individuais (0-100) — nullable: null = categoria NÃO avaliada
            // (fonte não consultada / INDETERMINADA), distinto de 0 (= regular/ótimo).
            $table->smallInteger('score_cadastral')->nullable();
            $table->smallInteger('score_cnd_federal')->nullable();
            $table->smallInteger('score_cnd_estadual')->nullable();
            $table->smallInteger('score_fgts')->nullable();
            $table->smallInteger('score_trabalhista')->nullable();
            $table->smallInteger('score_compliance')->nullable();  // CGU CNC (CEIS/CNEP/CEPIM) + CNJ
            $table->smallInteger('score_esg')->nullable();         // dívida: sem fonte (ESG)
            $table->smallInteger('score_protestos')->nullable();   // dívida: sem fonte (protestos)

            // Score consolidado — null = nada avaliado
            $table->smallInteger('score_total')->nullable();
            $table->string('classificacao', 20)->nullable();  // baixo, medio, alto, critico, nao_avaliado

            // Metadata
            $table->timestamp('ultima_consulta_em')->nullable();
            $table->timestamp('proxima_consulta_em')->nullable();
            $table->jsonb('dados_consultados')->nullable();

            $table->timestamps();

            // Indices — uniques parciais por alvo (NULLs são distintos no Postgres)
            $table->unique('participante_id');
            $table->unique('cliente_id');
            $table->index('user_id');
            $table->index('score_total');
            $table->index('classificacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participante_scores');
    }
};
