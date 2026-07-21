<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the consulta_resultados table to store individual consultation
     * results per participante. This enables on-demand report generation
     * (CSV/PDF) in Laravel instead of receiving pre-generated CSV from n8n.
     */
    public function up(): void
    {
        Schema::create('consulta_resultados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consulta_lote_id')->constrained('consulta_lotes')->cascadeOnDelete();
            // Consulta serve DOIS escopos: participante (contraparte) OU cliente (empresa
            // gerida, incl. empresa própria). Exatamente um dos dois é preenchido.
            $table->foreignId('participante_id')->nullable()->constrained('participantes')->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->cascadeOnDelete();
            $table->jsonb('resultado_dados')->nullable(); // All consultation data unified
            $table->string('status', 20)->default('pendente'); // pendente, sucesso, erro, timeout
            $table->text('error_message')->nullable();
            $table->timestamp('consultado_em')->nullable();
            $table->timestamps();

            // Index for filtering by status within a lote
            $table->index(['consulta_lote_id', 'status']);
        });

        // Unicidade por escopo (parcial): cada participante/cliente uma vez por lote.
        \Illuminate\Support\Facades\DB::statement('CREATE UNIQUE INDEX consulta_resultados_lote_participante_uq ON consulta_resultados (consulta_lote_id, participante_id) WHERE participante_id IS NOT NULL');
        \Illuminate\Support\Facades\DB::statement('CREATE UNIQUE INDEX consulta_resultados_lote_cliente_uq ON consulta_resultados (consulta_lote_id, cliente_id) WHERE cliente_id IS NOT NULL');

        // Registro canônico de certidões EMITIDAS (fiscal + judicial — vertical advocacia,
        // docs/advocacia/consultas-certidoes.md fase 2). 1 linha por (user, documento, fonte):
        // upsert da emissão mais recente; histórico completo permanece em consulta_resultados.
        // `valida_ate` alimenta os alertas de vencimento (15/7/1 dias); `arquivo_path` aponta o
        // PDF já arquivado em Meus Arquivos pelo ComprovanteArquivador (site_receipt expira em 7d).
        Schema::create('certidoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('participante_id')->nullable()->constrained('participantes')->nullOnDelete();
            $table->string('alvo_tipo', 20);        // participante | cliente
            $table->string('alvo_documento', 14);   // CNPJ só dígitos
            $table->string('tipo', 40);             // chave da fonte (certidao_stj, cnd_federal, ...)
            $table->string('orgao', 160)->nullable();
            $table->string('status', 40);           // Negativa | Positiva | PEND (vocabulário das fontes)
            $table->string('certidao_codigo', 120)->nullable();
            $table->date('emitida_em')->nullable();
            $table->date('valida_ate')->nullable();
            $table->string('validade_origem', 20)->nullable(); // resposta | regra_orgao
            $table->string('arquivo_path', 255)->nullable();
            $table->foreignId('consulta_lote_id')->nullable()->constrained('consulta_lotes')->nullOnDelete();
            $table->timestamps();

            // alvo_tipo na chave: o mesmo CNPJ pode ser participante E cliente do usuário.
            $table->unique(['user_id', 'alvo_tipo', 'alvo_documento', 'tipo']);
            $table->index(['user_id', 'valida_ate']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certidoes');
        Schema::dropIfExists('consulta_resultados');
    }
};
