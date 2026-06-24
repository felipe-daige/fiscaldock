<?php

namespace App\Services\Consultas;

use App\Models\ConsultaLote;
use App\Models\ConsultaResultado;
use App\Services\Consultas\Persistencia\PersistenciaCnpj;

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
