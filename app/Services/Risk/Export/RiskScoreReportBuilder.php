<?php

namespace App\Services\Risk\Export;

use App\Models\Cliente;
use App\Models\Participante;
use App\Models\ParticipanteScore;
use App\Support\Reports\ReportTheme;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Fonte única do PDF, XLSX e CSV do Score Fiscal.
 *
 * Reproduz o recorte da tela (cliente, classificação e busca), sem paginação, incluindo
 * avaliados e não consultados. CPF e a duplicata Participante/PROPRIO ficam de fora.
 */
class RiskScoreReportBuilder
{
    public const LIMITE_PDF = 80;

    public const COLUNAS = [
        'Status',
        'CNPJ',
        'Razão social',
        'Nome fantasia',
        'Tipo',
        'Papel comercial',
        'UF',
        'Score total',
        'Classificação',
        'Cadastral',
        'CND Federal',
        'CND Estadual',
        'FGTS',
        'CNDT',
        'Crédito IBS/CBS',
        'Última consulta',
        'Próxima consulta',
    ];

    private const CLASSIFICACOES = ['baixo', 'medio', 'alto', 'critico', 'inconclusivo'];

    private const CLASSIFICACAO_LABELS = [
        'baixo' => 'Baixo Risco',
        'medio' => 'Médio Risco',
        'alto' => 'Alto Risco',
        'critico' => 'Risco Crítico',
        'inconclusivo' => 'Risco Não Conclusivo',
    ];

    /**
     * @param  array{cliente_id?:mixed,classificacao?:mixed,busca?:mixed}  $filtros
     */
    public function montar(int $userId, array $filtros): array
    {
        $cliente = $this->clienteSelecionado($userId, $filtros['cliente_id'] ?? null);
        $classificacao = in_array($filtros['classificacao'] ?? null, self::CLASSIFICACOES, true)
            ? (string) $filtros['classificacao']
            : null;
        $busca = trim((string) ($filtros['busca'] ?? ''));

        $avaliados = $this->avaliados($userId, $cliente?->id, $classificacao, $busca);
        $naoConsultados = $this->naoConsultados($userId, $cliente?->id, $busca);

        $idsParticipantes = $avaliados->pluck('participante_id')
            ->merge($naoConsultados->where('tipo', 'participante')->pluck('id'))
            ->filter()->unique()->values();
        $papeis = $this->papeisParticipante($userId, $idsParticipantes->all());

        $registros = $avaliados
            ->map(fn (ParticipanteScore $score) => $this->registroAvaliado($score, $papeis))
            ->concat($naoConsultados->map(fn ($alvo) => $this->registroNaoConsultado($alvo, $papeis)))
            ->values();

        return [
            'titulo' => 'Score Fiscal — Risco de Regularidade',
            'gerado_em' => now(),
            'filtros' => [
                'Cliente' => $cliente?->razao_social ?? 'Todos os CNPJs',
                'Classificação' => $classificacao ? self::CLASSIFICACAO_LABELS[$classificacao] : 'Todas',
                'Busca' => $busca !== '' ? $busca : 'Sem filtro',
            ],
            'kpis' => $this->kpis($avaliados, $naoConsultados->count()),
            'colunas' => self::COLUNAS,
            'registros' => $registros->all(),
        ];
    }

    private function clienteSelecionado(int $userId, mixed $clienteId): ?Cliente
    {
        if (! ctype_digit((string) $clienteId)) {
            return null;
        }

        return Cliente::where('user_id', $userId)->find((int) $clienteId);
    }

    private function avaliados(int $userId, ?int $clienteId, ?string $classificacao, string $busca)
    {
        $cnpjRaw = "length(regexp_replace(coalesce(documento, ''), '[^0-9]', '', 'g')) = 14";

        return ParticipanteScore::query()
            ->where('user_id', $userId)
            ->with(['participante', 'cliente'])
            ->where(function (Builder $query) use ($cnpjRaw) {
                $query->whereHas('participante', fn (Builder $participante) => $participante
                    ->whereRaw($cnpjRaw)
                    ->where(fn (Builder $q) => $q->where('origem_tipo', '!=', 'PROPRIO')->orWhereNull('origem_tipo')))
                    ->orWhereHas('cliente', fn (Builder $cliente) => $cliente->whereRaw($cnpjRaw));
            })
            ->when($clienteId, fn (Builder $query) => $query->where(function (Builder $escopo) use ($clienteId) {
                $escopo->where('cliente_id', $clienteId)
                    ->orWhereHas('participante', fn (Builder $participante) => $participante->where('cliente_id', $clienteId));
            }))
            ->when($classificacao, fn (Builder $query) => $query->where('classificacao', $classificacao))
            ->when($busca !== '', fn (Builder $query) => $query->where(function (Builder $alvo) use ($busca) {
                $filtro = fn (Builder $q) => $q->where('razao_social', 'ilike', "%{$busca}%")
                    ->orWhere('documento', 'like', "%{$busca}%");
                $alvo->whereHas('participante', $filtro)->orWhereHas('cliente', $filtro);
            }))
            ->orderByRaw("CASE classificacao WHEN 'critico' THEN 5 WHEN 'alto' THEN 4 WHEN 'medio' THEN 3 WHEN 'baixo' THEN 2 WHEN 'inconclusivo' THEN 1 ELSE 0 END DESC")
            ->orderByRaw('score_total desc nulls last')
            ->orderByDesc('ultima_consulta_em')
            ->get();
    }

    private function naoConsultados(int $userId, ?int $clienteId, string $busca)
    {
        $cnpjRaw = "length(regexp_replace(coalesce(documento, ''), '[^0-9]', '', 'g')) = 14";
        $filtroBusca = fn (Builder $query) => $query->where(fn (Builder $q) => $q
            ->where('razao_social', 'ilike', "%{$busca}%")
            ->orWhere('documento', 'like', "%{$busca}%"));

        $participantes = Participante::query()
            ->where('user_id', $userId)
            ->whereRaw($cnpjRaw)
            ->whereDoesntHave('score')
            ->where(fn (Builder $q) => $q->where('origem_tipo', '!=', 'PROPRIO')->orWhereNull('origem_tipo'))
            ->when($clienteId, fn (Builder $query) => $query->where('cliente_id', $clienteId))
            ->when($busca !== '', $filtroBusca)
            ->get()
            ->map(function (Participante $participante) {
                $participante->setAttribute('tipo', 'participante');

                return $participante;
            });

        $clientes = Cliente::query()
            ->where('user_id', $userId)
            ->whereRaw($cnpjRaw)
            ->whereDoesntHave('score')
            ->when($clienteId, fn (Builder $query) => $query->whereKey($clienteId))
            ->when($busca !== '', $filtroBusca)
            ->get()
            ->map(function (Cliente $cliente) {
                $cliente->setAttribute('tipo', 'cliente');

                return $cliente;
            });

        return $participantes->concat($clientes)
            ->sortBy(fn ($alvo) => mb_strtoupper((string) $alvo->razao_social))
            ->values();
    }

    /** @return array<int,string> */
    private function papeisParticipante(int $userId, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $operacoes = DB::table('efd_notas')
            ->select('participante_id', 'tipo_operacao')
            ->where('user_id', $userId)
            ->whereIn('participante_id', $ids)
            ->groupBy('participante_id', 'tipo_operacao')
            ->get()
            ->groupBy('participante_id');

        return $operacoes->map(function ($linhas) {
            $tipos = $linhas->pluck('tipo_operacao');
            $entrada = $tipos->contains('entrada');
            $saida = $tipos->contains('saida');

            return match (true) {
                $entrada && $saida => 'Ambos',
                $entrada => 'Fornecedor',
                $saida => 'Comprador',
                default => '—',
            };
        })->all();
    }

    private function registroAvaliado(ParticipanteScore $score, array $papeis): array
    {
        return [
            'status' => 'Avaliado',
            'cnpj' => $score->alvo_documento,
            'razao_social' => $score->alvo_nome,
            'nome_fantasia' => $score->alvo_nome_fantasia ?: '—',
            'tipo' => $score->alvo_tipo === 'cliente' ? 'Cliente' : 'Participante',
            'papel' => $score->participante_id ? ($papeis[$score->participante_id] ?? '—') : '—',
            'uf' => $score->alvo_uf ?: '—',
            'score_total' => $score->score_total,
            'classificacao' => self::CLASSIFICACAO_LABELS[$score->classificacao] ?? 'Não Avaliado',
            'classificacao_codigo' => $score->classificacao,
            'score_cadastral' => $score->score_cadastral,
            'score_cnd_federal' => $score->score_cnd_federal,
            'score_cnd_estadual' => $score->score_cnd_estadual,
            'score_fgts' => $score->score_fgts,
            'score_trabalhista' => $score->score_trabalhista,
            'credito_reforma' => $this->creditoLabel($score->score_credito_reforma),
            'ultima_consulta' => $score->ultima_consulta_em?->format('d/m/Y H:i') ?? '—',
            'proxima_consulta' => $score->proxima_consulta_em?->format('d/m/Y') ?? '—',
        ];
    }

    private function registroNaoConsultado($alvo, array $papeis): array
    {
        $tipo = $alvo->tipo === 'cliente' ? 'Cliente' : 'Participante';

        return [
            'status' => 'Não consultado',
            'cnpj' => $this->formatarCnpj($alvo->documento),
            'razao_social' => $alvo->razao_social ?: 'N/A',
            'nome_fantasia' => $alvo->nome_fantasia ?: '—',
            'tipo' => $tipo,
            'papel' => $alvo->tipo === 'participante' ? ($papeis[$alvo->id] ?? '—') : '—',
            'uf' => $alvo->uf ?: '—',
            'score_total' => null,
            'classificacao' => 'Não Avaliado',
            'classificacao_codigo' => null,
            'score_cadastral' => null,
            'score_cnd_federal' => null,
            'score_cnd_estadual' => null,
            'score_fgts' => null,
            'score_trabalhista' => null,
            'credito_reforma' => '—',
            'ultima_consulta' => '—',
            'proxima_consulta' => '—',
        ];
    }

    public static function linha(array $registro): array
    {
        return [
            $registro['status'],
            $registro['cnpj'],
            $registro['razao_social'],
            $registro['nome_fantasia'],
            $registro['tipo'],
            $registro['papel'],
            $registro['uf'],
            $registro['score_total'],
            $registro['classificacao'],
            $registro['score_cadastral'],
            $registro['score_cnd_federal'],
            $registro['score_cnd_estadual'],
            $registro['score_fgts'],
            $registro['score_trabalhista'],
            $registro['credito_reforma'],
            $registro['ultima_consulta'],
            $registro['proxima_consulta'],
        ];
    }

    public static function corClassificacao(?string $classificacao): string
    {
        return ReportTheme::riscoHex($classificacao);
    }

    private function creditoLabel(?int $score): string
    {
        return match (true) {
            $score === null => 'Não identificado',
            $score <= 0 => 'Gera integral',
            $score >= 100 => 'Não gera',
            default => 'Parcial',
        };
    }

    private function formatarCnpj(mixed $documento): string
    {
        $cnpj = preg_replace('/\D/', '', (string) $documento);

        return strlen($cnpj) === 14
            ? preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $cnpj)
            : $cnpj;
    }

    private function kpis($avaliados, int $naoConsultados): array
    {
        return [
            'avaliados' => $avaliados->count(),
            'baixo' => $avaliados->where('classificacao', 'baixo')->count(),
            'medio' => $avaliados->where('classificacao', 'medio')->count(),
            'alto' => $avaliados->where('classificacao', 'alto')->count(),
            'critico' => $avaliados->where('classificacao', 'critico')->count(),
            'inconclusivo' => $avaliados->where('classificacao', 'inconclusivo')->count(),
            'nao_consultados' => $naoConsultados,
        ];
    }
}
