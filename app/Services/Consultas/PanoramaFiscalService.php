<?php

namespace App\Services\Consultas;

use App\Models\ParticipanteScore;
use App\Services\Consultas\Fiscal\TopMovimentacaoQuery;

/**
 * Compõe o "Panorama Fiscal" reusável de um CNPJ (escopo participante OU cliente):
 * KPIs + série mensal + mix CFOP + concentração de contrapartes + saúde fiscal.
 * Agnóstico de superfície — consumido pelo endpoint /app/panorama-fiscal.
 */
class PanoramaFiscalService
{
    /** @var array<string, string> escopo público => coluna física */
    private const ESCOPOS = ['participante' => 'participante_id', 'cliente' => 'cliente_id'];

    public function __construct(
        private ParticipanteFiscalResumoService $participantes,
        private ClienteFiscalResumoService $clientes,
        private TopMovimentacaoQuery $top,
    ) {}

    /** @return array<string, mixed>|null */
    public function para(int $userId, string $escopo, int $id): ?array
    {
        if (! isset(self::ESCOPOS[$escopo])) {
            throw new \InvalidArgumentException("Escopo de panorama inválido: {$escopo}");
        }
        $coluna = self::ESCOPOS[$escopo];

        $resumo = $escopo === 'participante'
            ? ($this->participantes->paraParticipantes($userId, [$id], comCfops: true, comProdutos: false)[$id] ?? null)
            : ($this->clientes->paraClientes($userId, [$id])[$id] ?? null);

        if ($resumo === null) {
            return null;
        }

        $serie = $this->top->serieMensal($userId, $coluna, [$id], (int) config('consultas.panorama_fiscal.meses', 24))[$id] ?? [];

        return [
            'escopo' => $escopo,
            'kpis' => [
                'total_comprado' => $resumo['total_comprado'],
                'total_vendido' => $resumo['total_vendido'],
                'qtd_entrada' => $resumo['qtd_entrada'],
                'qtd_saida' => $resumo['qtd_saida'],
                'primeira_nota' => $resumo['primeira_nota'],
                'ultima_nota' => $resumo['ultima_nota'],
                'papel' => $resumo['papel'],
            ],
            'serie_mensal' => $serie,
            'cfop_mix' => $this->comPct($resumo['top_cfops'] ?? [], fn ($c) => $c['valor'], fn ($c) => [
                'cfop' => $c['cfop'],
                'descricao' => $c['descricao'],
                'valor' => $c['valor'],
            ]),
            'concentracao' => $this->comPct(
                $resumo['relacionamentos'] ?? [],
                fn ($r) => ($r['valor_entrada'] ?? 0) + ($r['valor_saida'] ?? 0),
                fn ($r) => [
                    'nome' => $r['nome'] ?? '—',
                    'papel' => $r['papel'] ?? null,
                    'valor' => round(($r['valor_entrada'] ?? 0) + ($r['valor_saida'] ?? 0), 2),
                ],
            ),
            'saude' => $this->saude($userId, $coluna, $id, $resumo),
        ];
    }

    /**
     * Anexa pct (% do total por `valor`) a cada item, mapeando a forma de saída.
     *
     * @param  array<int, array<string, mixed>>  $itens
     * @return array<int, array<string, mixed>>
     */
    private function comPct(array $itens, callable $valorDe, callable $map): array
    {
        $total = array_sum(array_map($valorDe, $itens));

        return array_map(function ($item) use ($valorDe, $map, $total) {
            $linha = $map($item);
            $linha['pct'] = $total > 0 ? round($valorDe($item) / $total * 100, 1) : 0.0;

            return $linha;
        }, $itens);
    }

    /**
     * @param  array<string, mixed>  $resumo
     * @return array{score:?int, classificacao:?string, divergencias_catalogo:int}
     */
    private function saude(int $userId, string $coluna, int $id, array $resumo): array
    {
        $score = ParticipanteScore::where('user_id', $userId)
            ->where($coluna, $id)
            ->orderByDesc('ultima_consulta_em')
            ->first(['score_total', 'classificacao']);

        // Divergência barata: itens do CNPJ cujo catálogo 0200 não tem NCM.
        $semNcm = collect($this->top->produtos($userId, $coluna, [$id], 1000)[$id] ?? [])
            ->whereNull('ncm')->count();

        return [
            'score' => $score?->score_total !== null ? (int) $score->score_total : null,
            'classificacao' => $score?->classificacao,
            'divergencias_catalogo' => $semNcm,
        ];
    }
}
