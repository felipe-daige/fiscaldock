<?php

namespace App\Services\Efd\Handlers;

use App\Services\Efd\Sped\Contexto;
use App\Services\Efd\Sped\SpedRecord;

/**
 * Mapeia um registro SPED (+ documento-pai corrente) para uma linha de tabela.
 * L2 — por tipo de EFD, mesma interface (motor-laravel.md §L2).
 *
 * O handler devolve APENAS as colunas deriváveis do registro. As colunas de escopo
 * de importação (user_id, cliente_id, importacao_id, origem_arquivo) e as FKs
 * resolvidas (efd_nota_id via pai, participante_id via resolver) são preenchidas
 * pela PersistenciaEngine/Job (F3). Valores jsonb voltam como array PHP.
 */
interface SpedRegistroHandler
{
    /**
     * Códigos de registro SPED que este handler trata.
     *
     * @return string[]
     */
    public function registros(): array;

    /** Tabela-alvo (schema atual — nunca muda). */
    public function tabela(): string;

    /**
     * Registro → linha (coluna => valor), ou null para pular.
     *
     * @return array<string, mixed>|null
     */
    public function mapear(SpedRecord $rec, ?Contexto $pai): ?array;
}
