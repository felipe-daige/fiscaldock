<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('empresa', 255)->nullable()->after('telefone');
            $table->string('cargo', 255)->nullable()->after('empresa');
            $table->string('cnpj', 18)->nullable()->after('cargo');
            $table->string('faturamento_anual', 50)->nullable()->after('cnpj');
            $table->string('desafio_principal', 100)->nullable()->after('faturamento_anual');
        });

        // Backfill: preencher dados da empresa a partir de clientes com is_empresa_propria = true
        DB::statement("
            UPDATE users
            SET empresa = c.nome,
                cnpj = c.documento,
                faturamento_anual = c.faturamento_anual
            FROM clientes c
            WHERE c.user_id = users.id
              AND c.is_empresa_propria = true
        ");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['empresa', 'cargo', 'cnpj', 'faturamento_anual', 'desafio_principal']);
        });
    }
};
