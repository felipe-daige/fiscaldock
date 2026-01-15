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
        Schema::create('raf_participantes', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('raf_relatorio_processado_id')
                  ->constrained('raf_relatorio_processado')
                  ->cascadeOnDelete();
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->foreignId('cliente_id')
                  ->nullable()
                  ->constrained('clientes')
                  ->nullOnDelete();

            // Tipo de consulta
            $table->string('tipo_efd', 30);       // 'EFD Fiscal' ou 'EFD Contribuições'
            $table->string('modalidade', 20);     // 'gratuito' ou 'completa'

            // Dados da empresa consultante (dona do SPED)
            $table->string('consultante_cnpj', 14);
            $table->string('consultante_razao_social', 255)->nullable();

            // Dados do participante/fornecedor
            $table->string('cnpj', 14);
            $table->string('cnpj_matriz', 14)->nullable();
            $table->string('razao_social', 255)->nullable();
            $table->string('situacao_cadastral', 50)->nullable();
            $table->string('regime_tributario', 50)->nullable();
            $table->text('cnae_descricao')->nullable();

            // Dados CND (só em consultas completas)
            $table->string('cnd_situacao', 50)->nullable();
            $table->string('cnd_tipo', 100)->nullable();
            $table->date('cnd_data_emissao')->nullable();
            $table->date('cnd_data_validade')->nullable();
            $table->string('cnd_codigo_controle', 100)->nullable();
            $table->text('cnd_informacoes_adicionais')->nullable();

            // Período da análise
            $table->date('data_inicio')->nullable();
            $table->date('data_final')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('cnpj');
            $table->index('consultante_cnpj');
            $table->index(['tipo_efd', 'modalidade']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raf_participantes');
    }
};
