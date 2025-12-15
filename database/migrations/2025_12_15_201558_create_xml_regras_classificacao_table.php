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
        Schema::create('xml_regras_classificacao', function (Blueprint $table) {
            $table->id();
            $table->string('nome_regra');
            $table->json('condicoes'); // {cnpj_fornecedor, cfop, regime_tributario}
            $table->json('acao'); // {natureza_operacao, conta_debito, conta_credito}
            $table->integer('prioridade')->default(0);
            $table->boolean('ativo')->default(true);
            $table->integer('vezes_usada')->default(0);
            $table->timestamps();
            
            $table->index('ativo');
            $table->index('prioridade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xml_regras_classificacao');
    }
};
