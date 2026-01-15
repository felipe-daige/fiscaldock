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
        Schema::create('importacoes_participantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tipo_efd', 30); // 'EFD Fiscal' ou 'EFD Contribuições'
            $table->string('filename')->nullable();
            $table->integer('total_cnpjs')->default(0);
            $table->integer('processados')->default(0);
            $table->integer('importados')->default(0);
            $table->integer('duplicados')->default(0);
            $table->string('status', 20)->default('pendente'); // pendente, processando, concluido, erro
            $table->timestamp('iniciado_em')->nullable();
            $table->timestamp('concluido_em')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('importacoes_participantes');
    }
};
