<?php

namespace App\Support\Monitoramento;

use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoConsulta;
use App\Models\User;
use App\Services\AlertaCentralService;
use App\Services\PricingCatalogService;
use App\Support\Dinheiro;
use Illuminate\Support\Carbon;

class AlertaCentralNotifier implements MonitoramentoNotifier
{
    public function __construct(
        private AlertaCentralService $alertas,
        private PricingCatalogService $precos,
    ) {}

    public function assinaturaPausadaSemSaldo(MonitoramentoAssinatura $assinatura): void
    {
        $nome = $this->nomeAlvo($assinatura);

        $this->alertas->registrarAlertaMonitoramento([
            'user_id' => $assinatura->user_id,
            'tipo' => 'monitoramento_pausado_saldo',
            'severidade' => 'alta',
            'titulo' => 'Monitoramento pausado por saldo insuficiente',
            'descricao' => "O monitoramento contínuo de {$nome} foi pausado porque não havia saldo suficiente para o ciclo. Adicione saldo e reative a assinatura.",
            'participante_id' => $assinatura->participante_id,
            'cliente_id' => $assinatura->cliente_id,
        ]);
    }

    public function assinaturaPausadaPorFalhas(MonitoramentoAssinatura $assinatura): void
    {
        $nome = $this->nomeAlvo($assinatura);

        $this->alertas->registrarAlertaMonitoramento([
            'user_id' => $assinatura->user_id,
            'tipo' => 'monitoramento_pausado_falhas',
            'severidade' => 'alta',
            'titulo' => 'Monitoramento pausado após falhas seguidas',
            'descricao' => "O monitoramento contínuo de {$nome} foi pausado após 3 tentativas seguidas de consulta sem sucesso. Reative a assinatura para tentar novamente.",
            'participante_id' => $assinatura->participante_id,
            'cliente_id' => $assinatura->cliente_id,
        ]);
    }

    public function freioAtuou(User $user, int $assinaturasAdiadas, Carbon $proximoCiclo): void
    {
        $this->alertas->registrarAlertaMonitoramento([
            'user_id' => $user->id,
            'tipo' => 'monitoramento_freio_atuou',
            'severidade' => 'media',
            'titulo' => 'Freio de consumo atuou no monitoramento automático',
            'descricao' => "{$assinaturasAdiadas} assinatura(s) de monitoramento aguardando o próximo ciclo (a partir de {$proximoCiclo->format('d/m/Y')}): o consumo automático atingiu o limite definido. Nada foi pausado — as consultas retomam sozinhas. Aumente o limite no painel de monitoramento se quiser antecipar.",
        ]);
    }

    public function consumoProximoDoLimite(User $user, int $consumoCreditos, int $capCreditos): void
    {
        $consumoBrl = Dinheiro::brl($this->precos->creditsToCurrency($consumoCreditos));
        $capBrl = Dinheiro::brl($this->precos->creditsToCurrency($capCreditos));

        $this->alertas->registrarAlertaMonitoramento([
            'user_id' => $user->id,
            'tipo' => 'monitoramento_consumo_80',
            'severidade' => 'media',
            'titulo' => 'Monitoramento automático chegou a 80% do limite do ciclo',
            'descricao' => "O monitoramento automático já consumiu {$consumoBrl} de {$capBrl} neste ciclo. Ao atingir o limite, as próximas consultas aguardam o próximo ciclo.",
        ]);
    }

    public function situacaoPiorou(MonitoramentoConsulta $consulta, ?MonitoramentoConsulta $anterior): void
    {
        $de = $anterior?->situacao_geral ?? 'desconhecida';

        $this->alertas->registrarAlertaMonitoramento([
            'user_id' => $consulta->user_id,
            'tipo' => 'monitoramento_situacao_piorou',
            'severidade' => 'alta',
            'titulo' => 'Situação do CNPJ piorou',
            'descricao' => "A situação geral mudou de {$de} para {$consulta->situacao_geral} na última consulta do monitoramento contínuo.",
            'participante_id' => $consulta->participante_id,
            'cliente_id' => $consulta->cliente_id,
            'monitoramento_consulta_id' => $consulta->id,
        ]);
    }

    public function situacaoMelhorou(MonitoramentoConsulta $consulta, ?MonitoramentoConsulta $anterior): void
    {
        $de = $anterior?->situacao_geral ?? 'desconhecida';

        $this->alertas->registrarAlertaMonitoramento([
            'user_id' => $consulta->user_id,
            'tipo' => 'monitoramento_situacao_melhorou',
            'severidade' => 'baixa',
            'titulo' => 'Situação do CNPJ melhorou',
            'descricao' => "A situação geral mudou de {$de} para {$consulta->situacao_geral} na última consulta do monitoramento contínuo.",
            'participante_id' => $consulta->participante_id,
            'cliente_id' => $consulta->cliente_id,
            'monitoramento_consulta_id' => $consulta->id,
        ]);
    }

    public function pendenciasSurgiram(MonitoramentoConsulta $consulta): void
    {
        $this->alertas->registrarAlertaMonitoramento([
            'user_id' => $consulta->user_id,
            'tipo' => 'monitoramento_pendencias_surgiram',
            'severidade' => 'media',
            'titulo' => 'Surgiram pendências no CNPJ',
            'descricao' => 'A última consulta do monitoramento contínuo detectou pendências que não existiam na consulta anterior.',
            'participante_id' => $consulta->participante_id,
            'cliente_id' => $consulta->cliente_id,
            'monitoramento_consulta_id' => $consulta->id,
        ]);
    }

    public function certidaoVencendo(MonitoramentoConsulta $consulta): void
    {
        $validade = optional($consulta->proxima_validade)->format('d/m/Y');

        $this->alertas->registrarAlertaMonitoramento([
            'user_id' => $consulta->user_id,
            'tipo' => 'monitoramento_certidao_vencendo',
            'severidade' => 'media',
            'titulo' => 'Certidão vencendo',
            'descricao' => "Uma certidão do CNPJ monitorado vence em {$validade} (dentro de 30 dias).",
            'participante_id' => $consulta->participante_id,
            'cliente_id' => $consulta->cliente_id,
            'monitoramento_consulta_id' => $consulta->id,
        ]);
    }

    private function nomeAlvo(MonitoramentoAssinatura $assinatura): string
    {
        $alvo = $assinatura->alvo();

        return $alvo?->razao_social ?? 'CNPJ monitorado';
    }
}
