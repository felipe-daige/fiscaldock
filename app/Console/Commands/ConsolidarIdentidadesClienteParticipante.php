<?php

namespace App\Console\Commands;

use App\Services\Identidades\IdentidadeClienteParticipanteService;
use Illuminate\Console\Command;

class ConsolidarIdentidadesClienteParticipante extends Command
{
    protected $signature = 'identidades:consolidar-cliente-participante';

    protected $description = 'Instala a guarda e consolida documentos duplicados entre clientes e participantes';

    public function handle(IdentidadeClienteParticipanteService $identidades): int
    {
        $antes = $identidades->totalDuplicidades();
        $identidades->instalarEConsolidar();
        $depois = $identidades->totalDuplicidades();

        $this->info("Duplicidades antes: {$antes}; depois: {$depois}.");

        return $depois === 0 ? self::SUCCESS : self::FAILURE;
    }
}
