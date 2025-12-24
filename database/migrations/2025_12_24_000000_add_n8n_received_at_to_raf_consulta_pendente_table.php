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
        Schema::table('raf_consulta_pendente', function (Blueprint $table) {
            $table->timestamp('n8n_received_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raf_consulta_pendente', function (Blueprint $table) {
            $table->dropColumn('n8n_received_at');
        });
    }
};

