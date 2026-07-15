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
        Schema::create('monitoramento_assinaturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('participante_id')->nullable()->constrained('participantes')->onDelete('cascade');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('cascade');
            // 3º alvo possível: GRUPO (monitoramento dinâmico — cada ciclo consulta os membros
            // atuais). Excluir o grupo cancela a assinatura (cascade de negócio).
            $table->foreignId('grupo_id')->nullable()->constrained('participantes_grupos')->onDelete('cascade');
            $table->foreignId('plano_id')->constrained('monitoramento_planos');
            $table->enum('status', ['ativo', 'pausado', 'cancelado'])->default('ativo');
            $table->string('pausada_motivo')->nullable(); // manual | saldo | falhas — null quando ativa
            $table->integer('frequencia_dias')->default(30); // 30 = mensal
            $table->timestamp('proxima_execucao_em')->nullable();
            $table->timestamp('ultima_execucao_em')->nullable();
            $table->timestamps();

            $table->unique(['participante_id', 'plano_id']); // Um participante só pode ter uma assinatura por plano
            $table->unique(['cliente_id', 'plano_id']);       // Um cliente só pode ter uma assinatura por plano
            $table->unique(['grupo_id', 'plano_id']);         // Um grupo só pode ter uma assinatura por plano
        });

        if (! Schema::hasTable('account_subscriptions')) {
            Schema::create('account_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
                // sem cascade: arquivar plano via is_active, nunca hard-delete (RESTRICT protege assinaturas)
                $table->foreignId('subscription_plan_id')->constrained('subscription_plans');
                // string (não enum) de propósito: estados de cobrança podem crescer sem ALTER de constraint
                $table->string('status')->default('ativa'); // pendente, ativa, trial, cancelada, inadimplente
                $table->string('ciclo')->default('mensal');  // mensal, anual
                $table->timestamp('iniciada_em')->nullable();
                $table->timestamp('renova_em')->nullable();
                $table->decimal('creditos_inclusos_saldo', 12, 2)->default(0);
                $table->decimal('limite_consumo_automatico', 12, 2)->nullable(); // cap do cliente em R$; null = default
                $table->integer('assentos_extras')->default(0);
                $table->string('mp_preapproval_id')->nullable()->unique(); // id do preapproval (assinatura) no MP
                $table->timestamp('proximo_grant_em')->nullable();         // quando o scheduler concede o próximo mês
                $table->timestamp('ultimo_grant_em')->nullable();          // última concessão (guard de idempotência)
                // Proration da troca de plano: fração do ciclo ainda não usada no momento da troca.
                // Setado em TrocarPlanoMercadoPago, consumido/limpo por ConcederCreditosService na
                // 1ª concessão do tier destino (expira antigo pro-rata + concede novo pro-rata).
                $table->jsonb('proration_pendente')->nullable();
                $table->timestamps();
            });
        }

        // Coluna de proration (idempotente): cobre bancos onde account_subscriptions já existia.
        if (Schema::hasTable('account_subscriptions')
            && ! Schema::hasColumn('account_subscriptions', 'proration_pendente')) {
            Schema::table('account_subscriptions', function (Blueprint $table) {
                $table->jsonb('proration_pendente')->nullable()->after('ultimo_grant_em');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_subscriptions');
        Schema::dropIfExists('monitoramento_assinaturas');
    }
};
