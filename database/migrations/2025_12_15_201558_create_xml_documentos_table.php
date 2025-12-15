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
        Schema::create('xml_documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->onDelete('cascade');
            $table->string('chave_acesso')->unique();
            $table->string('cnpj_emitente');
            $table->string('cnpj_destinatario')->nullable();
            $table->date('data_emissao');
            $table->decimal('valor_total', 15, 2);
            $table->string('cfop')->nullable();
            $table->enum('tipo_documento', ['nfe', 'nfse'])->default('nfe');
            $table->enum('status', ['pendente', 'processado', 'aceito', 'rejeitado'])->default('pendente');
            $table->string('arquivo_path');
            $table->json('dados_extrados')->nullable();
            $table->timestamps();
            
            $table->index('cnpj_emitente');
            $table->index('status');
            $table->index('data_emissao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xml_documentos');
    }
};
