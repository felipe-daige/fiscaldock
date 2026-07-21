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
        Schema::create('consulta_lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('cliente_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('plano_id')->nullable()->constrained('monitoramento_planos')->onDelete('restrict');
            $table->string('status', 20)->default('pendente'); // pendente, processando, concluido, erro
            $table->integer('total_participantes');
            $table->decimal('creditos_cobrados', 12, 2)->default(0);
            $table->string('tab_id', 36)->nullable();
            $table->jsonb('resultado_resumo')->nullable();
            // Lote avulso por fontes (vertical advocacia): chaves selecionadas à la carte.
            // NULL = lote de plano (plano_id set) ou de clearance (plano_id null + fontes null).
            $table->jsonb('fontes_selecionadas')->nullable();
            $table->string('error_code', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('processado_em')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('tab_id');
        });

        Schema::create('consulta_lote_participantes', function (Blueprint $table) {
            $table->foreignId('consulta_lote_id')->constrained('consulta_lotes')->onDelete('cascade');
            $table->foreignId('participante_id')->constrained('participantes')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();

            $table->primary(['consulta_lote_id', 'participante_id']);
        });

        // Kits da consulta avulsa por fontes (vertical advocacia, fase 3): preset NOMEADO de
        // seleção com desconto — dado editável no admin, NÃO é plano/entidade de billing. O
        // desconto só se aplica quando a seleção do usuário bate exatamente com as fontes do kit.
        Schema::create('consulta_kits', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 120);
            $table->string('slug', 60)->unique();
            $table->string('descricao', 255)->nullable();
            $table->jsonb('fontes'); // chaves de fonte (mesmo vocabulário do FonteRegistry)
            $table->decimal('desconto_percentual', 5, 2)->default(0);
            $table->boolean('ativo')->default(true);
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consulta_kits');
        Schema::dropIfExists('consulta_lote_participantes');
        Schema::dropIfExists('consulta_lotes');
    }
};
