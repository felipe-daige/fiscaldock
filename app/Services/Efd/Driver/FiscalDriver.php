<?php

namespace App\Services\Efd\Driver;

use App\Services\Efd\Handlers\Handler0150;
use App\Services\Efd\Handlers\Handler0200;
use App\Services\Efd\Handlers\HandlerApuracaoE;
use App\Services\Efd\Handlers\HandlerC100;
use App\Services\Efd\Handlers\HandlerC170;
use App\Services\Efd\Handlers\HandlerC190;
use App\Services\Efd\Handlers\HandlerD100;
use App\Services\Efd\Handlers\HandlerD190;
use App\Services\Efd\Handlers\SpedRegistroHandler;

/**
 * Driver EFD ICMS/IPI (L2). Registra os 8 handlers fiscais. Instâncias construídas
 * uma vez e reusadas (o HandlerApuracaoE é agregador com estado) — logo cada job
 * deve criar um FiscalDriver novo.
 */
class FiscalDriver implements SpedDriver
{
    /** @var SpedRegistroHandler[] */
    private array $handlers;

    public function __construct()
    {
        $this->handlers = [
            new Handler0150,
            new Handler0200,
            new HandlerC100,
            new HandlerC170,
            new HandlerC190,
            new HandlerD100,
            new HandlerD190,
            new HandlerApuracaoE,
        ];
    }

    public function handlers(): array
    {
        return $this->handlers;
    }

    public function origemArquivo(): string
    {
        return 'fiscal';
    }

    public function tipoEfd(): string
    {
        return 'EFD ICMS/IPI';
    }

    /**
     * União dos registros tratados (conveniência p/ roteamento na engine).
     *
     * @return string[]
     */
    public function registros(): array
    {
        return array_merge(...array_map(fn (SpedRegistroHandler $h): array => $h->registros(), $this->handlers));
    }
}
