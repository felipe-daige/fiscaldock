<?php

namespace App\Services\Consultas;

use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Services\Consultas\Persistencia\PersistenciaCnpj;
use App\Services\CreditService;
use Illuminate\Support\Facades\Cache;

/**
 * Settlement da reconsulta (retry). NÃO reusa FecharLoteService::fechar() porque aquele estorna o
 * valor CHEIO da fonte (custoCreditos), já devolvido no 1º fechamento. Aqui estorna apenas a
 * parcela do retry (preço com desconto, do envelope consulta_retry_charge) das fontes que
 * RE-falharam — a que teve sucesso mantém a receita do retry.
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
            $erros = $this->persistencia->normalizarFontesErro(($row->resultado_dados ?? [])['_fontes_erro'] ?? []);
            $envelope = (array) Cache::get("consulta_retry_charge:{$loteId}:{$tipo}:{$id}", []);

            foreach ($fontes as $chave) {
                // Re-falhou = ainda marcada em _fontes_erro (sucesso teria limpado via gravar()).
                if (isset($erros[$chave])) {
                    $estorno += (int) ($envelope[$chave] ?? 0);
                }
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

        $this->fecharLote->persistirScores($loteId);
    }
}
