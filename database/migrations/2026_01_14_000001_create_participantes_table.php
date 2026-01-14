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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participantes');
    }
};
