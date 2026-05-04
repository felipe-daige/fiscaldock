<?php

namespace App\Services\Bi;

use App\Models\ConsultaResultado;
use App\Models\Participante;
use App\Services\ParecerFiscalService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class BiConsultasEfdService
{
    private const DIAS_CONSULTA_ANTIGA = 90;

    private const CONCENTRACAO_THRESHOLD_PERCENT = 30.0;

    public function __construct(
        private readonly ParecerFiscalService $parecerFiscalService,
    ) {}

    /**
     * @param  array{data_inicio?: ?string, data_fim?: ?string, cliente_id?: mixed}  $filtros
     * @return array{
     *   kpis: array<string, int|float>,
     *   participantes: array<int, array<string, mixed>>,
     *   regimes: array<int, array<string, mixed>>,
     *   situacoes: array<int, array<string, mixed>>
     * }
     */
    public function painel(int $userId, array $filtros = []): array
    {
        $participantes = $this->participantesConsultadosComMovimentacao($userId, $filtros);
        $totalConsultados = $this->totalParticipantesConsultados($userId);

        return [
            'kpis' => [
                'participantes_consultados' => $totalConsultados,
                'participantes_com_movimentacao' => $participantes->count(),
                'participantes_sem_movimentacao' => max(0, $totalConsultados - $participantes->count()),
                'valor_total_efd' => (float) $participantes->sum('valor_total_efd'),
                'total_notas_efd' => (int) $participantes->sum('total_notas_efd'),
            ],
            'participantes' => $participantes->all(),
            'regimes' => $this->agruparPorRegime($participantes)->all(),
            'situacoes' => $this->agruparPorSituacao($participantes)->all(),
        ];
    }

    /**
     * @param  array{data_inicio?: ?string, data_fim?: ?string, cliente_id?: mixed}  $filtros
     * @return Collection<int, array<string, mixed>>
     */
    public function participantesConsultadosComMovimentacao(int $userId, array $filtros = []): Collection
    {
        $rows = $this->baseQuery($userId, $filtros)->get();

        $mapeados = $rows->map(fn ($row) => $this->mapParticipanteRow($row));
        $valorGeral = (float) $mapeados->sum('valor_total_efd');

        return $mapeados
            ->map(fn (array $linha) => $this->aplicarConcentracao($linha, $valorGeral))
            ->sortByDesc('valor_total_efd')
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $participantes
     * @return Collection<int, array<string, mixed>>
     */
    private function agruparPorRegime(Collection $participantes): Collection
    {
        return $participantes
            ->groupBy(fn (array $row) => $row['regime_tributario'] ?: 'Nao identificado')
            ->map(function (Collection $grupo, string $regime): array {
                $valorTotal = (float) $grupo->sum('valor_total_efd');
                $totalParticipantes = $grupo->count();

                return [
                    'regime' => $regime,
                    'participantes' => $totalParticipantes,
                    'valor_total_efd' => $valorTotal,
                    'total_notas_efd' => (int) $grupo->sum('total_notas_efd'),
                    'ticket_medio_participante' => $totalParticipantes > 0
                        ? round($valorTotal / $totalParticipantes, 2)
                        : 0.0,
                ];
            })
            ->sortByDesc('valor_total_efd')
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $participantes
     * @return Collection<int, array<string, mixed>>
     */
    private function agruparPorSituacao(Collection $participantes): Collection
    {
        return $participantes
            ->groupBy(fn (array $row) => $row['situacao_cadastral'] ?: 'Nao informada')
            ->map(function (Collection $grupo, string $situacao): array {
                return [
                    'situacao' => $situacao,
                    'participantes' => $grupo->count(),
                    'valor_total_efd' => (float) $grupo->sum('valor_total_efd'),
                    'total_notas_efd' => (int) $grupo->sum('total_notas_efd'),
                ];
            })
            ->sortByDesc('valor_total_efd')
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapParticipanteRow(object $row): array
    {
        $resultadoDados = is_array($row->resultado_dados)
            ? $row->resultado_dados
            : (is_string($row->resultado_dados) ? json_decode($row->resultado_dados, true) : []);

        $participante = new Participante([
            'id' => $row->participante_efd_id,
            'documento' => $row->documento,
            'razao_social' => $row->razao_social,
            'regime_tributario' => $row->regime_participante,
            'situacao_cadastral' => $row->situacao_participante,
            'crt' => $row->crt,
        ]);

        $resultado = new ConsultaResultado([
            'participante_id' => $row->participante_consulta_id,
            'resultado_dados' => is_array($resultadoDados) ? $resultadoDados : [],
            'status' => ConsultaResultado::STATUS_SUCESSO,
            'consultado_em' => $row->consultado_em,
        ]);
        $resultado->setRelation('participante', $participante);

        $parecerResumo = $this->parecerFiscalService->gerarResumo($resultado->getParecerFiscalPayload());
        $parecerResumo = array_merge($this->badgesConsultaAntiga($row->consultado_em), $parecerResumo);
        $situacao = $resultado->getSituacaoCadastral() ?: $participante->situacao_cadastral;
        $situacaoNormalizada = strtoupper(trim((string) $situacao));

        return [
            'participante_id' => (int) $row->participante_efd_id,
            'participante_consulta_id' => (int) $row->participante_consulta_id,
            'documento' => $row->documento,
            'razao_social' => $row->razao_social,
            'regime_tributario' => $resultado->getRegimeTributarioLabel() ?: 'Nao identificado',
            'situacao_cadastral' => $situacao ?: 'Nao informada',
            'situacao_irregular' => $situacao !== null && $situacaoNormalizada !== '' && ! in_array($situacaoNormalizada, ['02', 'ATIVA'], true),
            'consultado_em' => $row->consultado_em,
            'valor_total_efd' => (float) $row->valor_total_efd,
            'total_notas_efd' => (int) $row->total_notas_efd,
            'valor_entradas' => (float) $row->valor_entradas,
            'valor_saidas' => (float) $row->valor_saidas,
            'total_entradas' => (int) $row->total_entradas,
            'total_saidas' => (int) $row->total_saidas,
            'ticket_medio' => (int) $row->total_notas_efd > 0
                ? round((float) $row->valor_total_efd / (int) $row->total_notas_efd, 2)
                : 0.0,
            'parecer_resumo' => array_map(fn (array $item) => [
                'label' => $item['badge_label'] ?? $item['titulo'] ?? 'Parecer',
                'hex' => $item['hex'] ?? '#6b7280',
                'tooltip' => $item['tooltip'] ?? ($item['descricao'] ?? ''),
            ], $parecerResumo),
        ];
    }

    /**
     * @param  array{data_inicio?: ?string, data_fim?: ?string, cliente_id?: mixed}  $filtros
     */
    private function baseQuery(int $userId, array $filtros)
    {
        $docConsulta = $this->normalizeDocumentoExpr('pcr.documento');
        $docEfd = $this->normalizeDocumentoExpr('p.documento');

        $latestResultados = DB::table('consulta_resultados as cr')
            ->join('participantes as pcr', 'pcr.id', '=', 'cr.participante_id')
            ->join('consulta_lotes as cl', 'cl.id', '=', 'cr.consulta_lote_id')
            ->where('cl.user_id', $userId)
            ->where('cr.status', ConsultaResultado::STATUS_SUCESSO)
            ->whereRaw("{$docConsulta} <> ''")
            ->selectRaw("MAX(cr.id) as id, {$docConsulta} as documento_normalizado")
            ->groupByRaw($docConsulta);

        return DB::table('efd_notas as en')
            ->join('participantes as p', 'p.id', '=', 'en.participante_id')
            ->joinSub($latestResultados, 'ult', fn ($join) => $join->whereRaw("ult.documento_normalizado = {$docEfd}"))
            ->join('consulta_resultados as cr', 'cr.id', '=', 'ult.id')
            ->join('participantes as pc', 'pc.id', '=', 'cr.participante_id')
            ->where('en.user_id', $userId)
            ->whereRaw("{$docEfd} <> ''")
            ->when(filled($filtros['cliente_id'] ?? null), fn ($q) => $q->where('en.cliente_id', (int) $filtros['cliente_id']))
            ->when(filled($filtros['data_inicio'] ?? null), fn ($q) => $q->where('en.data_emissao', '>=', $filtros['data_inicio']))
            ->when(filled($filtros['data_fim'] ?? null), fn ($q) => $q->where('en.data_emissao', '<=', $filtros['data_fim']))
            ->select([
                DB::raw('MAX(en.participante_id) as participante_efd_id'),
                DB::raw('MAX(cr.participante_id) as participante_consulta_id'),
                'p.documento',
                'p.razao_social',
                'p.regime_tributario as regime_participante',
                'p.situacao_cadastral as situacao_participante',
                'p.crt',
                'cr.resultado_dados',
                'cr.consultado_em',
                DB::raw('SUM(en.valor_total) as valor_total_efd'),
                DB::raw('COUNT(en.id) as total_notas_efd'),
                DB::raw("SUM(CASE WHEN en.tipo_operacao = 'entrada' THEN en.valor_total ELSE 0 END) as valor_entradas"),
                DB::raw("SUM(CASE WHEN en.tipo_operacao = 'saida' THEN en.valor_total ELSE 0 END) as valor_saidas"),
                DB::raw("SUM(CASE WHEN en.tipo_operacao = 'entrada' THEN 1 ELSE 0 END) as total_entradas"),
                DB::raw("SUM(CASE WHEN en.tipo_operacao = 'saida' THEN 1 ELSE 0 END) as total_saidas"),
            ])
            ->groupBy(
                'p.documento',
                'p.razao_social',
                'p.regime_tributario',
                'p.situacao_cadastral',
                'p.crt',
                'cr.resultado_dados',
                'cr.consultado_em'
            );
    }

    private function totalParticipantesConsultados(int $userId): int
    {
        $docConsulta = $this->normalizeDocumentoExpr('p.documento');

        return (int) DB::table('consulta_resultados as cr')
            ->join('participantes as p', 'p.id', '=', 'cr.participante_id')
            ->join('consulta_lotes as cl', 'cl.id', '=', 'cr.consulta_lote_id')
            ->where('cl.user_id', $userId)
            ->where('cr.status', ConsultaResultado::STATUS_SUCESSO)
            ->whereRaw("{$docConsulta} <> ''")
            ->distinct()
            ->count(DB::raw($docConsulta));
    }

    private function normalizeDocumentoExpr(string $column): string
    {
        return "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE({$column}, ''), '.', ''), '/', ''), '-', ''), ' ', ''), '\\\\', '')";
    }

    /**
     * @param  array<string, mixed>  $linha
     * @return array<string, mixed>
     */
    private function aplicarConcentracao(array $linha, float $valorGeral): array
    {
        $valorParticipante = (float) ($linha['valor_total_efd'] ?? 0);
        $pct = $valorGeral > 0
            ? round($valorParticipante / $valorGeral * 100, 2)
            : 0.0;

        $linha['concentracao_percentual'] = $pct;

        if ($pct >= self::CONCENTRACAO_THRESHOLD_PERCENT) {
            $linha['parecer_resumo'][] = [
                'label' => 'Concentração '.number_format($pct, 1, ',', '.').'%',
                'hex' => '#b45309',
                'tooltip' => 'Participante representa '.number_format($pct, 1, ',', '.').'% do valor total EFD dos consultados visíveis',
            ];
        }

        return $linha;
    }

    /**
     * @return array<int, array{badge_label: string, hex: string, tooltip: string}>
     */
    private function badgesConsultaAntiga(mixed $consultadoEm): array
    {
        if ($consultadoEm === null || $consultadoEm === '') {
            return [[
                'badge_label' => 'Consulta antiga',
                'hex' => '#9ca3af',
                'tooltip' => 'Sem data de consulta registrada',
            ]];
        }

        $dias = (int) Carbon::parse($consultadoEm)->diffInDays(now());

        if ($dias < self::DIAS_CONSULTA_ANTIGA) {
            return [];
        }

        return [[
            'badge_label' => 'Consulta antiga',
            'hex' => '#9ca3af',
            'tooltip' => "Última consulta há {$dias} dias (limite ".self::DIAS_CONSULTA_ANTIGA.')',
        ]];
    }
}
