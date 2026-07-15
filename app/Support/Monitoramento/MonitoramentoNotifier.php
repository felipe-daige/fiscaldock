<?php

namespace App\Support\Monitoramento;

use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoConsulta;

/**
 * Canal de aviso do monitoramento contínuo. Hoje só in-app; mensageria
 * (e-mail/WhatsApp) futura implementa este mesmo contrato.
 */
interface MonitoramentoNotifier
{
    public function assinaturaPausadaSemSaldo(MonitoramentoAssinatura $assinatura): void;

    public function assinaturaPausadaPorFalhas(MonitoramentoAssinatura $assinatura): void;

    /** Freio §6.2 v2: N assinaturas adiadas neste ciclo (nada pausado; retomam no próximo ciclo). */
    public function freioAtuou(\App\Models\User $user, int $assinaturasAdiadas, \Illuminate\Support\Carbon $proximoCiclo): void;

    /** Consumo automático do ciclo atingiu >= 80% do cap. */
    public function consumoProximoDoLimite(\App\Models\User $user, float $consumoCreditos, float $capCreditos): void;

    public function situacaoPiorou(MonitoramentoConsulta $consulta, ?MonitoramentoConsulta $anterior): void;

    public function situacaoMelhorou(MonitoramentoConsulta $consulta, ?MonitoramentoConsulta $anterior): void;

    public function pendenciasSurgiram(MonitoramentoConsulta $consulta): void;

    public function certidaoVencendo(MonitoramentoConsulta $consulta): void;
}
