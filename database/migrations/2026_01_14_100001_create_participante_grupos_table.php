<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Migration consolidada em 2026_01_14_000001_create_participantes_table.php
 *
 * Este arquivo é mantido vazio para preservar o histórico de migrations já executadas.
 * As tabelas participante_grupos e participante_grupo_participante agora são criadas
 * na migration principal de participantes.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Conteúdo movido para create_participantes_table.php
    }

    public function down(): void
    {
        // Conteúdo movido para create_participantes_table.php
    }
};
