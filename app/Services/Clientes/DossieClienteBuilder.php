<?php

namespace App\Services\Clientes;

use App\Models\Cliente;
use App\Models\ConsultaResultado;
use App\Models\ParticipanteScore;
use App\Services\Consultas\Fiscal\TopMovimentacaoQuery;
use App\Services\Consultas\ResultadoDetalhePresenter;
use App\Services\RiskScoreService;

/**
 * Monta o payload único do dossiê do cliente: consulta (certidões) + score + movimentações EFD.
 * Espelha DossieParticipanteBuilder, escopado por cliente_id. Sem efeitos colaterais.
 */
final class DossieClienteBuilder
{
    public function __construct(
        private ClienteMovimentacaoService $movimentacao,
        private ResultadoDetalhePresenter $presenter,
        private TopMovimentacaoQuery $top,
        private RiskScoreService $risk,
    ) {}

    public function montar(Cliente $c): array
    {
        $ultima = ConsultaResultado::where('cliente_id', $c->id)
            ->where('status', ConsultaResultado::STATUS_SUCESSO)
            ->with('lote.plano')
            ->orderByDesc('consultado_em')
            ->first();

        // Consolida com o histórico acumulado (participante_scores.dados_consultados): a
        // última consulta pode ser parcial (só cadastro / fonte com falha na integração) e
        // não pode esconder certidão válida de consulta anterior nem rebaixar o score a
        // 'inconclusivo' — mesma semântica do merge de RiskScoreService::persistirScore.
        // Mutação só em memória ($ultima não é salvo; o builder segue sem efeitos colaterais).
        $historico = ParticipanteScore::where('cliente_id', $c->id)->first()?->dados_consultados;
        if ($ultima && is_array($historico) && $historico !== []) {
            $ultima->resultado_dados = array_merge($historico, (array) $ultima->resultado_dados);
        }

        $consulta = [
            'tem' => (bool) $ultima,
            'resumo' => $ultima ? $this->presenter->resumoTextual($ultima) : null,
            // Passa as certidões que o PLANO desta consulta realmente incluiu → fonte pedida mas
            // sem retorno vira card de erro; fonte fora do plano não aparece como erro falso.
            'blocos' => $ultima ? $this->presenter->blocos($ultima, $this->presenter->esperadasDoResultado($ultima)) : [],
            'consultado_em' => $ultima?->consultado_em?->format('d/m/Y H:i'),
        ];

        $score = $ultima
            ? $ultima->calcularScore()
            : ['scores' => [], 'score_total' => 0, 'classificacao' => 'medio'];

        $score['detalhamento'] = $this->risk->detalhar($score['scores'] ?? []);

        return [
            'cliente' => $c,
            'gerado_em' => now()->format('d/m/Y H:i'),
            'consulta' => $consulta,
            'score' => $score,
            'movimentacao' => [
                'kpis' => $this->movimentacao->kpis($c),
                'por_competencia' => $this->movimentacao->porCompetencia($c),
                'por_cfop' => $this->movimentacao->porCfop($c),
                'por_cst' => $this->movimentacao->porCst($c),
                'impostos' => $this->movimentacao->impostos($c),
            ],
            'top_produtos' => $this->top->produtos($c->user_id, 'cliente_id', [$c->id], 10)[$c->id] ?? [],
            'top_cfops' => $this->top->cfops($c->user_id, 'cliente_id', [$c->id], 10)[$c->id] ?? [],
        ];
    }
}
