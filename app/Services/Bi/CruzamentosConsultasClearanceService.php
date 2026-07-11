<?php

namespace App\Services\Bi;

use App\Models\ParticipanteScore;
use App\Models\XmlNota;
use App\Services\BiService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * BI cross — cruza o resultado das consultas de CNPJ (regularidade por fornecedor)
 * com o acervo fiscal (volume de compras desse fornecedor nas notas).
 *
 * Fase 1 (2026-06-16): 2 cruzamentos acionáveis pro contador —
 *  1. Fornecedor com certidão/situação irregular × volume de compras dele.
 *  2. Nota cancelada na SEFAZ × situação do emitente consultado (esparso: depende de
 *     nfe_consultas, snapshot do clearance em lote).
 *
 * Fase 2 (2026-07-11): compras somam EFD + XML, filtro de período (data_emissao) e
 * drill-down por documento (notasDoFornecedor).
 *
 * Volume de compras (regra canônica de MOVIMENTO por participante):
 *  - EFD: entradas não canceladas com dedup P1 escopado ao participante
 *    (BiService::dedupParticipanteSql — MESMA regra da ficha/dossiê/Score Fiscal).
 *  - XML: entradas (exceto devolução) cujo emitente é o fornecedor; a chave que já
 *    existe em efd_notas NÃO entra (EFD vence, espelha NotaFiscalService::listarUnificadas).
 *
 * FONTE ÚNICA (2026-07-04): lê a regularidade de `participante_scores` — a projeção canônica
 * de `consulta_resultados` (gravada no fecho do lote via RiskScoreService::atualizarScore).
 * É a MESMA fonte do Score de Risco e do alerta `certidao_positiva`, então as telas não
 * divergem. Certidão irregular = subscore de certidão > 0 (classificação já feita pelo
 * CertidaoBadge dentro do RiskScoreService). Situação cadastral vem do `dados_consultados`
 * persistido junto ao score. Ver docs/alertas/README.md ("fonte única").
 */
class CruzamentosConsultasClearanceService
{
    private const SITUACOES_IRREGULARES = ['BAIXADA', 'INAPTA', 'SUSPENSA', 'NULA'];

    private const DRILL_LIMITE = 100;

    /**
     * @param  array{cliente_id?:int|null, data_inicio?:string|null, data_fim?:string|null}  $filtros
     * @return Collection<int, array{participante_id:int, razao_social:string, documento:string, motivos:array<int,string>, valor_comprado:float, qtd_notas:int}>
     */
    public function fornecedoresIrregularesComCompras(int $userId, array $filtros = []): Collection
    {
        $scores = $this->scoresPorParticipante($userId);

        $candidatos = $scores
            ->map(fn (ParticipanteScore $s) => [
                'participante_id' => $s->participante_id,
                'razao_social' => $s->participante?->razao_social ?? '—',
                'documento' => $s->participante?->documento ?? '—',
                'motivos' => $this->motivosIrregularidade($s),
            ])
            ->filter(fn (array $linha) => $linha['motivos'] !== []);

        return $this->anexarCompras($userId, $candidatos, $filtros);
    }

    /**
     * Notas canceladas na SEFAZ cujo emitente foi consultado. Esparso (depende de nfe_consultas).
     * Filtro de período usa a data de emissão do snapshot (linhas sem data ficam fora quando
     * o período é aplicado).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function notasCanceladasComEmitente(int $userId, array $filtros = []): Collection
    {
        $situacoesPorCnpj = $this->situacaoPorCnpj($userId);

        $canceladas = DB::table('nfe_consultas')
            ->where('user_id', $userId)
            ->whereRaw('UPPER(status) = ?', ['CANCELADA'])
            ->when(! empty($filtros['cliente_id']), fn ($q) => $q->where('cliente_id', $filtros['cliente_id']))
            ->when(! empty($filtros['data_inicio']), fn ($q) => $q->where('data_emissao', '>=', $filtros['data_inicio']))
            ->when(! empty($filtros['data_fim']), fn ($q) => $q->where('data_emissao', '<=', $filtros['data_fim']))
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
     * Drill-down: os documentos de compra (EFD + XML) de um fornecedor, com os mesmos
     * filtros da tela. Ordenado por emissão desc, limitado a DRILL_LIMITE.
     *
     * @return Collection<int, array{origem:string, numero:string|null, data_emissao:string|null, valor:float|null, chave_acesso:string|null}>
     */
    public function notasDoFornecedor(int $userId, int $participanteId, array $filtros = []): Collection
    {
        $efd = $this->queryComprasEfd($userId, [$participanteId], $filtros)
            ->get(['numero', 'data_emissao', 'valor_total', 'chave_acesso'])
            ->map(fn ($n) => [
                'origem' => 'EFD',
                'numero' => $n->numero !== null ? (string) $n->numero : null,
                'data_emissao' => $n->data_emissao ? substr((string) $n->data_emissao, 0, 10) : null,
                'valor' => $n->valor_total !== null ? (float) $n->valor_total : null,
                'chave_acesso' => $n->chave_acesso,
            ]);

        $documento = preg_replace('/\D/', '', (string) DB::table('participantes')
            ->where('user_id', $userId)->where('id', $participanteId)->value('documento'));

        $xml = $documento === ''
            ? collect()
            : $this->queryComprasXml($userId, [$documento], $filtros)
                ->get(['numero_documento', 'data_emissao', 'valor_total', 'chave_acesso'])
                ->map(fn ($n) => [
                    'origem' => 'XML',
                    'numero' => $n->numero_documento !== null ? (string) $n->numero_documento : null,
                    'data_emissao' => $n->data_emissao ? substr((string) $n->data_emissao, 0, 10) : null,
                    'valor' => $n->valor_total !== null ? (float) $n->valor_total : null,
                    'chave_acesso' => $n->chave_acesso,
                ]);

        return $efd->concat($xml)
            ->sortByDesc('data_emissao')
            ->take(self::DRILL_LIMITE)
            ->values();
    }

    /**
     * Diagnóstico do cruzamento — explica por que a tela pode estar vazia (não é bug, é cobertura
     * de dado): quantos CNPJs foram consultados, quantos fornecedores há nas notas de entrada
     * (EFD + XML, por documento) e quantos desses fornecedores já foram consultados (o overlap
     * que alimenta os cruzamentos).
     *
     * @return array{consultados_qtd:int, fornecedores_entrada_qtd:int, fornecedores_consultados_qtd:int}
     */
    public function diagnostico(int $userId): array
    {
        $docsConsultados = $this->scoresPorParticipante($userId)
            ->map(fn (ParticipanteScore $s) => preg_replace('/\D/', '', (string) ($s->participante?->documento ?? '')))
            ->filter()
            ->unique();

        $docsEfd = DB::table('efd_notas as n')
            ->join('participantes as p', 'p.id', '=', 'n.participante_id')
            ->where('n.user_id', $userId)
            ->where('n.tipo_operacao', 'entrada')
            ->where('n.cancelada', false)
            ->distinct()
            ->pluck('p.documento');

        $docsXml = XmlNota::where('user_id', $userId)
            ->where('tipo_nota', XmlNota::TIPO_ENTRADA)
            ->where('finalidade', '!=', XmlNota::FINALIDADE_DEVOLUCAO)
            ->whereNotNull('emit_documento')
            ->distinct()
            ->pluck('emit_documento');

        $docsFornecedores = $docsEfd->concat($docsXml)
            ->map(fn ($d) => preg_replace('/\D/', '', (string) $d))
            ->filter()
            ->unique();

        return [
            'consultados_qtd' => $docsConsultados->count(),
            'fornecedores_entrada_qtd' => $docsFornecedores->count(),
            'fornecedores_consultados_qtd' => $docsConsultados->intersect($docsFornecedores)->count(),
        ];
    }

    public function resumo(int $userId, array $filtros = []): array
    {
        $irregulares = $this->fornecedoresIrregularesComCompras($userId, $filtros);
        $canceladas = $this->notasCanceladasComEmitente($userId, $filtros);

        return [
            'irregulares_qtd' => $irregulares->count(),
            'irregulares_valor' => round((float) $irregulares->sum('valor_comprado'), 2),
            'canceladas_qtd' => $canceladas->count(),
        ];
    }

    /**
     * Score de regularidade (última consulta, projeção canônica) por participante consultado,
     * no escopo do usuário. Uma linha por participante — `participante_scores` é UNIQUE por alvo
     * e já guarda a versão mais recente mesclada.
     *
     * @return Collection<int, ParticipanteScore>
     */
    private function scoresPorParticipante(int $userId): Collection
    {
        return ParticipanteScore::where('user_id', $userId)
            ->whereNotNull('participante_id')
            ->with('participante')
            ->get();
    }

    /**
     * Motivos de irregularidade do fornecedor (certidões positivas + situação cadastral),
     * derivados do score canônico: subscore de certidão > 0 = irregular (classificação já feita
     * pelo CertidaoBadge dentro do RiskScoreService); situação vem do `dados_consultados`.
     *
     * Público: também é a classificação usada por CruzamentosEfdInternosService
     * (regularidade da fonte pagadora F600) — mesma leitura, telas não divergem.
     *
     * @return array<int, string>
     */
    public function motivosIrregularidade(ParticipanteScore $s): array
    {
        $motivos = [];

        $dados = is_array($s->dados_consultados) ? $s->dados_consultados : [];
        $situacao = strtoupper(trim((string) ($dados['situacao_cadastral'] ?? '')));
        if (in_array($situacao, self::SITUACOES_IRREGULARES, true)) {
            $motivos[] = "Situação cadastral: {$situacao}";
        }

        $certidoes = [
            'score_cnd_federal' => 'CND Federal positiva',
            'score_cnd_estadual' => 'CND Estadual positiva',
            'score_trabalhista' => 'CNDT positiva (débitos trabalhistas)',
        ];

        foreach ($certidoes as $coluna => $rotulo) {
            if ((int) ($s->{$coluna} ?? 0) > 0) {
                $motivos[] = $rotulo;
            }
        }

        return $motivos;
    }

    /**
     * Anexa volume de compras (EFD + XML) a cada candidato e descarta quem não comprou.
     */
    private function anexarCompras(int $userId, Collection $candidatos, array $filtros): Collection
    {
        $ids = $candidatos->pluck('participante_id')->filter()->unique()->values()->all();

        if ($ids === []) {
            return collect();
        }

        $comprasEfd = $this->comprasEfdPorParticipante($userId, $ids, $filtros);

        $docPorParticipante = $candidatos
            ->mapWithKeys(fn (array $l) => [$l['participante_id'] => preg_replace('/\D/', '', (string) $l['documento'])])
            ->filter();
        $comprasXml = $this->comprasXmlPorDocumento($userId, $docPorParticipante->values()->all(), $filtros);

        return $candidatos
            ->map(function (array $linha) use ($comprasEfd, $comprasXml, $docPorParticipante) {
                $efd = $comprasEfd[$linha['participante_id']] ?? null;
                $xml = $comprasXml[$docPorParticipante[$linha['participante_id']] ?? ''] ?? null;

                $linha['valor_comprado'] = round((float) ($efd->valor ?? 0) + (float) ($xml->valor ?? 0), 2);
                $linha['qtd_notas'] = (int) ($efd->qtd ?? 0) + (int) ($xml->qtd ?? 0);

                return $linha;
            })
            ->filter(fn (array $linha) => $linha['valor_comprado'] > 0 || $linha['qtd_notas'] > 0)
            ->sortByDesc('valor_comprado')
            ->values();
    }

    /**
     * Entradas EFD do(s) fornecedor(es): não canceladas, dedup P1 escopado ao participante
     * (regra canônica de movimento — BiService::dedupParticipanteSql).
     */
    private function queryComprasEfd(int $userId, array $participanteIds, array $filtros): \Illuminate\Database\Query\Builder
    {
        return DB::table('efd_notas')
            ->where('user_id', $userId)
            ->where('tipo_operacao', 'entrada')
            ->where('cancelada', false)
            ->whereIn('participante_id', $participanteIds)
            ->whereRaw(BiService::dedupParticipanteSql('efd_notas'))
            ->when(! empty($filtros['cliente_id']), fn ($q) => $q->where('cliente_id', $filtros['cliente_id']))
            ->when(! empty($filtros['data_inicio']), fn ($q) => $q->where('data_emissao', '>=', $filtros['data_inicio']))
            ->when(! empty($filtros['data_fim']), fn ($q) => $q->where('data_emissao', '<=', $filtros['data_fim']));
    }

    /**
     * Entradas XML emitidas pelo(s) documento(s): exceto devolução; chave que já existe em
     * efd_notas fica fora (EFD vence — espelha NotaFiscalService::listarUnificadas).
     */
    private function queryComprasXml(int $userId, array $documentos, array $filtros): \Illuminate\Database\Eloquent\Builder
    {
        return XmlNota::where('user_id', $userId)
            ->where('tipo_nota', XmlNota::TIPO_ENTRADA)
            ->where('finalidade', '!=', XmlNota::FINALIDADE_DEVOLUCAO)
            ->whereIn('emit_documento', $documentos)
            ->whereRaw(
                'NOT EXISTS (SELECT 1 FROM efd_notas en WHERE en.user_id = ? AND en.chave_acesso = xml_notas.chave_acesso)',
                [$userId]
            )
            ->when(! empty($filtros['cliente_id']), fn ($q) => $q->where('cliente_id', $filtros['cliente_id']))
            ->when(! empty($filtros['data_inicio']), fn ($q) => $q->where('data_emissao', '>=', $filtros['data_inicio']))
            ->when(! empty($filtros['data_fim']), fn ($q) => $q->where('data_emissao', '<=', $filtros['data_fim']));
    }

    /**
     * @param  array<int, int>  $participanteIds
     * @return array<int, object> keyed by participante_id
     */
    private function comprasEfdPorParticipante(int $userId, array $participanteIds, array $filtros): array
    {
        return $this->queryComprasEfd($userId, $participanteIds, $filtros)
            ->selectRaw('participante_id, COUNT(*) as qtd, SUM(valor_total) as valor')
            ->groupBy('participante_id')
            ->get()
            ->keyBy('participante_id')
            ->all();
    }

    /**
     * @param  array<int, string>  $documentos  só dígitos
     * @return array<string, object> keyed by emit_documento
     */
    private function comprasXmlPorDocumento(int $userId, array $documentos, array $filtros): array
    {
        if ($documentos === []) {
            return [];
        }

        return $this->queryComprasXml($userId, $documentos, $filtros)
            ->selectRaw('emit_documento, COUNT(*) as qtd, SUM(valor_total) as valor')
            ->groupBy('emit_documento')
            ->get()
            ->keyBy('emit_documento')
            ->all();
    }

    /**
     * Situação geral (label) por CNPJ do emitente, a partir do último resultado de consulta.
     *
     * @return array<string, string>
     */
    private function situacaoPorCnpj(int $userId): array
    {
        return $this->scoresPorParticipante($userId)
            ->mapWithKeys(function (ParticipanteScore $s) {
                $cnpj = preg_replace('/\D/', '', (string) ($s->participante?->documento ?? ''));
                if ($cnpj === '') {
                    return [];
                }
                $motivos = $this->motivosIrregularidade($s);

                return [$cnpj => $motivos === [] ? 'Regular' : implode(' · ', $motivos)];
            })
            ->all();
    }
}
