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
        Schema::create('xml_lancamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('xml_documento_id')->constrained('xml_documentos')->onDelete('cascade');
            $table->string('natureza_operacao');
            $table->string('conta_debito')->nullable(); // Referência futura a plano de contas
            $table->string('conta_credito')->nullable();
            $table->decimal('valor', 15, 2);
            $table->date('data_competencia');
            $table->enum('status', ['sugerido', 'aceito', 'gerado'])->default('sugerido');
            $table->timestamps();
            
            $table->index('status');
            $table->index('data_competencia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xml_lancamentos');
    }
};
