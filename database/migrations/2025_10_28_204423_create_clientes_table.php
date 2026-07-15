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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Tipo de cliente
            $table->enum('tipo_pessoa', ['PF', 'PJ'])->default('PJ');

            // Dados principais
            // Documento é único POR usuário, não global: o mesmo CNPJ pode ser
            // cliente (ou empresa própria) de contas diferentes. Unique composto
            // declarado abaixo, após todas as colunas.
            $table->string('documento'); // CPF ou CNPJ
            $table->string('nome')->nullable(); // Nome fantasia ou nome da pessoa
            $table->string('razao_social')->nullable(); // Razão social (obrigatório para PJ; null p/ PF)

            // Contato
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();

            // Dados específicos PJ
            $table->string('faturamento_anual')->nullable();
            $table->string('preparacao_reforma')->nullable();

            // Status
            $table->boolean('ativo')->default(true);

            $table->timestamps();

            // Unicidade por usuário: cada conta não pode repetir o mesmo CPF/CNPJ,
            // mas o mesmo documento pode existir em contas diferentes.
            $table->unique(['user_id', 'documento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
