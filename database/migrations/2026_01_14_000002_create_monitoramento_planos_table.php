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
        Schema::create('monitoramento_planos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // basico, cadastral, fiscal_federal, fiscal_completo, due_diligence
            $table->string('nome');
            $table->text('descricao');
            $table->json('consultas_incluidas'); // ["cnpj", "simples", "sintegra", "pgfn", "fgts", ...]
            $table->integer('custo_creditos');
            $table->boolean('is_gratuito')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoramento_planos');
    }
};
