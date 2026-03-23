<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cabeçalhos de NF-e, NFS-e, CT-e (Registros A100, C100, D100)
        Schema::create('efd_notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('participante_id')->nullable()->constrained('participantes')->nullOnDelete();
            $table->foreignId('importacao_id')->constrained('importacoes_sped')->cascadeOnDelete();
            $table->string('chave_acesso', 44)->nullable();
            $table->string('modelo', 2)->index();
            $table->bigInteger('numero');
            $table->string('serie', 10)->nullable();
            $table->date('data_emissao')->index();
            $table->enum('tipo_operacao', ['entrada', 'saida'])->index();
            $table->string('origem_arquivo')->nullable();
            $table->decimal('valor_total', 15, 2);
            $table->decimal('valor_desconto', 15, 2)->default(0);
            $table->jsonb('metadados')->nullable();
            $table->timestamps();

            $table->index(['cliente_id', 'data_emissao', 'tipo_operacao'], 'efd_notas_cliente_data_tipo_idx');
        });

        // Índice único parcial: NULL em chave_acesso = NFS-e sem chave (não conta para unicidade)
        DB::statement('
            CREATE UNIQUE INDEX efd_notas_unique_nota
            ON efd_notas (cliente_id, chave_acesso, modelo, numero, serie)
            WHERE chave_acesso IS NOT NULL
        ');

        // Itens/produtos (Registros A170, C170)
        Schema::create('efd_notas_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('efd_nota_id')->constrained('efd_notas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('numero_item');
            $table->string('codigo_item')->index();
            $table->text('descricao');
            $table->decimal('quantidade', 15, 4)->nullable();
            $table->string('unidade_medida', 6)->nullable();
            $table->decimal('valor_unitario', 15, 4)->nullable();
            $table->decimal('valor_total', 15, 2);
            $table->integer('cfop')->nullable()->index();
            $table->string('cst_icms', 10)->nullable();
            $table->decimal('aliquota_icms', 10, 4)->nullable();
            $table->decimal('valor_icms', 15, 2)->nullable();
            $table->string('cst_pis', 10)->nullable();
            $table->decimal('aliquota_pis', 10, 4)->nullable();
            $table->decimal('valor_pis', 15, 2)->nullable();
            $table->string('cst_cofins', 10)->nullable();
            $table->decimal('aliquota_cofins', 10, 4)->nullable();
            $table->decimal('valor_cofins', 15, 2)->nullable();
            $table->jsonb('metadados')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS efd_notas_unique_nota');
        Schema::dropIfExists('efd_notas_itens');
        Schema::dropIfExists('efd_notas');
    }
};
