<?php

namespace App\Services\Clearance;

use App\Jobs\ProcessarClearanceJob;
use App\Models\ConsultaLote;
use App\Models\EfdNota;
use App\Models\User;
use App\Models\XmlNota;
use App\Services\SaldoService;
use App\Services\ValidacaoContabilService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Orquestra o clearance SEFAZ em lote: resolve as chaves do acervo, debita saldo,
 * cria o ConsultaLote e dispara um ProcessarClearanceJob por documento via Bus::batch.
 * Substitui o despacho ao webhook n8n (desligado no cutover de 2026-06-07).
 */
class ClearanceLoteService
{
    public function __construct(private SaldoService $saldoService) {}

    /**
     * @param  array<int>  $notaIds
     * @param  array<int|string, string>  $origens  id => 'efd'|'xml'
     * @param  string  $tier  'basico' | 'full'
     * @return array<string, mixed> inclui 'http_status' quando 'success' === false
     */
    public function iniciar(array $notaIds, array $origens, string $tier, int $userId, ?string $tabId): array
    {
        $tier = in_array($tier, ['basico', 'full'], true) ? $tier : 'basico';

        // Clearance Full (tributos/itens) ainda não existe — exige certificado A1/A3. Enquanto a
        // flag está off, 'full' não entrega nada além do básico, então coage p/ basico (não cobra o
        // dobro). Regra: nunca confiar no frontend, mesmo com o card "em breve" desabilitado.
        if ($tier === 'full' && ! config('clearance.full.habilitado')) {
            $tier = 'basico';
        }

        $itens = $this->resolverItens($notaIds, $origens, $userId);

        if ($itens->isEmpty()) {
            return [
                'success' => false,
                'http_status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'error' => 'Nenhuma nota válida para clearance (NFS-e e chaves inválidas são ignoradas).',
            ];
        }

        $custoUnit = ValidacaoContabilService::custoUnitarioPorTier($tier);

        return $this->iniciarComItens(
            $itens,
            $custoUnit,
            $userId,
            $tabId,
            "Clearance em lote ({$tier}) · {$itens->count()} documento(s)",
            'app.clearance.notas.resultado',
            $tier === 'full',
        );
    }

    /**
     * Motor compartilhado (lote e busca avulsa): debita, cria o ConsultaLote, dispara o batch
     * e o fechamento. $itens = [{chave, tipo('nfe'|'cte'), cliente_id}].
     *
     * @param  Collection<int, array{chave: string, tipo: string, cliente_id: int|null}>  $itens
     * @return array<string, mixed>
     */
    public function iniciarComItens(
        Collection $itens,
        float $custoUnit,
        int $userId,
        ?string $tabId,
        string $descricaoDebito,
        string $resultadoRouteName = 'app.clearance.notas.resultado',
        bool $regularidade = false,
        string $fluxoOrigem = 'lote'
    ): array {
        if ($itens->isEmpty()) {
            return [
                'success' => false,
                'http_status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'error' => 'Nenhum documento válido para consultar.',
            ];
        }

        $user = User::findOrFail($userId);
        $custoTotal = $itens->count() * $custoUnit;
        $fluxoOrigem = $fluxoOrigem === 'avulsa' ? 'avulsa' : 'lote';

        if (! $this->saldoService->hasEnough($user, $custoTotal)) {
            return [
                'success' => false,
                'http_status' => Response::HTTP_PAYMENT_REQUIRED,
                'error' => 'Saldo insuficiente.',
                'custo_necessario' => $custoTotal,
                'saldo_atual' => $this->saldoService->getBalance($user),
            ];
        }

        $this->saldoService->deduct($user, $custoTotal, 'clearance_lote', $descricaoDebito);

        $lote = null;

        try {
            $lote = ConsultaLote::create([
                'user_id' => $userId,
                'cliente_id' => null,
                'plano_id' => null,
                'status' => ConsultaLote::STATUS_PROCESSANDO,
                'total_participantes' => $itens->count(),
                // Coluna canônica em R$ (lida por dashboards/relatórios/estorno).
                'creditos_cobrados' => $custoTotal,
                'tab_id' => $tabId,
                // Tier contratado — a tela de resultado usa isto pra saber se a regularidade da
                // contraparte foi paga/disparada (num lote básico o bloco nem aparece).
                'resultado_resumo' => [
                    'tier' => $regularidade ? 'full' : 'basico',
                    'fluxo_origem' => $fluxoOrigem,
                ],
            ]);

            // Lista de chaves do lote → FecharClearanceLoteService soma o estorno por doc.
            Cache::put("clearance_lote_chaves:{$lote->id}", $itens->pluck('chave')->all(), 86400);

            $total = $itens->count();
            $tier = $regularidade ? 'full' : 'basico';

            // Faixa da barra: no completo os documentos vão até 50% e a regularidade das
            // contrapartes ocupa 50→95 (o 100 vem do 'finalizado'). No básico, os documentos
            // usam a faixa toda.
            $spanDocs = $regularidade ? 50 : 95;
            $totalEtapas = ClearanceEtapas::total($tier);

            // Etapa 1 (Preparando consulta) publicada JÁ — antes de qualquer chamada externa, como
            // no contrato de etapas da Consulta CNPJ. Sem isto a tela abria sem etapa corrente e o
            // strip só ganhava vida quando o 1º documento começava.
            if ($tabId) {
                Cache::put("progresso:{$userId}:{$tabId}", [
                    'tab_id' => $tabId,
                    'etapa' => 1,
                    'total_etapas' => $totalEtapas,
                    'etapa_label' => ClearanceEtapas::para($tier)[0]['label'],
                    'status' => 'processando',
                    'progresso' => 0,
                    'mensagem' => "Preparando {$total} documento(s)…",
                ], 600);
            }

            $jobs = $itens->values()->map(fn (array $item, int $i) => new ProcessarClearanceJob(
                loteId: $lote->id,
                chave: $item['chave'],
                tipoDocumento: $item['tipo'],
                userId: $userId,
                tabId: (string) $tabId,
                clienteId: $item['cliente_id'],
                custoCreditos: $custoUnit,
                indice: $i + 1,
                total: $total,
                pctSpan: $spanDocs,
                totalEtapas: $totalEtapas,
            ))->all();

            $loteId = $lote->id;
            Bus::batch($jobs)
                ->name("clearance-lote-{$loteId}")
                ->then(function () use ($loteId, $regularidade, $userId, $tabId) {
                    // Clearance completo: investiga a regularidade das contrapartes ANTES de
                    // fechar. Fechar aqui emitiria 'finalizado' → o front recarregaria a tela de
                    // resultado com as contrapartes ainda "em apuração", e nada as atualizaria.
                    // Quem fecha o lote é o batch da regularidade (ver dispararConsulta).
                    if ($regularidade) {
                        $out = app(RegularidadeContraparteService::class)
                            ->investigarPorLoteClearance($loteId, $userId, $tabId, fecharClearanceLoteId: $loteId);

                        // Nada a consultar (sem contraparte cadastrável, ou todas já frescas):
                        // não há batch pra fechar o lote — fecha agora.
                        if (($out['lote_id'] ?? null) !== null) {
                            return;
                        }
                    }

                    app(FecharClearanceLoteService::class)->fechar($loteId);
                })
                ->dispatch();

            return [
                'success' => true,
                // Flag legada consumida pelo front: sinaliza "processamento assíncrono iniciado".
                'webhook_disparado' => true,
                'consulta_lote_id' => $lote->id,
                'tab_id' => $tabId,
                'total_notas' => $total,
                'valor_cobrado_reais' => round($custoTotal, 2),
                'valor_utilizado_reais' => round($custoTotal, 2),
                'novo_saldo_reais' => $this->saldoService->getBalance($user),
                'resultado_url' => route($resultadoRouteName, ['consultaLoteId' => $lote->id]),
            ];
        } catch (\Throwable $e) {
            if ($lote) {
                $lote->update([
                    'status' => ConsultaLote::STATUS_ERRO,
                    'error_code' => 'INTERNAL_ERROR',
                    'error_message' => $e->getMessage(),
                ]);
            }

            $this->saldoService->add($user, $custoTotal, 'clearance_refund', 'Estorno · falha ao iniciar clearance');

            Log::error('Clearance: exceção ao iniciar', ['user_id' => $userId, 'error' => $e->getMessage()]);

            return [
                'success' => false,
                'http_status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'error' => 'Erro ao iniciar o clearance. Saldo estornado.',
            ];
        }
    }

    /**
     * Resolve nota_ids (efd/xml) → itens [chave, tipo('nfe'|'cte'), cliente_id], escopo user_id.
     * Pula NFS-e e chaves != 44 dígitos; dedup por chave (primeiro vence).
     *
     * @return Collection<int, array{chave: string, tipo: string, cliente_id: int|null}>
     */
    private function resolverItens(array $notaIds, array $origens, int $userId): Collection
    {
        $xmlIds = [];
        $efdIds = [];
        foreach ($notaIds as $id) {
            $origem = $origens[$id] ?? $origens[(string) $id] ?? 'xml';
            if ($origem === 'efd') {
                $efdIds[] = (int) $id;
            } else {
                $xmlIds[] = (int) $id;
            }
        }

        $itens = collect();

        if ($xmlIds !== []) {
            XmlNota::whereIn('id', $xmlIds)
                ->where('user_id', $userId)
                ->get(['id', 'chave_acesso', 'tipo_documento', 'emit_cliente_id', 'dest_cliente_id'])
                ->each(function (XmlNota $nota) use ($itens) {
                    $tipo = $this->tipoConsulta(strtoupper((string) ($nota->tipo_documento ?: 'NFE')));
                    if ($tipo !== null) {
                        $itens->push([
                            'chave' => preg_replace('/\D/', '', (string) $nota->chave_acesso),
                            'tipo' => $tipo,
                            'cliente_id' => $nota->emit_cliente_id ?: $nota->dest_cliente_id,
                        ]);
                    }
                });
        }

        if ($efdIds !== []) {
            EfdNota::whereIn('id', $efdIds)
                ->where('user_id', $userId)
                ->get(['id', 'chave_acesso', 'modelo', 'cliente_id'])
                ->each(function (EfdNota $nota) use ($itens) {
                    $tipo = $this->tipoConsultaPorModelo(strtoupper((string) $nota->modelo));
                    if ($tipo !== null) {
                        $itens->push([
                            'chave' => preg_replace('/\D/', '', (string) $nota->chave_acesso),
                            'tipo' => $tipo,
                            'cliente_id' => $nota->cliente_id,
                        ]);
                    }
                });
        }

        return $itens
            ->filter(fn (array $item) => strlen($item['chave']) === 44)
            ->unique('chave')
            ->values();
    }

    /** tipo_documento textual (XML) → slug de consulta ('nfe'|'cte') ou null (não suportado). */
    private function tipoConsulta(string $tipoDocumento): ?string
    {
        return match ($tipoDocumento) {
            'NFE', 'NFCE' => 'nfe',
            'CTE' => 'cte',
            default => null, // NFSE e afins ficam fora
        };
    }

    /** modelo (EFD) → slug de consulta ('nfe'|'cte') ou null. */
    private function tipoConsultaPorModelo(string $modelo): ?string
    {
        return match ($modelo) {
            '55', '65' => 'nfe',
            '57' => 'cte',
            default => null,
        };
    }
}
