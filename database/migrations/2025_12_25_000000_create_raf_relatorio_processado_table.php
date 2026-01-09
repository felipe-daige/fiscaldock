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
        Schema::create('raf_relatorio_processado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->string('document_type');
            $table->string('consultant_type');
            $table->string('filename');
            $table->text('report_csv_base64');
            $table->string('resume_url')->nullable();
            $table->integer('total_participants');
            $table->decimal('total_price', 10, 2);
            $table->string('cnpj_empresa_analisada')->nullable();
            $table->string('razao_social_empresa')->nullable();
            $table->date('data_inicial_analisada')->nullable();
            $table->date('data_final_analisada')->nullable();
            $table->integer('total_fornecedores')->default(0);
            $table->integer('qnt_fornecedores_cnpj')->default(0);
            $table->integer('qnt_fornecedores_cpf')->default(0);
            // Situação Cadastral
            $table->integer('qnt_situacao_nula')->default(0);
            $table->integer('qnt_situacao_ativa')->default(0);
            $table->integer('qnt_situacao_suspensa')->default(0);
            $table->integer('qnt_situacao_inapta')->default(0);
            $table->integer('qnt_situacao_baixada')->default(0);
            // Regime Tributário
            $table->integer('qnt_simples')->default(0);
            $table->integer('qnt_presumido')->default(0);
            $table->integer('qnt_real')->default(0);
            $table->integer('qnt_regime_indeterminado')->default(0);
            // CND
            $table->integer('qnt_cnd_regular')->default(0);
            $table->integer('qnt_cnd_pendencia')->default(0);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raf_relatorio_processado');
    }
};

