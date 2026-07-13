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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sobrenome');
            $table->string('telefone');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            // Troca de e-mail no perfil: só vira `email` depois de confirmado no e-mail NOVO
            // (link assinado). Até lá o e-mail antigo continua válido pra login.
            $table->string('pending_email')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->decimal('credits', 12, 2)->default(0);
            $table->string('empresa')->nullable();
            $table->string('cargo')->nullable();
            $table->string('cnpj', 18)->nullable();
            $table->string('faturamento_anual')->nullable();
            $table->string('desafio_principal')->nullable();
            $table->timestamp('terms_accepted_at')->nullable();
            $table->boolean('marketing_opt_in')->default(false);
            $table->timestamp('marketing_opt_in_at')->nullable();
            // LGPD fase 2: pedido de exclusão de conta (direito do titular). Flag, não hard-delete —
            // o processamento/anonimização respeita a retenção fiscal de SPED/XML.
            $table->timestamp('deletion_requested_at')->nullable();
            // LGPD fase 2.2: versão dos documentos legais aceitos (force re-aceite quando sobe).
            // Backfill p/ '1.0' nos usuários existentes — eles já aceitaram a única versão que existiu.
            $table->string('terms_version')->nullable();
            $table->string('privacy_version')->nullable();
            // LGPD fase 2.2: marca quando a PII do titular foi anonimizada (comando lgpd:processar-exclusoes).
            $table->timestamp('anonimizado_em')->nullable();
            // Operador FiscalDock (acesso ao painel admin §6.1). Ligado manualmente via SQL.
            $table->boolean('is_admin')->default(false);
            $table->boolean('trial_used')->default(false);
            $table->timestamp('trial_started_at')->nullable();
            $table->timestamp('trial_expires_at')->nullable();
            $table->unsignedInteger('trial_credits_granted')->default(0);
            $table->unsignedInteger('trial_credits_remaining')->default(0);
            $table->unsignedInteger('trial_credits_expired')->default(0);
            $table->string('trial_source')->nullable();
            $table->boolean('alertas_operacionais')->default(true);
            $table->boolean('alertas_monitoramento')->default(true);
            $table->boolean('resumo_periodico')->default(true);
            // Frequência de notificação (gerenciável em /app/configuracoes):
            // severidade mínima para e-mail imediato ('media' = alta+média, 'alta' = só alta)
            // e cadência do resumo periódico ('semanal' | 'mensal').
            $table->string('alertas_severidade_minima')->default('media');
            $table->string('resumo_frequencia')->default('semanal');
            // Âncora + guarda de idempotência do resumo: a janela do próximo resumo começa
            // onde o último terminou (janela fixa de 7/30 dias deixava buraco quando o
            // intervalo entre 1as segundas era 35 dias), e um 2º run no mesmo período não
            // reenvia.
            $table->timestamp('ultimo_resumo_em')->nullable();
            $table->jsonb('dashboard_prefs')->nullable();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('landing_leads', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('origem')->default('banner_contato');
            $table->string('user_agent', 500)->nullable();
            $table->ipAddress('ip')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_leads');
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
