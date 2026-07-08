<?php

namespace App\Services\Clientes;

use App\Models\Cliente;
use App\Services\Consultas\ParticipanteFiscalResumoService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Monta o panorama tabular ("de uma folha") da carteira de clientes para o PDF de listagem.
 * Reaproveita a mesma dedup EFD+XML da grade (`DashboardController::clientes`) e a regularidade
 * canônica (`ParticipanteFiscalResumoService::mapaRegularidadeCliente`) para que o volume
 * movimentado e o status batam com a tela. Escopo sempre por `user_id` (nunca vaza cross-user).
 */
final class ClienteListagemBuilder
{
    private const REGULARIDADE_LABEL = [
        'regular' => 'Regular',
        'irregular' => 'Irregular',
        'indeterminada' => 'Indeterminada',
    ];

    public function __construct(private ParticipanteFiscalResumoService $resumo) {}

    /**
     * @param  array<int,int|string>  $ids  clientes selecionados (filtrados por user_id)
     * @return array{clientes:array<int,array<string,mixed>>,total:int,total_movimentado:float,gerado_em:string}|null
     */
    public function montar(int $userId, array $ids): ?array
    {
        $idsLimpos = collect($ids)->map(fn ($id) => (int) $id)->filter()->unique()->values();
        if ($idsLimpos->isEmpty()) {
            return null;
        }

        $clientes = Cliente::where('user_id', $userId)
            ->whereIn('id', $idsLimpos)
            ->orderByRaw("COALESCE(razao_social, nome, '') asc")
            ->get();

        if ($clientes->isEmpty()) {
            return null;
        }

        // Volume movimentado por cliente — EFD (fiscal, não cancelada) + XML deduplicado por
        // chave (EFD vence). Mesma regra da grade para o número não divergir da tela.
        $movPorCliente = DB::query()
            ->fromSub($this->notasUnificadas($userId), 'n')
            ->whereIn('cliente_id', $clientes->pluck('id'))
            ->groupBy('cliente_id')
            ->selectRaw('cliente_id, SUM(valor_total) as valor, MAX(data_emissao) as ultima')
            ->get()
            ->keyBy('cliente_id');

        // Regularidade + última consulta pela fonte única (documento normalizado).
        $mapa = $this->resumo->mapaRegularidadeCliente($userId);
        $docParaClasse = [];
        foreach ($mapa['porRegularidade'] as $classe => $docs) {
            foreach (array_keys($docs) as $doc) {
                $docParaClasse[$doc] = $classe;
            }
        }

        $linhas = $clientes->map(function (Cliente $c) use ($movPorCliente, $mapa, $docParaClasse) {
            $doc = preg_replace('/\D/', '', (string) $c->documento);
            $mov = $movPorCliente->get($c->id);
            $ultimaConsulta = $mapa['ultimaPorDoc'][$doc] ?? null;
            $classe = $docParaClasse[$doc] ?? null;

            return [
                'nome' => $c->razao_social ?: ($c->nome ?: '—'),
                'documento' => $c->documento_formatado,
                'tipo' => $c->tipo_pessoa ?: '—',
                'uf' => $c->uf ?: '—',
                'situacao' => $c->situacao_cadastral ?: '—',
                'regime' => $c->regime_tributario ?: '—',
                'movimentado' => (float) ($mov->valor ?? 0),
                'ultima_nota' => $mov?->ultima ? Carbon::parse($mov->ultima)->format('m/Y') : null,
                // CPF não é consultável como PJ → não é "não consultado", é pessoa física.
                'regularidade' => $classe
                    ? (self::REGULARIDADE_LABEL[$classe] ?? ucfirst($classe))
                    : \App\Support\Documento::rotuloSemConsulta($c->documento, 'Não consultado'),
                'regularidade_classe' => $classe ?? (\App\Support\Documento::ehCpf($c->documento) ? \App\Support\Documento::CLASSE_CPF : 'nao_consultado'),
                'ultima_consulta' => $ultimaConsulta ? Carbon::parse($ultimaConsulta)->format('d/m/Y') : null,
            ];
        })->all();

        return [
            'clientes' => $linhas,
            'total' => count($linhas),
            'total_movimentado' => (float) collect($linhas)->sum('movimentado'),
            'gerado_em' => now()->format('d/m/Y H:i'),
        ];
    }

    /** União EFD(fiscal, não cancelada) + XML (chave ausente no EFD) — dedup igual à grade. */
    private function notasUnificadas(int $userId): \Illuminate\Database\Query\Builder
    {
        return DB::table('efd_notas')
            ->where('user_id', $userId)
            ->where('origem_arquivo', 'fiscal')
            ->where('cancelada', false)
            ->selectRaw('cliente_id, valor_total, data_emissao')
            ->unionAll(
                DB::table('xml_notas as x')
                    ->where('x.user_id', $userId)
                    ->whereNotExists(fn ($sub) => $sub->select(DB::raw(1))
                        ->from('efd_notas as e')
                        ->whereColumn('e.chave_acesso', 'x.chave_acesso')
                        ->where('e.user_id', $userId)
                        ->where('e.origem_arquivo', 'fiscal')
                        ->where('e.cancelada', false))
                    ->selectRaw('x.cliente_id, x.valor_total, x.data_emissao')
            );
    }
}
