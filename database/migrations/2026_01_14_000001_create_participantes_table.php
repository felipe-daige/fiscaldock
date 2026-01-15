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
        // Tabela principal de participantes (Monitoramento)
        Schema::create('participantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->string('cnpj', 14)->index();
            $table->string('razao_social')->nullable();
            $table->string('nome_fantasia')->nullable();
            $table->string('situacao_cadastral')->nullable();
            $table->string('regime_tributario')->nullable();
            $table->string('origem_tipo'); // SPED_EFD_FISCAL, SPED_EFD_CONTRIB, NFE, NFSE, MANUAL
            $table->json('origem_ref')->nullable(); // {"raf_relatorio_id": 123}
            $table->timestamp('ultima_consulta_em')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'cnpj']); // CNPJ único por usuário
        });

        // Tabela de grupos de participantes
        Schema::create('participante_grupos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nome');
            $table->string('cor', 7)->nullable(); // Cor do badge (hex: #RRGGBB)
            $table->text('descricao')->nullable();
            $table->boolean('is_auto')->default(false); // Grupo criado automaticamente (ex: importação SPED)
            $table->timestamps();

            $table->index(['user_id', 'nome']);
        });

        // Tabela pivot para relação many-to-many
        Schema::create('participante_grupo_participante', function (Blueprint $table) {
            $table->foreignId('participante_id')->constrained('participantes')->onDelete('cascade');
            $table->foreignId('participante_grupo_id')->constrained('participante_grupos')->onDelete('cascade');
            $table->timestamps();

            $table->primary(['participante_id', 'participante_grupo_id'], 'participante_grupo_pk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participante_grupo_participante');
        Schema::dropIfExists('participante_grupos');
        Schema::dropIfExists('participantes');
    }
};
