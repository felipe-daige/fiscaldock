<?php

namespace App\Services\Consultas;

use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Services\Consultas\Persistencia\PersistenciaCnpj;
use App\Services\CreditService;
use Illuminate\Support\Facades\Cache;

/**
 * Settlement da reconsulta (retry). A cobrança é por CNPJ (preço do plano com desconto), guardada
 * no envelope `consulta_retry_charge:{lote}:{tipo}:{id}` como escalar. Estorna o valor cheio do
 * CNPJ apenas quando NENHUMA fonte reconsultada daquele CNPJ voltou com sucesso (todas re-falharam
 * = ainda marcadas em `_fontes_erro`). Se ≥1 fonte teve sucesso, mantém a receita do retry.
 */
class FecharRetryService
{
    public function __construct(
        private CreditService $creditService,
        private FecharLoteService $fecharLote,
        private PersistenciaCnpj $persistencia,
    ) {}

    /**
     * @param  array<int, array{alvo_tipo:string, alvo_id:int, fonte:string}>  $alvosFontes
     */
    public function fechar(int $loteId, array $alvosFontes): void
    {
        $lote = ConsultaLote::findOrFail($loteId);

        // Agrupa as fontes reconsultadas por alvo.
        $porAlvo = [];
        foreach ($alvosFontes as $af) {
            $porAlvo[$af['alvo_tipo'].':'.$af['alvo_id']][] = $af['fonte'];
        }

        $estorno = 0;
        foreach ($porAlvo as $alvoKey => $fontes) {
            [$tipo, $id] = explode(':', $alvoKey);
            $id = (int) $id;
            $chaveEscopo = $tipo === 'cliente' ? 'cliente_id' : 'participante_id';

            $row = ConsultaResultado::where('consulta_lote_id', $loteId)->where($chaveEscopo, $id)->first();
            $erros = $this->persistencia->normalizarFontesErro(($row?->resultado_dados ?? [])['_fontes_erro'] ?? []);
            $cobrado = (int) Cache::get("consulta_retry_charge:{$loteId}:{$tipo}:{$id}", 0);

            // Zero-sucesso = TODAS as fontes reconsultadas ainda em _fontes_erro (sucesso teria
            // limpado via gravar()). Só então estorna o valor cheio do CNPJ.
            $algumaSucesso = false;
            foreach ($fontes as $chave) {
                if (! isset($erros[$chave])) {
                    $algumaSucesso = true;
                    break;
                }
            }
            if (! $algumaSucesso) {
                $estorno += $cobrado;
            }

            // Limpa o estorno cheio que o job do retry possa ter re-gravado p/ este alvo
            // (evita double-refund se um fechar() normal rodar depois) + o envelope já consumido.
            Cache::forget("consulta_estorno:{$loteId}:{$tipo}:{$id}");
            Cache::forget("consulta_retry_charge:{$loteId}:{$tipo}:{$id}");
        }

        if ($estorno > 0) {
            $this->creditService->add(
                $lote->user,
                $estorno,
                type: 'consulta_retry_refund',
                description: "Estorno de {$estorno} crédito(s) — reconsulta sem sucesso no lote #{$lote->id}",
                source: $lote,
            );
        }

        // Encerra a tela de processamento: restaura o lote p/ terminal (executar virou p/
        // processando ao disparar a reconsulta). `concluido` = mesmo terminal do FecharLoteService
        // (e o valor real dos lotes finalizados). Roda no `finally` do batch → sucesso ou falha.
        $lote->status = ConsultaLote::STATUS_CONCLUIDO;
        $lote->save();

        $this->fecharLote->persistirScores($loteId);
    }
}
