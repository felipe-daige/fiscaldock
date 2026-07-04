<?php

namespace App\Observers;

use App\Models\Alerta;
use App\Services\AlertaAuditoriaService;

/**
 * Grava a auditoria em toda transição de status do alerta — captura ações manuais
 * (via contexto de ator) e do sistema num único lugar. Bulk updates NÃO disparam
 * observers, então o auto-resolve do recalcular usa saves por-modelo (ver
 * AlertaCentralService::recalcular).
 */
class AlertaObserver
{
    public function created(Alerta $alerta): void
    {
        AlertaAuditoriaService::registrarTransicao($alerta, null, $alerta->status);
    }

    public function updated(Alerta $alerta): void
    {
        if ($alerta->wasChanged('status')) {
            AlertaAuditoriaService::registrarTransicao(
                $alerta,
                $alerta->getOriginal('status'),
                $alerta->status,
            );
        }
    }
}
