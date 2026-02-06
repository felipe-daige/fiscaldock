<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Padroniza nomes de tabelas usando prefixo por origem (xml_*, sped_*).
     *
     * Mudanças:
     * - importacoes_xml → xml_importacoes
     * - notas_fiscais → xml_notas
     * - importacoes_sped → sped_importacoes
     * - notas_sped → sped_notas
     *
     * Nota: xml_chaves_processadas será dropada em migration separada
     * (tabela redundante - dados já existem em xml_notas)
     */
    public function up(): void
    {
        // XML tables
        Schema::rename('importacoes_xml', 'xml_importacoes');
        Schema::rename('notas_fiscais', 'xml_notas');

        // SPED tables
        Schema::rename('importacoes_sped', 'sped_importacoes');
        Schema::rename('notas_sped', 'sped_notas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // XML tables (restore original names)
        Schema::rename('xml_importacoes', 'importacoes_xml');
        Schema::rename('xml_notas', 'notas_fiscais');

        // SPED tables (restore original names)
        Schema::rename('sped_importacoes', 'importacoes_sped');
        Schema::rename('sped_notas', 'notas_sped');
    }
};
