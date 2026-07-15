<?php

namespace App\Services\Consultas;

use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Services\Consultas\Persistencia\PersistenciaCnpj;
use App\Services\SaldoService;
use Illuminate\Support\Facades\Cache;

/**
 * Settlement da reconsulta (retry). A cobrança é por CNPJ (preço do plano com desconto), guardada
 * no envelope `consulta_retry_charge:{lote}:{tipo}:{id}` como escalar. Estorna o valor cheio do
 * CNPJ apenas quando NENHUMA fonte reconsultada daquele CNPJ foi ENTREGUE. Entregue = sucesso
 * (gravar() limpou a fonte de `_fontes_erro`) OU re-falha `erro_participante`: a fonte oficial
 * respondeu recusando os dados do CNPJ (ex. 620 FGTS) e a InfoSimples FATURA essa resposta
 * (`billable: true`) — provedor cobrou, a cobrança fica com o usuário (mesma regra do lote
 * original, onde `erro_participante` nunca foi estornável — ver ResultadoFonte::ehFalhaEstornavel).
 */
class FecharRetryService
{
    public function __construct(
        private SaldoService $saldoService,
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

        $estorno = 0.0;
        foreach ($porAlvo as $alvoKey => $fontes) {
            [$tipo, $id] = explode(':', $alvoKey);
            $id = (int) $id;
            $chaveEscopo = $tipo === 'cliente' ? 'cliente_id' : 'participante_id';

            $row = ConsultaResultado::where('consulta_lote_id', $loteId)->where($chaveEscopo, $id)->first();
            $erros = $this->persistencia->normalizarFontesErro(($row?->resultado_dados ?? [])['_fontes_erro'] ?? []);
            $cobrado = (float) Cache::get("consulta_retry_charge:{$loteId}:{$tipo}:{$id}", 0);

            // Estorna só quando NENHUMA fonte foi entregue: sucesso (fora de _fontes_erro) e
            // re-falha `erro_participante` contam como entrega — nesta classe a fonte oficial
            // respondeu (recusando os dados) e o provedor fatura a chamada, então o custo
            // repassa ao usuário em vez de virar estorno.
            $algumaEntregue = false;
            foreach ($fontes as $chave) {
                $erro = $erros[$chave] ?? null;
                if ($erro === null || ($erro['status'] ?? null) === 'erro_participante') {
                    $algumaEntregue = true;
                    break;
                }
            }
            if (! $algumaEntregue) {
                $estorno += $cobrado;
            }

            // Limpa o estorno cheio que o job do retry possa ter re-gravado p/ este alvo
            // (evita double-refund se um fechar() normal rodar depois) + o envelope já consumido.
            Cache::forget("consulta_estorno:{$loteId}:{$tipo}:{$id}");
            Cache::forget("consulta_retry_charge:{$loteId}:{$tipo}:{$id}");
        }

        if ($estorno > 0) {
            $this->saldoService->add(
                $lote->user,
                $estorno,
                type: 'consulta_retry_refund',
                description: 'Estorno de R$ '.number_format($estorno, 2, ',', '.')." — reconsulta sem sucesso no lote #{$lote->id}",
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
