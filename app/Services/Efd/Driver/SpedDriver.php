<?php

namespace App\Services\Efd\Driver;

use App\Services\Efd\Handlers\SpedRegistroHandler;

/**
 * Driver de um tipo de EFD (L2). Diz QUAIS registros tratar (via handlers) e o
 * `origem_arquivo` do tipo. O núcleo (L1/L3/L4) é agnóstico ao driver — plugar um
 * novo tipo (PIS/COFINS) = 1 driver + seus handlers, zero linha do núcleo.
 */
interface SpedDriver
{
    /**
     * Handlers do driver. DEVEM ser as MESMAS instâncias entre chamadas (handlers
     * agregadores guardam estado durante o job).
     *
     * @return SpedRegistroHandler[]
     */
    public function handlers(): array;

    /** Tag de `origem_arquivo` nas notas: 'fiscal' | 'contribuicoes'. */
    public function origemArquivo(): string;

    /** tipo_efd canônico atendido (ex.: 'EFD ICMS/IPI'). */
    public function tipoEfd(): string;
}
