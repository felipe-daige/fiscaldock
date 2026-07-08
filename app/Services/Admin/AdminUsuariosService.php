<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Lista de usuários e atividade derivada para o console admin (read-only, escopo global).
 */
class AdminUsuariosService
{
    private const ORDENAVEIS = ['created_at', 'ultima_atividade_ts', 'credits', 'qtd_consultas'];

    /**
     * @param  array{q?:string,ordenar?:string}  $filtros
     */
    public function lista(array $filtros, int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $ordenar = in_array($filtros['ordenar'] ?? '', self::ORDENAVEIS, true) ? $filtros['ordenar'] : 'created_at';

        $q = DB::table('users')->selectRaw(
            'users.*,
             (select count(*) from consulta_lotes cl where cl.user_id = users.id) as qtd_consultas,
             ((select count(*) from efd_importacoes ei where ei.user_id = users.id)
              + (select count(*) from xml_importacoes xi where xi.user_id = users.id)) as qtd_importacoes,
             (select max(last_activity) from sessions se where se.user_id = users.id) as ultima_atividade_ts,
             (select sp.nome from account_subscriptions s join subscription_plans sp on sp.id = s.subscription_plan_id
                where s.user_id = users.id and s.status = \'ativa\' limit 1) as plano_nome'
        );

        $busca = trim((string) ($filtros['q'] ?? ''));
        if ($busca !== '') {
            $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $busca).'%';
            $q->where(function ($w) use ($like) {
                $w->where('name', 'ilike', $like)
                    ->orWhere('email', 'ilike', $like)
                    ->orWhere('empresa', 'ilike', $like)
                    ->orWhere('cnpj', 'ilike', $like);
            });
        }

        $q->orderByRaw("{$ordenar} desc nulls last");

        return $q->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @return array{qtd_consultas:int,qtd_importacoes:int,creditos_consumidos:float,total_pago:float}
     */
    public function kpis(int $userId): array
    {
        return [
            'qtd_consultas' => DB::table('consulta_lotes')->where('user_id', $userId)->count(),
            'qtd_importacoes' => DB::table('efd_importacoes')->where('user_id', $userId)->count()
                + DB::table('xml_importacoes')->where('user_id', $userId)->count(),
            'creditos_consumidos' => abs((float) DB::table('credit_transactions')->where('user_id', $userId)->where('amount', '<', 0)->sum('amount')),
            'total_pago' => (float) DB::table('mercado_pago_payments')->where('user_id', $userId)->where('status', 'approved')->sum('valor'),
        ];
    }

    public function assinaturaAtiva(int $userId): ?object
    {
        return DB::table('account_subscriptions as s')
            ->join('subscription_plans as p', 'p.id', '=', 's.subscription_plan_id')
            ->where('s.user_id', $userId)->where('s.status', 'ativa')
            ->selectRaw('s.*, p.nome as plano_nome, p.preco_mensal_centavos, p.preco_anual_centavos')
            ->first();
    }

    public function ultimaSessao(int $userId): ?object
    {
        return DB::table('sessions')->where('user_id', $userId)->orderByDesc('last_activity')->first();
    }

    /**
     * Agregados do detalhe operacional do usuário.
     *
     * @return array<string, mixed>
     */
    public function detalhe(User $usuario): array
    {
        $userId = (int) $usuario->id;
        $desde30 = now()->subDays(30)->toDateTimeString();

        $consultasPorStatus = $this->contagemPorStatus('consulta_lotes', $userId);
        $importacoesPorStatus = $this->somarContagens(
            $this->contagemPorStatus('efd_importacoes', $userId),
            $this->contagemPorStatus('xml_importacoes', $userId),
        );
        $clearancePorStatus = $this->somarContagens(
            $this->contagemPorStatus('nfe_consultas', $userId),
            $this->contagemPorStatus('cte_consultas', $userId),
        );

        $ultimaCompra = DB::table('mercado_pago_payments')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->orderByDesc('created_at')
            ->first();

        $trialExpiraEm = $usuario->trial_expires_at;
        $trialAtivo = $usuario->hasActiveTrial();
        $trialExpirado = $usuario->isTrialExpired();

        return [
            'conta' => [
                'bloqueado' => $usuario->bloqueado_em !== null,
                'admin' => (bool) $usuario->is_admin,
                'lgpd_solicitada' => $usuario->deletion_requested_at !== null,
                'tem_compra_confirmada' => DB::table('credit_transactions')
                    ->where('user_id', $userId)->where('type', 'purchase')->where('amount', '>', 0)->exists(),
                'trial_usado' => (bool) $usuario->trial_used,
                'trial_ativo' => $trialAtivo,
                'trial_expirado' => $trialExpirado,
                'trial_status' => $trialAtivo ? 'Trial ativo' : ($trialExpirado ? 'Trial expirado' : ($usuario->trial_used ? 'Trial usado' : 'Sem trial')),
                'trial_expira_em' => $trialExpiraEm,
                'trial_dias_restantes' => $trialAtivo && $trialExpiraEm ? now()->diffInDays($trialExpiraEm, false) : null,
            ],
            'financeiro' => [
                'total_pago' => (float) DB::table('mercado_pago_payments')
                    ->where('user_id', $userId)->where('status', 'approved')->sum('valor'),
                'compras_aprovadas' => DB::table('mercado_pago_payments')
                    ->where('user_id', $userId)->where('status', 'approved')->count(),
                'ultima_compra_em' => $ultimaCompra?->created_at,
                'ultima_compra_valor' => $ultimaCompra ? (float) $ultimaCompra->valor : null,
                'creditos_consumidos' => abs((float) DB::table('credit_transactions')
                    ->where('user_id', $userId)->where('amount', '<', 0)->sum('amount')),
                'movimentos_recentes' => DB::table('credit_transactions')
                    ->where('user_id', $userId)
                    ->orderByDesc('created_at')
                    ->limit(8)
                    ->get(['type', 'amount', 'balance_after', 'description', 'created_at']),
                'pagamentos_por_status' => $this->contagemPorStatus('mercado_pago_payments', $userId),
            ],
            'uso' => [
                'clientes_total' => DB::table('clientes')->where('user_id', $userId)->count(),
                'clientes_ativos' => DB::table('clientes')->where('user_id', $userId)->where('ativo', true)->count(),
                'participantes_total' => DB::table('participantes')->where('user_id', $userId)->count(),
                'monitoramentos_ativos' => DB::table('monitoramento_assinaturas')->where('user_id', $userId)->where('status', 'ativo')->count(),
                'consultas_30d' => $this->contarDesde('consulta_lotes', $userId, $desde30),
                'importacoes_30d' => $this->contarDesde('efd_importacoes', $userId, $desde30)
                    + $this->contarDesde('xml_importacoes', $userId, $desde30),
                'clearance_30d' => $this->contarDesde('nfe_consultas', $userId, $desde30)
                    + $this->contarDesde('cte_consultas', $userId, $desde30),
                'consultas_por_status' => $consultasPorStatus,
                'importacoes_por_status' => $importacoesPorStatus,
                'clearance_por_status' => $clearancePorStatus,
                'efd_notas' => DB::table('efd_notas')->where('user_id', $userId)->count(),
                'xml_notas' => DB::table('xml_notas')->where('user_id', $userId)->count(),
            ],
        ];
    }

    /**
     * Atividade derivada (timeline) — UNION das fontes, desc por data.
     *
     * @return Collection<int, array{tipo:string,data:?string,titulo:string,detalhe:?string}>
     */
    public function timeline(int $userId, int $limit = 50): Collection
    {
        $lim = max(1, min(200, $limit));
        $sql = "
            SELECT 'consulta' AS tipo, created_at AS data, ('Consulta de '||total_participantes||' CNPJ(s)') AS titulo, status AS detalhe FROM consulta_lotes WHERE user_id = :u
            UNION ALL
            SELECT 'importacao_efd', created_at, ('Importação EFD '||coalesce(tipo_efd,'')), status FROM efd_importacoes WHERE user_id = :u
            UNION ALL
            SELECT 'importacao_xml', created_at, 'Importação XML', status FROM xml_importacoes WHERE user_id = :u
            UNION ALL
            SELECT 'credito', created_at, (type||' '||amount::text), coalesce(description,'') FROM credit_transactions WHERE user_id = :u
            UNION ALL
            SELECT 'pagamento', created_at, ('Pagamento '||status||' R$ '||valor::text), coalesce(tipo,'') FROM mercado_pago_payments WHERE user_id = :u
            ORDER BY data DESC NULLS LAST
            LIMIT {$lim}";

        return collect(DB::select($sql, ['u' => $userId]))->map(fn ($r) => [
            'tipo' => $r->tipo,
            'data' => $r->data,
            'titulo' => $r->titulo,
            'detalhe' => $r->detalhe,
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function contagemPorStatus(string $tabela, int $userId): array
    {
        return DB::table($tabela)
            ->where('user_id', $userId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    private function contarDesde(string $tabela, int $userId, string $desde): int
    {
        return DB::table($tabela)
            ->where('user_id', $userId)
            ->where('created_at', '>=', $desde)
            ->count();
    }

    /**
     * @param  array<string, int>  ...$contagens
     * @return array<string, int>
     */
    private function somarContagens(array ...$contagens): array
    {
        $resultado = [];

        foreach ($contagens as $grupo) {
            foreach ($grupo as $status => $total) {
                $resultado[$status] = ($resultado[$status] ?? 0) + (int) $total;
            }
        }

        ksort($resultado);

        return $resultado;
    }
}
