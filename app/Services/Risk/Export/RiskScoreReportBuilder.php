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
     * @param  array{cliente_id?:mixed,classificacao?:mixed,busca?:mixed,status?:mixed,tipo?:mixed,credito?:mixed,score_min?:mixed,score_max?:mixed}  $filtros
     */
    public function montar(int $userId, array $filtros): array
    {
        $cliente = $this->clienteSelecionado($userId, $filtros['cliente_id'] ?? null);
        $classificacao = in_array($filtros['classificacao'] ?? null, self::CLASSIFICACOES, true)
            ? (string) $filtros['classificacao']
            : null;
        $busca = trim((string) ($filtros['busca'] ?? ''));
        $status = in_array($filtros['status'] ?? null, ['consultados', 'nao_consultados'], true) ? (string) $filtros['status'] : 'todos';
        $tipo = in_array($filtros['tipo'] ?? null, ['cliente', 'participante'], true) ? (string) $filtros['tipo'] : 'todos';
        $credito = in_array($filtros['credito'] ?? null, ['gera', 'parcial', 'nao_gera', 'indefinido'], true) ? (string) $filtros['credito'] : 'todos';
        $scoreMin = $this->scoreFiltro($filtros['score_min'] ?? null);
        $scoreMax = $this->scoreFiltro($filtros['score_max'] ?? null);
        if ($scoreMin !== null && $scoreMax !== null && $scoreMin > $scoreMax) {
            [$scoreMin, $scoreMax] = [$scoreMax, $scoreMin];
        }

        $avaliados = $status === 'nao_consultados'
            ? collect()
            : $this->avaliados($userId, $cliente?->id, $classificacao, $busca, $tipo, $credito, $scoreMin, $scoreMax);
        $naoConsultados = $status === 'consultados' || $credito !== 'todos' || $scoreMin !== null || $scoreMax !== null
            ? collect()
            : $this->naoConsultados($userId, $cliente?->id, $busca, $tipo);

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
                'Lista' => $this->statusLabel($status),
                'Tipo' => $this->tipoLabel($tipo),
                'Classificação' => $classificacao ? self::CLASSIFICACAO_LABELS[$classificacao] : 'Todas',
                'Crédito IBS/CBS' => $this->creditoFiltroLabel($credito),
                'Score' => $this->scoreLabel($scoreMin, $scoreMax),
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

    private function avaliados(int $userId, ?int $clienteId, ?string $classificacao, string $busca, string $tipo, string $credito, ?int $scoreMin, ?int $scoreMax)
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
            ->when($tipo === 'cliente', fn (Builder $query) => $query->whereNotNull('cliente_id')->whereNull('participante_id'))
            ->when($tipo === 'participante', fn (Builder $query) => $query->whereNotNull('participante_id'))
            ->when($scoreMin !== null, fn (Builder $query) => $query->where('score_total', '>=', $scoreMin))
            ->when($scoreMax !== null, fn (Builder $query) => $query->where('score_total', '<=', $scoreMax))
            ->when($credito === 'gera', fn (Builder $query) => $query->where('score_credito_reforma', '<=', 0))
            ->when($credito === 'parcial', fn (Builder $query) => $query->whereBetween('score_credito_reforma', [1, 99]))
            ->when($credito === 'nao_gera', fn (Builder $query) => $query->where('score_credito_reforma', '>=', 100))
            ->when($credito === 'indefinido', fn (Builder $query) => $query->whereNull('score_credito_reforma'))
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

    private function naoConsultados(int $userId, ?int $clienteId, string $busca, string $tipo)
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
            ->when($tipo === 'cliente', fn (Builder $query) => $query->whereRaw('1 = 0'))
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
            ->when($tipo === 'participante', fn (Builder $query) => $query->whereRaw('1 = 0'))
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

    private function scoreFiltro(mixed $valor): ?int
    {
        if ($valor === null || $valor === '' || ! is_numeric($valor)) {
            return null;
        }

        return max(0, min(100, (int) $valor));
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'consultados' => 'Consultados',
            'nao_consultados' => 'Não consultados',
            default => 'Todos',
        };
    }

    private function tipoLabel(string $tipo): string
    {
        return match ($tipo) {
            'cliente' => 'Clientes',
            'participante' => 'Participantes',
            default => 'Clientes e participantes',
        };
    }

    private function creditoFiltroLabel(string $credito): string
    {
        return match ($credito) {
            'gera' => 'Gera crédito',
            'parcial' => 'Crédito parcial',
            'nao_gera' => 'Não gera crédito',
            'indefinido' => 'Indefinido',
            default => 'Todos',
        };
    }

    private function scoreLabel(?int $min, ?int $max): string
    {
        return match (true) {
            $min !== null && $max !== null => "{$min} a {$max}",
            $min !== null => "a partir de {$min}",
            $max !== null => "até {$max}",
            default => 'Sem filtro',
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
