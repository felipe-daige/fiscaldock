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
        Schema::create('notas_sped', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('importacao_sped_id')->constrained('importacoes_sped')->cascadeOnDelete();
            $table->foreignId('emit_participante_id')->nullable()->constrained('participantes')->nullOnDelete();
            $table->foreignId('dest_participante_id')->nullable()->constrained('participantes')->nullOnDelete();

            // Dados basicos da nota (extraidos do SPED)
            $table->string('tipo_efd', 30); // EFD_FISCAL, EFD_CONTRIB
            $table->string('registro', 10); // C100, C170, M100, etc.
            $table->smallInteger('tipo_nota'); // 0=entrada, 1=saida
            $table->string('modelo_doc', 2)->nullable(); // 55=NFe, 57=CTe, etc.
            $table->string('serie', 3)->nullable();
            $table->string('numero_nota', 20)->nullable();
            $table->string('chave_acesso', 44)->nullable()->index();
            $table->date('data_emissao')->nullable();
            $table->date('data_entrada_saida')->nullable();

            // Valores
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->decimal('valor_icms', 15, 2)->default(0);
            $table->decimal('valor_icms_st', 15, 2)->default(0);
            $table->decimal('valor_ipi', 15, 2)->default(0);
            $table->decimal('valor_pis', 15, 2)->default(0);
            $table->decimal('valor_cofins', 15, 2)->default(0);
            $table->decimal('valor_frete', 15, 2)->default(0);
            $table->decimal('valor_desconto', 15, 2)->default(0);

            // CFOP principal (para analise)
            $table->string('cfop_principal', 4)->nullable();

            // Payload completo (opcional)
            $table->jsonb('payload')->nullable();

            // Validacao VCI (futuro)
            $table->jsonb('validacao')->nullable();

            $table->timestamps();

            // Indices
            $table->index(['user_id', 'data_emissao']);
            $table->index(['user_id', 'tipo_nota']);
            $table->index(['importacao_sped_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_sped');
    }
};
