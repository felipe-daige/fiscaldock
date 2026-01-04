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
            $table->string('tab_id', 36)->nullable();
            $table->string('tipo_efd')->nullable();
            $table->string('tipo_consulta')->nullable();
            $table->integer('qtd_participantes')->nullable();
            $table->decimal('valor_total_consulta', 10, 2)->nullable();
            $table->decimal('custo_unitario', 10, 2)->nullable();
            $table->string('resume_url')->nullable();
            $table->string('status')->default('pending')->after('resume_url');
            $table->string('error_code')->nullable()->after('status');
            $table->text('error_message')->nullable()->after('error_code');
            $table->boolean('credits_refunded')->default(false)->after('error_message');
            $table->timestamp('error_at')->nullable()->after('credits_refunded');
            $table->timestamps();
            $table->timestamp('n8n_received_at')->nullable();
            $table->timestamp('processing_started_at')->nullable();
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

