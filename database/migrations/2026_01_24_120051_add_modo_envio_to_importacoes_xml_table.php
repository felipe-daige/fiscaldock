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
        Schema::table('importacoes_xml', function (Blueprint $table) {
            $table->string('modo_envio', 10)->default('xml')->after('tipo_documento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('importacoes_xml', function (Blueprint $table) {
            $table->dropColumn('modo_envio');
        });
    }
};
