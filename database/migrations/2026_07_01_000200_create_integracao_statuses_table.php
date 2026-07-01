<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integracao_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->unique();
            $table->string('nome');
            $table->string('grupo', 20);
            $table->smallInteger('ordem')->default(0);
            $table->string('status', 20)->default('operacional');
            $table->text('mensagem')->nullable();
            $table->foreignId('atualizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('grupo');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integracao_statuses');
    }
};
