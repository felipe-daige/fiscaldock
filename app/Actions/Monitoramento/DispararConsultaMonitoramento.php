<?php

namespace App\Actions\Monitoramento;

use App\Jobs\ProcessarConsultaJob;
use App\Models\ConsultaLote;
use App\Models\MonitoramentoAssinatura;
use App\Models\MonitoramentoConsulta;
use App\Services\Advocacia\CatalogoFontesAvulsas;
use App\Services\Consultas\FecharLoteService;
use App\Services\SaldoService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;

/**
 * Dispara um ciclo de monitoramento contínuo reusando a pipeline de consulta:
 * cria a MonitoramentoConsulta + um ConsultaLote de 1 alvo + o batch
 * ProcessarConsultaJob, com FecharLoteService + FinalizarCicloMonitoramento no
 * callback. Débito/estorno são da pipeline (FecharLoteService).
 */
class DispararConsultaMonitoramento
{
    public function __construct(
        private SaldoService $saldoService,
        private CatalogoFontesAvulsas $catalogo,
    ) {}

    public function execute(MonitoramentoAssinatura $assinatura, ?MonitoramentoConsulta $parent = null): MonitoramentoConsulta
    {
        $plano = $assinatura->plano;
        $user = $assinatura->user;
        $alaCarte = $assinatura->usaAlaCarte();

        // Grupo é DINÂMICO: membros avaliados agora; custo do ciclo = N × plano (custoCiclo).
        $ehGrupo = $assinatura->alvoTipo() === 'grupo';
        $membros = $ehGrupo ? $assinatura->membrosDoGrupo() : collect();
        $custo = $assinatura->custoCiclo();

        $consulta = MonitoramentoConsulta::create([
            'user_id' => $assinatura->user_id,
            'participante_id' => $assinatura->participante_id,
            'cliente_id' => $assinatura->cliente_id,
            'grupo_id' => $assinatura->grupo_id,
            'plano_id' => $assinatura->plano_id,
            'assinatura_id' => $assinatura->id,
            'parent_consulta_id' => $parent?->id,
            'tipo' => 'assinatura',
            'status' => 'pendente',
            'creditos_cobrados' => $custo,
        ]);

        // Grupo sem membros: ciclo em vazio — não cobra, não cria lote; a próxima execução
        // segue agendada pelo chamador (agendarProximaExecucao no comando).
        if ($ehGrupo && $membros->isEmpty()) {
            $consulta->update(['status' => 'sucesso', 'creditos_cobrados' => 0, 'executado_em' => now()]);

            return $consulta;
        }

        if ($custo > 0 && ! $this->saldoService->deduct($user, $custo, 'monitoramento_assinatura', "Monitoramento contínuo — assinatura #{$assinatura->id}", $consulta)) {
            $consulta->marcarErro('saldo_insuficiente', 'Saldo insuficiente no disparo do ciclo.');

            return $consulta;
        }

        // Alvos do lote: membros do grupo, ou o alvo único — mesmo shape do executar da consulta.
        $alvos = $ehGrupo
            ? $membros->map(fn ($p) => [
                'tipo' => 'participante', 'id' => $p->id,
                'cnpj' => preg_replace('/[^0-9]/', '', (string) $p->documento),
                'uf' => $p->uf ?? null, 'crt' => $p->crt ?? null,
            ])->values()
            : collect([[
                'tipo' => $assinatura->alvoTipo(),
                'id' => $assinatura->alvo()->id,
                'cnpj' => preg_replace('/[^0-9]/', '', (string) $assinatura->alvo()->documento),
                'uf' => $assinatura->alvo()->uf ?? null,
                'crt' => $assinatura->participante_id ? ($assinatura->alvo()->crt ?? null) : null,
            ]]);

        // Derivação por modo. À la carte: fontes/etapas/preço vêm do catálogo (fonte única, com
        // kit/preset do dono); estorno de falha devolve o unitário via precosVenda. Legado: plano.
        $fontesSelecionadas = $alaCarte ? $assinatura->fontesSelecionadas() : [];
        $consultasIncluidas = $alaCarte ? $this->catalogo->atributosDe($fontesSelecionadas) : $plano->consultas_incluidas;
        $etapas = $alaCarte ? $this->catalogo->etapasDe($fontesSelecionadas) : $plano->resolvedEtapas();
        $precosVenda = $alaCarte ? $this->catalogo->precificar($fontesSelecionadas, (int) $user->id)['precos'] : null;

        $tabId = (string) Str::uuid();
        $lote = ConsultaLote::create([
            'user_id' => $user->id,
            'cliente_id' => $assinatura->cliente_id,
            'plano_id' => $alaCarte ? null : $plano->id,
            'fontes_selecionadas' => $alaCarte ? $fontesSelecionadas : null,
            'status' => ConsultaLote::STATUS_PROCESSANDO,
            'total_participantes' => $alvos->count(),
            'creditos_cobrados' => $custo,
            'tab_id' => $tabId,
        ]);
        $participanteIds = $alvos->where('tipo', 'participante')->pluck('id')->all();
        if ($participanteIds) {
            $lote->participantes()->attach($participanteIds);
        }
        $consulta->update(['consulta_lote_id' => $lote->id]);

        $totalAlvos = $alvos->count();
        $jobs = $alvos->values()->map(fn ($alvo, $i) => new ProcessarConsultaJob(
            loteId: $lote->id,
            alvoTipo: $alvo['tipo'],
            alvoId: $alvo['id'],
            userId: $user->id,
            tabId: $tabId,
            consultasIncluidas: $consultasIncluidas,
            alvo: ['cnpj' => $alvo['cnpj'], 'uf' => $alvo['uf'], 'crt' => $alvo['crt']],
            etapas: $etapas,
            alvoIndice: $i + 1,
            totalAlvos: $totalAlvos,
            precosVenda: $precosVenda,
        ))->all();

        $consultaId = $consulta->id;
        Bus::batch($jobs)
            ->name("monitoramento-lote-{$lote->id}")
            ->then(function () use ($lote, $consultaId) {
                app(FecharLoteService::class)->fechar($lote->id, resumo: ['engine' => 'laravel', 'origem' => 'monitoramento']);
                app(FinalizarCicloMonitoramento::class)->execute($consultaId);
            })
            ->dispatch();

        return $consulta;
    }
}
