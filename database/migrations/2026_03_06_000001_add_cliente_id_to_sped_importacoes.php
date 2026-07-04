<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('efd_importacoes', function (Blueprint $table) {
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete()->after('user_id');
            $table->jsonb('resumo_final')->nullable()->after('cliente_id');
            $table->string('cnpj', 14)->nullable()->after('tipo_efd');
            $table->date('periodo_inicio')->nullable()->after('cnpj');
            $table->date('periodo_fim')->nullable()->after('periodo_inicio');
            $table->string('arquivo_hash', 64)->nullable()->after('periodo_fim');
        });

        // Adiciona coluna origem_arquivo em efd_notas (idempotente)
        if (! Schema::hasColumn('efd_notas', 'origem_arquivo')) {
            Schema::table('efd_notas', function (Blueprint $table) {
                $table->string('origem_arquivo')->nullable()->after('tipo_operacao');
            });
        }

        // Backfill: determina origem_arquivo a partir do tipo_efd da importação
        // Postgresql syntax
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("
                UPDATE efd_notas n
                SET origem_arquivo = CASE
                    WHEN imp.tipo_efd = 'EFD PIS/COFINS' THEN 'contribuicoes'
                    ELSE 'fiscal'
                END
                FROM efd_importacoes imp
                WHERE imp.id = n.importacao_id
                AND n.origem_arquivo IS NULL
            ");
        } else {
            // SQLite fallback: simple update without join
            DB::statement("
                UPDATE efd_notas
                SET origem_arquivo = 'fiscal'
                WHERE origem_arquivo IS NULL
            ");
        }

        // Cria tabela alertas (idempotente)
        if (! Schema::hasTable('alertas')) {
            Schema::create('alertas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
                $table->foreignId('participante_id')->nullable()->constrained('participantes')->nullOnDelete();
                $table->unsignedBigInteger('importacao_id')->nullable();
                $table->string('tipo', 50);
                $table->string('categoria', 30);
                $table->string('severidade', 10);
                $table->string('titulo', 255);
                $table->text('descricao');
                $table->integer('total_afetados')->default(0);
                // Materialidade: valor fiscal em risco do alerta (0 quando não monetário).
                // Permite KPI "R$ em risco" e ordenação por risco sem parsear o jsonb `detalhes`.
                $table->decimal('valor_risco', 15, 2)->default(0);
                $table->jsonb('detalhes')->nullable();
                $table->string('status', 20)->default('ativo');
                $table->smallInteger('prioridade')->default(0);
                $table->text('notas')->nullable();
                $table->timestamp('notificado_em')->nullable();
                $table->timestamp('visto_em')->nullable();
                $table->timestamp('resolvido_em')->nullable();
                $table->string('hash', 64);
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['user_id', 'categoria']);
                $table->index(['user_id', 'severidade']);
                $table->index('cliente_id');
                $table->unique(['user_id', 'hash']);
            });
        }

        // Coluna de materialidade em bancos onde `alertas` já existia (idempotente).
        if (Schema::hasTable('alertas') && ! Schema::hasColumn('alertas', 'valor_risco')) {
            Schema::table('alertas', function (Blueprint $table) {
                $table->decimal('valor_risco', 15, 2)->default(0);
            });
        }

        // Auditoria de alertas: 1 linha por transição de status (append-only).
        // user_id null = evento do sistema (auto-resolve/reativação no recalcular).
        // ator_nome é snapshot (sobrevive à exclusão do usuário). Idempotente.
        if (! Schema::hasTable('alerta_auditorias')) {
            Schema::create('alerta_auditorias', function (Blueprint $table) {
                $table->id();
                $table->foreignId('alerta_id')->constrained('alertas')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('acao', 20);            // criado, resolvido, ignorado, visto, reaberto, auto_resolvido, reativado
                $table->string('de_status', 20)->nullable();
                $table->string('para_status', 20)->nullable();
                $table->string('ator_nome', 120)->nullable(); // snapshot; null/"Sistema" = automático
                $table->text('notas')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index(['alerta_id', 'created_at']);
                $table->index(['user_id', 'created_at']);
            });
        }

        // Backstop: duas importações CONCLUÍDAS do mesmo período/cliente/tipo nunca coexistem.
        // NULLS NOT DISTINCT (PG15+) faz cliente_id NULL colidir entre si (uploads sem cliente).
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("
                CREATE UNIQUE INDEX IF NOT EXISTS efd_importacoes_periodo_unique
                ON efd_importacoes (user_id, cliente_id, tipo_efd, periodo_inicio, periodo_fim)
                NULLS NOT DISTINCT
                WHERE status = 'concluido' AND periodo_inicio IS NOT NULL
            ");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS efd_importacoes_periodo_unique');
        }
        if (Schema::hasColumn('efd_importacoes', 'arquivo_hash')) {
            Schema::table('efd_importacoes', function (Blueprint $table) {
                $table->dropColumn(['cnpj', 'periodo_inicio', 'periodo_fim', 'arquivo_hash']);
            });
        }
        Schema::dropIfExists('alerta_auditorias');
        Schema::dropIfExists('alertas');
        if (Schema::hasColumn('efd_notas', 'origem_arquivo')) {
            Schema::table('efd_notas', function (Blueprint $table) {
                $table->dropColumn('origem_arquivo');
            });
        }
        if (Schema::hasColumn('efd_importacoes', 'resumo_final')) {
            Schema::table('efd_importacoes', function (Blueprint $table) {
                $table->dropColumn('resumo_final');
            });
        }
        if (Schema::hasColumn('efd_importacoes', 'cliente_id')) {
            Schema::table('efd_importacoes', function (Blueprint $table) {
                $table->dropForeign(['cliente_id']);
                $table->dropColumn('cliente_id');
            });
        }
    }
};
