<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sped_importacoes', function (Blueprint $table) {
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('sped_importacoes', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Cliente::class);
            $table->dropColumn('cliente_id');
        });
    }
};
