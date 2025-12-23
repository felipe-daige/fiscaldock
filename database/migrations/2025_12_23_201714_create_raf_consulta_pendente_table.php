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
        Schema::create('raf_consulta_pendente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('tipo_efd');
            $table->string('tipo_consulta');
            $table->integer('qtd_participantes');
            $table->decimal('valor_total_consulta', 10, 2);
            $table->decimal('custo_unitario', 10, 2);
            $table->string('resume_url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raf_consulta_pendente');
    }
};

