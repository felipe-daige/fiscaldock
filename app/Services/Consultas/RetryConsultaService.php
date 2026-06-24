<?php

namespace App\Services\Consultas;

use App\Jobs\ProcessarConsultaJob;
use App\Models\Cliente;
use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Models\Participante;
use App\Services\Consultas\Persistencia\PersistenciaCnpj;
use App\Services\CreditService;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

/**
 * Reconsulta de fontes com falha transitória (classe `retry`, ex. código 600 da InfoSimples).
 *
 * A fonte que falhou já foi estornada no fechamento do lote (ver ResultadoFonte::ehFalhaEstornavel),
 * então a reconsulta cobra apenas um percentual do custo da fonte (desconto), válido um número
 * limitado de vezes por fonte. Só `status='retry'` é elegível — `fatal`/`interno` não.
 */
class RetryConsultaService
{
    public function __construct(
        private FonteRegistry $registry,
        private PersistenciaCnpj $persistencia,
        private CreditService $credits,
    ) {}

    /**
     * Varre os resultados do lote e separa as fontes em falha entre elegíveis (retry, dentro do
     * limite de tentativas) e inelegíveis (fatal / interno / esgotado), com preço já calculado.
     *
     * @return array{
     *   elegiveis: array<int, array<string, mixed>>,
     *   inelegiveis: array<int, array<string, mixed>>,
     *   total_preco_creditos: int
     * }
     */
    public function pendentesRetry(ConsultaLote $lote): array
    {
        $maxPorFonte = (int) config('consultas.retry.max_por_fonte', 1);
        $elegiveis = [];
        $inelegiveis = [];

        $rows = ConsultaResultado::where('consulta_lote_id', $lote->id)
            ->with(['participante', 'cliente'])
            ->get();

        foreach ($rows as $row) {
            $dados = (array) $row->resultado_dados;
            $erros = $this->persistencia->normalizarFontesErro($dados['_fontes_erro'] ?? []);
            if (! $erros) {
                continue;
            }

            [$alvoTipo, $alvoId, $cnpj, $razao] = $this->alvoDe($row);

            foreach ($erros as $chave => $e) {
                $fonte = $this->registry->get($chave);
                $custo = $fonte?->custoCreditos() ?? 0;

                $base = [
                    'alvo_tipo' => $alvoTipo,
                    'alvo_id' => $alvoId,
                    'cnpj' => $cnpj,
                    'razao' => $razao,
                    'fonte' => $chave,
                    'titulo' => $this->titulo($chave),
                    'codigo' => $e['codigo'],
                    'custo_creditos' => $custo,
                ];

                $elegivel = $e['origem'] === 'integracao'
                    && $e['status'] === 'retry'
                    && (int) $e['tentativas'] < $maxPorFonte
                    && $fonte !== null;

                if ($elegivel) {
                    $base['preco_creditos'] = $this->precoPorFonte($custo);
                    $elegiveis[] = $base;
                } else {
                    $base['motivo'] = match (true) {
                        $e['status'] === 'fatal' => 'fatal',
                        $e['origem'] === 'interno' => 'interno',
                        (int) $e['tentativas'] >= $maxPorFonte => 'esgotado',
                        default => 'indisponivel',
                    };
                    $inelegiveis[] = $base;
                }
            }
        }

        return [
            'elegiveis' => $elegiveis,
            'inelegiveis' => $inelegiveis,
            'total_preco_creditos' => array_sum(array_column($elegiveis, 'preco_creditos')),
        ];
    }

    /**
     * @param  array<int, array{custo_creditos?: int}>  $elegiveis
     * @return array{creditos:int, reais:float}
     */
    public function precificar(array $elegiveis): array
    {
        $creditos = 0;
        foreach ($elegiveis as $e) {
            $creditos += $this->precoPorFonte((int) ($e['custo_creditos'] ?? 0));
        }

        return ['creditos' => $creditos, 'reais' => round($creditos * 0.20, 2)];
    }

    /**
     * Cobra o retry (50% off) das fontes selecionadas e re-despacha o job escopado a elas, no
     * MESMO lote. Valida elegibilidade no backend (nunca confia no frontend). O fechamento
     * (estorno parcial se re-falhar) é feito pelo FecharRetryService no `then` do batch.
     *
     * @param  array<int, array{alvo_tipo:string, alvo_id:int, fonte:string}>  $selecao
     * @return array{creditos:int}
     */
    public function executar(ConsultaLote $lote, array $selecao): array
    {
        abort_unless(
            ConsultaLote::normalizeStatus($lote->status) === ConsultaLote::STATUS_FINALIZADO,
            422,
            'Lote ainda em processamento.'
        );

        $pend = $this->pendentesRetry($lote);
        $indexElegivel = collect($pend['elegiveis'])
            ->keyBy(fn ($e) => $e['alvo_tipo'].':'.$e['alvo_id'].':'.$e['fonte']);

        $aplicar = [];
        $custoTotal = 0;
        foreach ($selecao as $s) {
            $k = $s['alvo_tipo'].':'.$s['alvo_id'].':'.$s['fonte'];
            $e = $indexElegivel->get($k);
            if (! $e) {
                throw ValidationException::withMessages([
                    'selecao' => "Fonte não elegível para reconsulta: {$s['fonte']}.",
                ]);
            }
            $aplicar[] = $e;
            $custoTotal += (int) $e['preco_creditos'];
        }
        abort_if(empty($aplicar), 422, 'Nenhuma fonte elegível selecionada.');

        abort_unless($this->credits->hasEnough($lote->user, $custoTotal), 402, 'Saldo insuficiente para a reconsulta.');
        $this->credits->deduct($lote->user, $custoTotal, 'consulta_retry', "Reconsulta lote #{$lote->id}", $lote);

        // Agrupa por alvo: incrementa tentativas (trava 1×) + grava envelope de cobrança + monta jobs.
        $porAlvo = [];
        foreach ($aplicar as $e) {
            $porAlvo[$e['alvo_tipo'].':'.$e['alvo_id']][] = $e;
        }

        $jobs = [];
        foreach ($porAlvo as $alvoKey => $itens) {
            [$tipo, $id] = explode(':', $alvoKey);
            $id = (int) $id;
            $envelope = [];
            $fontes = [];
            foreach ($itens as $e) {
                $this->persistencia->incrementarTentativaFonte($lote->id, $tipo, $id, $e['fonte']);
                $envelope[$e['fonte']] = (int) $e['preco_creditos'];
                $fontes[] = $e['fonte'];
            }
            Cache::put("consulta_retry_charge:{$lote->id}:{$tipo}:{$id}", $envelope, 86400);
            $jobs[] = $this->montarJob($lote, $tipo, $id, $fontes);
        }

        $alvosFontes = array_map(fn ($e) => [
            'alvo_tipo' => $e['alvo_tipo'], 'alvo_id' => $e['alvo_id'], 'fonte' => $e['fonte'],
        ], $aplicar);

        Bus::batch($jobs)
            ->name("consulta-retry-{$lote->id}")
            ->then(fn () => app(FecharRetryService::class)->fechar($lote->id, $alvosFontes))
            ->dispatch();

        return ['creditos' => $custoTotal];
    }

    private function montarJob(ConsultaLote $lote, string $tipo, int $id, array $fontes): ProcessarConsultaJob
    {
        return new ProcessarConsultaJob(
            loteId: $lote->id,
            alvoTipo: $tipo,
            alvoId: $id,
            userId: $lote->user_id,
            tabId: (string) $lote->tab_id,
            consultasIncluidas: $lote->plano->resolvedConsultasIncluidas(),
            alvo: $this->resolverAlvo($tipo, $id),
            etapas: $lote->plano->resolvedEtapas(),
            alvoIndice: 1,
            totalAlvos: 1,
            somenteFontes: $fontes,
        );
    }

    /**
     * @return array{cnpj:string, uf:?string, crt:mixed}
     */
    private function resolverAlvo(string $tipo, int $id): array
    {
        if ($tipo === 'cliente') {
            $c = Cliente::find($id);

            return ['cnpj' => preg_replace('/[^0-9]/', '', (string) $c?->documento), 'uf' => $c?->uf, 'crt' => null];
        }

        $p = Participante::find($id);

        return ['cnpj' => preg_replace('/[^0-9]/', '', (string) $p?->documento), 'uf' => $p?->uf, 'crt' => $p?->crt];
    }

    private function precoPorFonte(int $custo): int
    {
        $pct = (int) config('consultas.retry.desconto_pct', 50);

        return (int) ceil($custo * (100 - $pct) / 100);
    }

    private function titulo(string $chave): string
    {
        return (string) config("consultas.fonte_nome.{$chave}", $chave);
    }

    /**
     * @return array{0:string,1:int,2:string,3:string} [tipo, id, cnpj, razao]
     */
    private function alvoDe(ConsultaResultado $row): array
    {
        if ($row->cliente_id) {
            return ['cliente', $row->cliente_id, (string) ($row->cliente?->documento ?? ''), (string) ($row->cliente?->razao_social ?? '')];
        }

        return ['participante', $row->participante_id, (string) ($row->participante?->documento ?? ''), (string) ($row->participante?->razao_social ?? '')];
    }
}
