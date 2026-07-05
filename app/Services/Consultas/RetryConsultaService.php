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

/**
 * Reconsulta de fontes com falha transitória (classe `retry`, ex. código 600 da InfoSimples).
 *
 * A fonte que falhou já foi estornada no fechamento do lote (ver ResultadoFonte::ehFalhaEstornavel),
 * então a reconsulta cobra o preço do PLANO com desconto, por CNPJ afetado. Retry é ilimitado.
 * Elegíveis: `retry` e `erro_participante` — `fatal`/`interno` não.
 *
 * Settlement (FecharRetryService): re-falha total da classe `retry` = estorno → líquido zero
 * (o provedor não fatura instabilidade). Re-falha `erro_participante` MANTÉM a cobrança: a fonte
 * oficial respondeu recusando os dados e a InfoSimples fatura essa resposta (`billable: true`).
 */
class RetryConsultaService
{
    public function __construct(
        private FonteRegistry $registry,
        private PersistenciaCnpj $persistencia,
        private CreditService $credits,
    ) {}

    /**
     * Varre os resultados do lote e separa as fontes em falha entre elegíveis (retry) e
     * inelegíveis (fatal / interno), com o preço por CNPJ (plano × desconto) já calculado.
     *
     * @return array{
     *   elegiveis: array<int, array<string, mixed>>,
     *   inelegiveis: array<int, array<string, mixed>>,
     *   motivos: array<string, array<string, mixed>>,
     *   alvos: array<int, array<string, mixed>>,
     *   persistentes: array<int, array<string, mixed>>,
     *   suporte: ?array{contexto:string, mensagem:string},
     *   desconto_pct_efetivo: int,
     *   total_preco_creditos: int
     * }
     */
    public function pendentesRetry(ConsultaLote $lote): array
    {
        $elegiveis = [];
        $inelegiveis = [];
        $motivos = [];

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
                    'tentativas' => (int) $e['tentativas'],
                    'custo_creditos' => $custo,
                ];

                // Retry ILIMITADO: falhas `retry` (instabilidade) e `erro_participante` (fonte
                // recusou os dados do CNPJ, ex. 620) seguem elegíveis independente de quantas
                // vezes já foram reconsultadas — o botão coexiste com o "Comunicar com o suporte".
                // Só `fatal` (problema da NOSSA conta/infra no provedor) e `interno` ficam fora.
                $elegivel = $e['origem'] === 'integracao'
                    && in_array($e['status'], ['retry', 'erro_participante'], true)
                    && $fonte !== null;

                if ($elegivel) {
                    $base['preco_creditos'] = $this->precoPorFonte($custo);
                    $info = $this->motivoDe((int) $e['codigo']);
                    $base['motivo'] = $info['motivo'];
                    $motivos[$info['motivo']] ??= array_diff_key($info, ['motivo' => null]);
                    $elegiveis[] = $base;
                } else {
                    $base['motivo'] = match (true) {
                        $e['status'] === 'fatal' => 'fatal',
                        $e['origem'] === 'interno' => 'interno',
                        default => 'indisponivel',
                    };
                    $inelegiveis[] = $base;
                }
            }
        }

        // Cobrança da reconsulta = preço do PLANO com desconto, por CNPJ afetado (≥1 fonte
        // elegível). Não é soma por fonte. O backend reconsulta só as fontes com erro.
        $precoPlano = $this->precoPlanoRetry($lote);
        // Desconto efetivo exibido = preço real cobrado vs custo do plano. Pode diferir do
        // desconto nominal (config) porque `precoPlanoRetry` arredonda p/ inteiro (ledger integer).
        $custoPlano = (int) ($lote->plano?->custo_creditos ?? 0);
        $descontoEfetivoPct = $custoPlano > 0
            ? (int) round((1 - $precoPlano / $custoPlano) * 100)
            : (int) config('consultas.retry.desconto_pct', 50);
        $alvos = [];
        foreach ($elegiveis as $e) {
            $k = $e['alvo_tipo'].':'.$e['alvo_id'];
            $alvos[$k] ??= [
                'alvo_tipo' => $e['alvo_tipo'],
                'alvo_id' => $e['alvo_id'],
                'cnpj' => $e['cnpj'],
                'razao' => $e['razao'],
                'fontes' => [],
                'preco_creditos' => $precoPlano,
            ];
            $alvos[$k]['fontes'][] = $e['titulo'];
        }
        $alvos = array_values($alvos);

        // Suporte: fontes JÁ reconsultadas ≥1× e que ainda falham (tentativas≥1) → oferece
        // "Comunicar com o suporte" COEXISTINDO com o Reconsultar (que segue disponível, ilimitado).
        $persistentes = array_values(array_filter(
            array_merge($elegiveis, $inelegiveis),
            fn ($x) => ($x['tentativas'] ?? 0) >= 1,
        ));
        $suporte = null;
        if ($persistentes) {
            $fontes = array_values(array_unique(array_map(fn ($i) => $i['titulo'], $persistentes)));
            $codigos = array_values(array_unique(array_filter(array_map(fn ($i) => (string) $i['codigo'], $persistentes))));
            $cnpjsAfetados = count(array_unique(array_map(fn ($i) => $i['alvo_tipo'].':'.$i['alvo_id'], $persistentes)));
            $suporte = [
                'contexto' => \Illuminate\Support\Str::limit("Lote #{$lote->id} · ".implode(', ', $fontes).' · '.$cnpjsAfetados.' CNPJ(s)', 120, ''),
                'mensagem' => \Illuminate\Support\Str::limit('Fonte(s) sem resultado após a reconsulta (código InfoSimples '.implode('/', $codigos).'): '.implode(', ', $fontes).'. Os CNPJs continuam sem certidão. Podem verificar?', 500, ''),
            ];
        }

        return [
            'elegiveis' => $elegiveis,
            'inelegiveis' => $inelegiveis,
            'motivos' => $motivos,
            'alvos' => $alvos,
            'persistentes' => $persistentes,
            'suporte' => $suporte,
            'desconto_pct_efetivo' => $descontoEfetivoPct,
            'total_preco_creditos' => array_sum(array_column($alvos, 'preco_creditos')),
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
     * Cobra (plano × desconto) por CNPJ afetado e re-despacha a reconsulta SÓ das fontes que
     * falharam de cada CNPJ. Backend-autoritativo: não recebe seleção do front — recalcula os
     * CNPJs/fontes elegíveis. O settlement (estorno se o CNPJ não obtiver nenhum sucesso) é feito
     * pelo FecharRetryService no `finally` do batch.
     *
     * @return array{creditos:int}
     */
    public function executar(ConsultaLote $lote): array
    {
        abort_unless(
            ConsultaLote::normalizeStatus($lote->status) === ConsultaLote::STATUS_FINALIZADO,
            422,
            'Lote ainda em processamento.'
        );

        $pend = $this->pendentesRetry($lote);
        abort_if(empty($pend['alvos']), 422, 'Nenhum CNPJ elegível para reconsulta.');

        $custoTotal = (int) $pend['total_preco_creditos'];
        abort_unless($this->credits->hasEnough($lote->user, $custoTotal), 402, 'Saldo insuficiente para a reconsulta.');
        $this->credits->deduct($lote->user, $custoTotal, 'consulta_retry', "Reconsulta lote #{$lote->id}", $lote);

        // Tela de processamento: vira o lote p/ `processando` com tab_id novo (strip limpo). A view
        // já renderiza o card de progresso + SSE a partir do status; os jobs escrevem em
        // progresso:{user}:{tab}. SÓ após validações + deduct (senão o lote ficaria preso sem job).
        // Tudo daqui até o dispatch num try: se o batch não for despachado (falha de fila/DB), o
        // `finally` nunca registra → reverte o flip e estorna o débito p/ o lote não ficar preso.
        try {
            $lote->tab_id = (string) \Illuminate\Support\Str::uuid();
            $lote->status = ConsultaLote::STATUS_PROCESSANDO;
            $lote->save();

            // Fontes elegíveis (chaves) por alvo.
            $fontesPorAlvo = [];
            foreach ($pend['elegiveis'] as $e) {
                $fontesPorAlvo[$e['alvo_tipo'].':'.$e['alvo_id']][] = $e['fonte'];
            }

            $jobs = [];
            $alvosFontes = [];
            $totalAlvos = count($pend['alvos']);
            foreach (array_values($pend['alvos']) as $i => $alvo) {
                $tipo = $alvo['alvo_tipo'];
                $id = (int) $alvo['alvo_id'];
                $fontes = $fontesPorAlvo[$tipo.':'.$id] ?? [];
                foreach ($fontes as $f) {
                    $this->persistencia->incrementarTentativaFonte($lote->id, $tipo, $id, $f);
                    $alvosFontes[] = ['alvo_tipo' => $tipo, 'alvo_id' => $id, 'fonte' => $f];
                }
                // Envelope de cobrança per-alvo = preço do plano (escalar), p/ estorno integral se zero-sucesso.
                Cache::put("consulta_retry_charge:{$lote->id}:{$tipo}:{$id}", (int) $alvo['preco_creditos'], 86400);
                // Índice/total REAIS: com 1/1 hardcodado, o 2º CNPJ repostava 0% depois do 100%
                // do 1º (a barra da UI não tem clamp anti-retrocesso).
                $jobs[] = $this->montarJob($lote, $tipo, $id, $fontes, $i + 1, $totalAlvos);
            }

            // `finally` (não `then`): roda no sucesso E na falha → o lote nunca fica preso em
            // `processando` e o settlement (estorno se zero-sucesso) sempre acontece.
            Bus::batch($jobs)
                ->name("consulta-retry-{$lote->id}")
                ->finally(fn () => app(FecharRetryService::class)->fechar($lote->id, $alvosFontes))
                ->dispatch();
        } catch (\Throwable $e) {
            // Nenhum job foi enfileirado → estorna o débito e restaura o terminal (não fica preso).
            $this->credits->add(
                $lote->user,
                $custoTotal,
                type: 'consulta_retry_refund',
                description: "Estorno — falha ao despachar reconsulta do lote #{$lote->id}",
                source: $lote,
            );
            $lote->status = ConsultaLote::STATUS_CONCLUIDO;
            $lote->save();

            throw $e;
        }

        return ['creditos' => $custoTotal];
    }

    private function montarJob(ConsultaLote $lote, string $tipo, int $id, array $fontes, int $alvoIndice = 1, int $totalAlvos = 1): ProcessarConsultaJob
    {
        return new ProcessarConsultaJob(
            loteId: $lote->id,
            alvoTipo: $tipo,
            alvoId: $id,
            userId: $lote->user_id,
            tabId: (string) $lote->tab_id,
            consultasIncluidas: $lote->plano->resolvedConsultasIncluidas(),
            alvo: $this->resolverAlvo($lote->id, $tipo, $id),
            etapas: $lote->plano->resolvedEtapas(),
            alvoIndice: $alvoIndice,
            totalAlvos: $totalAlvos,
            somenteFontes: $fontes,
        );
    }

    /**
     * @return array{cnpj:string, uf:?string, crt:mixed, matriz_filial:?string}
     */
    private function resolverAlvo(int $loteId, string $tipo, int $id): array
    {
        // Retry não roda o cadastro de novo (só as fontes que falharam), então o indicador
        // oficial (RFB) de matriz/filial vem do resultado já persistido nesse lote — sem ele,
        // CndFederalFonte::params() cairia de volta na ORDEM do CNPJ (heurística que já
        // mandou consulta pro CNPJ errado num caso real: ver lote #220).
        $matrizFilial = ConsultaResultado::where('consulta_lote_id', $loteId)
            ->where($tipo === 'cliente' ? 'cliente_id' : 'participante_id', $id)
            ->value('resultado_dados');
        $matrizFilial = is_array($matrizFilial) ? ($matrizFilial['matriz_filial'] ?? null) : null;

        if ($tipo === 'cliente') {
            $c = Cliente::find($id);

            return ['cnpj' => preg_replace('/[^0-9]/', '', (string) $c?->documento), 'uf' => $c?->uf, 'crt' => null, 'matriz_filial' => $matrizFilial];
        }

        $p = Participante::find($id);

        return ['cnpj' => preg_replace('/[^0-9]/', '', (string) $p?->documento), 'uf' => $p?->uf, 'crt' => $p?->crt, 'matriz_filial' => $matrizFilial];
    }

    /**
     * Resolve o código InfoSimples (classe retry) para o motivo acionável + apresentação.
     * Código não mapeado cai no fallback `tecnica_pontual` (nunca fica sem orientação).
     *
     * @return array{motivo:string,rotulo:string,aguardar_minutos:int,icone:string,orientacao:string}
     */
    public function motivoDe(int $codigo): array
    {
        $motivo = (string) config("consultas.retry.codigo_motivo.{$codigo}", 'tecnica_pontual');
        $apresentacao = (array) config("consultas.retry.motivos.{$motivo}", []);

        return ['motivo' => $motivo] + $apresentacao;
    }

    /**
     * Preço da reconsulta por CNPJ = custo do plano consultado com o desconto de retry aplicado.
     * Inteiro (ledger `credit_transactions.amount` é integer); arredonda p/ cima (nunca subcobra).
     */
    public function precoPlanoRetry(ConsultaLote $lote): int
    {
        $pct = (int) config('consultas.retry.desconto_pct', 50);
        $custoPlano = (float) ($lote->plano?->custo_creditos ?? 0);

        return (int) ceil($custoPlano * (100 - $pct) / 100);
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
