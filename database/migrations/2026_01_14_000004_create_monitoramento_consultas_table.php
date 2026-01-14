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
        Schema::create('monitoramento_consultas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('participante_id')->constrained('participantes')->onDelete('cascade');
            $table->foreignId('plano_id')->constrained('monitoramento_planos');
            $table->foreignId('assinatura_id')->nullable()->constrained('monitoramento_assinaturas')->onDelete('set null');
            $table->enum('tipo', ['assinatura', 'avulso']);
            $table->enum('status', ['pendente', 'processando', 'sucesso', 'erro'])->default('pendente');
            $table->json('resultado')->nullable(); // Dados retornados pelo n8n
            $table->integer('creditos_cobrados')->default(0);
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('executado_em')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoramento_consultas');
    }
};
