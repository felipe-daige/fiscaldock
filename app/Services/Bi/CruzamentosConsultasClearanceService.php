<?php

namespace App\Services\Bi;

use App\Models\ConsultaResultado;
use App\Support\CertidaoBadge;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * BI cross — cruza o resultado das consultas de CNPJ (regularidade/sanção por fornecedor)
 * com o acervo fiscal (volume de compras desse fornecedor nas notas).
 *
 * Fase 1 (2026-06-16): 3 cruzamentos acionáveis pro contador —
 *  1. Fornecedor com certidão/situação irregular × volume de compras dele.
 *  2. Fornecedor sancionado (CEIS/CGU) × volume de compras dele.
 *  3. Nota cancelada na SEFAZ × situação do emitente consultado (esparso: depende de
 *     nfe_consultas, snapshot do clearance em lote).
 *
 * Volume de compras = entradas do EFD ICMS/IPI (`origem_arquivo = 'fiscal'`, evita a
 * dupla-contagem com a gêmea PIS/COFINS). XML = fase 2.
 *
 * Classificação de regularidade pelo classificador canônico CertidaoBadge.
 */
class CruzamentosConsultasClearanceService
{
    private const SITUACOES_IRREGULARES = ['BAIXADA', 'INAPTA', 'SUSPENSA', 'NULA'];

    /**
     * @return Collection<int, array{participante_id:int, razao_social:string, documento:string, motivos:array<int,string>, valor_comprado:float, qtd_notas:int}>
     */
    public function fornecedoresIrregularesComCompras(int $userId, array $filtros = []): Collection
    {
        $resultados = $this->resultadosMaisRecentesPorParticipante($userId);

        $candidatos = $resultados
            ->map(fn (ConsultaResultado $r) => [
                'participante_id' => $r->participante_id,
                'razao_social' => $r->participante?->razao_social ?? '—',
                'documento' => $r->participante?->documento ?? '—',
                'motivos' => $this->motivosIrregularidade($r),
            ])
            ->filter(fn (array $linha) => $linha['motivos'] !== []);

        return $this->anexarCompras($userId, $candidatos, $filtros);
    }

    /**
     * @return Collection<int, array{participante_id:int, razao_social:string, documento:string, bases:array<int,string>, valor_comprado:float, qtd_notas:int}>
     */
    public function fornecedoresSancionadosComCompras(int $userId, array $filtros = []): Collection
    {
        $resultados = $this->resultadosMaisRecentesPorParticipante($userId);

        $candidatos = $resultados
            ->map(function (ConsultaResultado $r) {
                $cgu = $r->getDado('cgu_cnc');
                $temSancao = is_array($cgu) && ($cgu['possui_sancao'] ?? false);

                return [
                    'participante_id' => $r->participante_id,
                    'razao_social' => $r->participante?->razao_social ?? '—',
                    'documento' => $r->participante?->documento ?? '—',
                    'bases' => $temSancao ? array_values(array_filter((array) ($cgu['bases_com_registro'] ?? []))) : [],
                    '_sancionado' => $temSancao,
                ];
            })
            ->filter(fn (array $linha) => $linha['_sancionado'] === true)
            ->map(function (array $linha) {
                unset($linha['_sancionado']);

                return $linha;
            });

        return $this->anexarCompras($userId, $candidatos, $filtros);
    }

    /**
     * Notas canceladas na SEFAZ cujo emitente foi consultado. Esparso (depende de nfe_consultas).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function notasCanceladasComEmitente(int $userId, array $filtros = []): Collection
    {
        $situacoesPorCnpj = $this->situacaoPorCnpj($userId);

        $canceladas = DB::table('nfe_consultas')
            ->where('user_id', $userId)
            ->whereRaw('UPPER(status) = ?', ['CANCELADA'])
            ->get(['chave_acesso', 'numero', 'emit_nome', 'emit_cnpj', 'valor_total']);

        return $canceladas->map(function ($n) use ($situacoesPorCnpj) {
            $cnpj = preg_replace('/\D/', '', (string) ($n->emit_cnpj ?? ''));

            return [
                'chave_acesso' => $n->chave_acesso,
                'numero' => $n->numero,
                'emit_nome' => $n->emit_nome ?? '—',
                'emit_cnpj' => $n->emit_cnpj ?? '—',
                'valor' => $n->valor_total !== null ? (float) $n->valor_total : null,
                'situacao_emitente' => $situacoesPorCnpj[$cnpj] ?? null,
            ];
        })->values();
    }

    /**
     * Diagnóstico do cruzamento — explica por que a tela pode estar vazia (não é bug, é cobertura
     * de dado): quantos CNPJs foram consultados, quantos fornecedores há nas notas de entrada e
     * quantos desses fornecedores já foram consultados (o overlap que alimenta os cruzamentos).
     *
     * @return array{consultados_qtd:int, fornecedores_entrada_qtd:int, fornecedores_consultados_qtd:int}
     */
    public function diagnostico(int $userId): array
    {
        $idsConsultados = $this->resultadosMaisRecentesPorParticipante($userId)
            ->pluck('participante_id')->filter()->unique();

        $fornecedoresEntrada = DB::table('efd_notas')
            ->where('user_id', $userId)
            ->where('origem_arquivo', 'fiscal')
            ->where('tipo_operacao', 'entrada')
            ->whereNotNull('participante_id')
            ->distinct()
            ->pluck('participante_id');

        return [
            'consultados_qtd' => $idsConsultados->count(),
            'fornecedores_entrada_qtd' => $fornecedoresEntrada->count(),
            'fornecedores_consultados_qtd' => $idsConsultados->intersect($fornecedoresEntrada)->count(),
        ];
    }

    public function resumo(int $userId, array $filtros = []): array
    {
        $irregulares = $this->fornecedoresIrregularesComCompras($userId, $filtros);
        $sancionados = $this->fornecedoresSancionadosComCompras($userId, $filtros);
        $canceladas = $this->notasCanceladasComEmitente($userId, $filtros);

        return [
            'irregulares_qtd' => $irregulares->count(),
            'irregulares_valor' => round((float) $irregulares->sum('valor_comprado'), 2),
            'sancionados_qtd' => $sancionados->count(),
            'sancionados_valor' => round((float) $sancionados->sum('valor_comprado'), 2),
            'canceladas_qtd' => $canceladas->count(),
        ];
    }

    /**
     * Último resultado de consulta (sucesso) por participante, no escopo do usuário.
     *
     * @return Collection<int, ConsultaResultado>
     */
    private function resultadosMaisRecentesPorParticipante(int $userId): Collection
    {
        return ConsultaResultado::query()
            ->select('consulta_resultados.*')
            ->join('consulta_lotes', 'consulta_lotes.id', '=', 'consulta_resultados.consulta_lote_id')
            ->where('consulta_lotes.user_id', $userId)
            ->where('consulta_resultados.status', ConsultaResultado::STATUS_SUCESSO)
            ->whereNotNull('consulta_resultados.participante_id')
            ->with('participante')
            ->orderBy('consulta_resultados.consultado_em', 'desc')
            ->orderBy('consulta_resultados.id', 'desc')
            ->get()
            ->groupBy('participante_id')
            ->map(fn (Collection $g) => $g->first())
            ->values();
    }

    /**
     * Motivos de irregularidade do fornecedor (certidões positivas + situação cadastral).
     *
     * @return array<int, string>
     */
    private function motivosIrregularidade(ConsultaResultado $r): array
    {
        $motivos = [];

        $situacao = strtoupper(trim((string) $r->getSituacaoCadastral()));
        if (in_array($situacao, self::SITUACOES_IRREGULARES, true)) {
            $motivos[] = "Situação cadastral: {$situacao}";
        }

        $certidoes = [
            'cnd_federal' => 'CND Federal positiva',
            'cnd_estadual' => 'CND Estadual positiva',
            'cndt' => 'CNDT positiva (débitos trabalhistas)',
        ];

        foreach ($certidoes as $chave => $rotulo) {
            $dado = $r->getDado($chave);
            if ($dado === null) {
                continue;
            }
            if (CertidaoBadge::classificar($dado, true)['label'] === 'Irregular') {
                $motivos[] = $rotulo;
            }
        }

        return $motivos;
    }

    /**
     * Anexa volume de compras (entradas fiscais) a cada candidato e descarta quem não comprou.
     */
    private function anexarCompras(int $userId, Collection $candidatos, array $filtros): Collection
    {
        $ids = $candidatos->pluck('participante_id')->filter()->unique()->values()->all();

        if ($ids === []) {
            return collect();
        }

        $compras = $this->comprasPorParticipante($userId, $ids, $filtros);

        return $candidatos
            ->map(function (array $linha) use ($compras) {
                $compra = $compras[$linha['participante_id']] ?? null;
                $linha['valor_comprado'] = $compra ? round((float) $compra->valor, 2) : 0.0;
                $linha['qtd_notas'] = $compra ? (int) $compra->qtd : 0;

                return $linha;
            })
            ->filter(fn (array $linha) => $linha['valor_comprado'] > 0 || $linha['qtd_notas'] > 0)
            ->sortByDesc('valor_comprado')
            ->values();
    }

    /**
     * @param  array<int, int>  $participanteIds
     * @return array<int, object> keyed by participante_id
     */
    private function comprasPorParticipante(int $userId, array $participanteIds, array $filtros): array
    {
        $query = DB::table('efd_notas')
            ->where('user_id', $userId)
            ->where('origem_arquivo', 'fiscal')
            ->where('tipo_operacao', 'entrada')
            ->whereIn('participante_id', $participanteIds);

        if (! empty($filtros['cliente_id'])) {
            $query->where('cliente_id', $filtros['cliente_id']);
        }

        return $query
            ->selectRaw('participante_id, COUNT(*) as qtd, SUM(valor_total) as valor')
            ->groupBy('participante_id')
            ->get()
            ->keyBy('participante_id')
            ->all();
    }

    /**
     * Situação geral (label) por CNPJ do emitente, a partir do último resultado de consulta.
     *
     * @return array<string, string>
     */
    private function situacaoPorCnpj(int $userId): array
    {
        return $this->resultadosMaisRecentesPorParticipante($userId)
            ->mapWithKeys(function (ConsultaResultado $r) {
                $cnpj = preg_replace('/\D/', '', (string) ($r->participante?->documento ?? ''));
                if ($cnpj === '') {
                    return [];
                }
                $motivos = $this->motivosIrregularidade($r);

                return [$cnpj => $motivos === [] ? 'Regular' : implode(' · ', $motivos)];
            })
            ->all();
    }
}
