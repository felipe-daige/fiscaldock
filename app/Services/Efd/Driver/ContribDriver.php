<?php

namespace App\Services\Efd\Driver;

use App\Services\Efd\Handlers\Handler0150;
use App\Services\Efd\Handlers\Handler0200;
use App\Services\Efd\Handlers\HandlerA100;
use App\Services\Efd\Handlers\HandlerA170;
use App\Services\Efd\Handlers\HandlerApuracaoM;
use App\Services\Efd\Handlers\HandlerC100;
use App\Services\Efd\Handlers\HandlerC170;
use App\Services\Efd\Handlers\HandlerF600;
use App\Services\Efd\Handlers\SpedRegistroHandler;

/**
 * Driver EFD PIS/COFINS (L2). Prova a modularização: reusa L1 (parser/walker), L3 (engine),
 * L4 (job), L5 (harness) SEM mudança — só troca o conjunto de handlers.
 *
 * Compartilhados com o fiscal: `Handler0150` (só muda origem_tipo), `Handler0200`,
 * `HandlerC100`, `HandlerC170` (índices idênticos). Próprios do PIS/COFINS: A100/A170
 * (NFS-e), F600 (retenção), apuração bloco M + 0110. Sem C190/D190/D100 (a EFD
 * Contribuições não persiste consolidado ICMS nem CT-e como nota).
 */
class ContribDriver implements SpedDriver
{
    /** @var SpedRegistroHandler[] */
    private array $handlers;

    public function __construct()
    {
        $this->handlers = [
            new Handler0150('SPED_EFD_CONTRIB'),
            new Handler0200,
            new HandlerA100,
            new HandlerA170,
            new HandlerC100,
            new HandlerC170,
            new HandlerF600,
            new HandlerApuracaoM,
        ];
    }

    public function handlers(): array
    {
        return $this->handlers;
    }

    public function origemArquivo(): string
    {
        return 'contribuicoes';
    }

    public function tipoEfd(): string
    {
        return 'EFD PIS/COFINS';
    }

    /**
     * @return string[]
     */
    public function registros(): array
    {
        return array_merge(...array_map(fn (SpedRegistroHandler $h): array => $h->registros(), $this->handlers));
    }
}
