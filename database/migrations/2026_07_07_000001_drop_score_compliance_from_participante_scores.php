<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Remove a coluna `score_compliance` — as fontes CGU CNC e CNJ Improbidade foram
 * descontinuadas (2026-07-07). O escopo de fontes InfoSimples ficou fixo em 6
 * (CND Federal/Estadual/Municipal, CRF FGTS, CNDT, SINTEGRA) e o score de risco
 * nunca ponderou essa categoria. Nada mais lê/escreve a coluna.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('participante_scores', 'score_compliance')) {
            Schema::table('participante_scores', function (Blueprint $table) {
                $table->dropColumn('score_compliance');
            });
        }

        // Última migration estrutural: instala a invariável cross-table depois que todas as
        // tabelas consumidoras de participante_id/cliente_id já existem. Em bancos existentes,
        // o comando identidades:consolidar-cliente-participante aplica o mesmo SQL.
        DB::unprepared(file_get_contents(database_path('sql/identidade_cliente_participante.sql')));
    }

    public function down(): void
    {
        DB::unprepared('
            DROP TRIGGER IF EXISTS clientes_identidade_exclusiva_merge ON clientes;
            DROP TRIGGER IF EXISTS clientes_identidade_exclusiva_lock ON clientes;
            DROP TRIGGER IF EXISTS participantes_identidade_exclusiva_guard ON participantes;
            DROP FUNCTION IF EXISTS fiscaldock_consolidar_cliente_exclusivo();
            DROP FUNCTION IF EXISTS fiscaldock_travar_cliente_exclusivo();
            DROP FUNCTION IF EXISTS fiscaldock_guardar_participante_exclusivo();
            DROP FUNCTION IF EXISTS fiscaldock_consolidar_participante_cliente(BIGINT, BIGINT);
            DROP FUNCTION IF EXISTS fiscaldock_travar_identidade(BIGINT, TEXT);
            DROP FUNCTION IF EXISTS fiscaldock_documento_normalizado(TEXT);
        ');

        if (! Schema::hasColumn('participante_scores', 'score_compliance')) {
            Schema::table('participante_scores', function (Blueprint $table) {
                $table->smallInteger('score_compliance')->nullable()->after('score_trabalhista');
            });
        }
    }
};
