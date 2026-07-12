<?php

namespace App\Services\Admin;

use App\Models\AccountSubscription;
use App\Models\AdminActionLog;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminAcaoService
{
    public const STATUS_ASSINATURA = [
        AccountSubscription::STATUS_PENDENTE,
        AccountSubscription::STATUS_ATIVA,
        AccountSubscription::STATUS_INADIMPLENTE,
        AccountSubscription::STATUS_CANCELADA,
    ];

    public function __construct(private CreditService $credit) {}

    public function registrar(User $admin, ?User $alvo, string $acao, string $motivo, array $detalhe = []): AdminActionLog
    {
        return AdminActionLog::create([
            'admin_user_id' => $admin->id,
            'target_user_id' => $alvo?->id,
            'acao' => $acao,
            'motivo' => $motivo,
            'detalhe' => $detalhe ?: null,
            'ip' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    public function bloquear(User $admin, User $alvo, string $motivo): AdminActionLog
    {
        $this->proibirAutoAlvo($admin, $alvo, 'bloquear a própria conta');

        return DB::transaction(function () use ($admin, $alvo, $motivo) {
            $alvo->forceFill(['bloqueado_em' => now()])->save();

            return $this->registrar($admin, $alvo, 'bloquear', $motivo);
        });
    }

    public function desbloquear(User $admin, User $alvo, string $motivo): AdminActionLog
    {
        return DB::transaction(function () use ($admin, $alvo, $motivo) {
            $alvo->forceFill(['bloqueado_em' => null])->save();

            return $this->registrar($admin, $alvo, 'desbloquear', $motivo);
        });
    }

    public function definirAdmin(User $admin, User $alvo, bool $tornarAdmin, string $motivo): AdminActionLog
    {
        if (! $tornarAdmin) {
            $this->proibirAutoAlvo($admin, $alvo, 'rebaixar a própria conta');
        }

        return DB::transaction(function () use ($admin, $alvo, $tornarAdmin, $motivo) {
            $alvo->forceFill(['is_admin' => $tornarAdmin])->save();

            return $this->registrar($admin, $alvo, $tornarAdmin ? 'promover_admin' : 'rebaixar_admin', $motivo);
        });
    }

    public function creditar(User $admin, User $alvo, float $valor, string $motivo): AdminActionLog
    {
        if ((int) $valor === 0) {
            throw new \InvalidArgumentException('Valor do ajuste não pode ser zero.');
        }

        return DB::transaction(function () use ($admin, $alvo, $valor, $motivo) {
            $saldoAntes = (int) $alvo->fresh()->credits;
            $desc = "[admin {$admin->id}] {$motivo}";

            if ($valor > 0) {
                $this->credit->add($alvo, $valor, 'manual_add', $desc);
                $acao = 'creditar';
            } else {
                $ok = $this->credit->deduct($alvo, abs($valor), 'manual_ajuste', $desc);
                if (! $ok) {
                    throw new \RuntimeException('Saldo insuficiente para o débito.');
                }
                $acao = 'debitar';
            }

            $saldoDepois = (int) $alvo->fresh()->credits;

            return $this->registrar($admin, $alvo, $acao, $motivo, [
                'valor' => (int) $valor,
                'saldo_antes' => $saldoAntes,
                'saldo_depois' => $saldoDepois,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function criarUsuario(User $admin, array $dados, string $motivo): User
    {
        return DB::transaction(function () use ($admin, $dados, $motivo) {
            $marketing = (bool) ($dados['marketing_opt_in'] ?? false);
            $bloqueado = (bool) ($dados['bloqueado'] ?? false);
            $emailVerificado = (bool) ($dados['email_verified'] ?? false);

            $usuario = User::create([
                'name' => trim((string) $dados['name']),
                'sobrenome' => trim((string) $dados['sobrenome']),
                'telefone' => $this->normalizarTelefone($dados['telefone'] ?? ''),
                'email' => Str::lower(trim((string) $dados['email'])),
                'password' => (string) $dados['password'],
                'credits' => (float) ($dados['credits'] ?? 0),
                'empresa' => $this->nullSeVazio($dados['empresa'] ?? null),
                'cargo' => $this->nullSeVazio($dados['cargo'] ?? null),
                'cnpj' => $this->nullSeVazio($dados['cnpj'] ?? null),
                'faturamento_anual' => $this->nullSeVazio($dados['faturamento_anual'] ?? null),
                'desafio_principal' => $this->nullSeVazio($dados['desafio_principal'] ?? null),
                'desafio_secundario' => $this->nullSeVazio($dados['desafio_secundario'] ?? null),
                'marketing_opt_in' => $marketing,
                'marketing_opt_in_at' => $marketing ? now() : null,
                'is_admin' => (bool) ($dados['is_admin'] ?? false),
                'bloqueado_em' => $bloqueado ? now() : null,
                // null força reaceite no primeiro acesso; admin nao aceita termos pelo titular.
                'terms_version' => null,
                'privacy_version' => null,
            ]);

            $this->registrar($admin, $usuario, 'usuario_criar', $motivo, [
                'email' => $usuario->email,
                'is_admin' => (bool) $usuario->is_admin,
                'bloqueado' => $usuario->bloqueado_em !== null,
            ]);

            return $usuario;
        });
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function atualizarUsuario(User $admin, User $alvo, array $dados, string $motivo): AdminActionLog
    {
        $tornarAdmin = (bool) ($dados['is_admin'] ?? false);
        $bloquear = (bool) ($dados['bloqueado'] ?? false);

        if (! $tornarAdmin && $alvo->is_admin) {
            $this->proibirAutoAlvo($admin, $alvo, 'rebaixar a própria conta');
        }

        if ($bloquear && $alvo->bloqueado_em === null) {
            $this->proibirAutoAlvo($admin, $alvo, 'bloquear a própria conta');
        }

        return DB::transaction(function () use ($admin, $alvo, $dados, $motivo, $tornarAdmin, $bloquear) {
            $camposAuditados = [
                'name', 'sobrenome', 'telefone', 'email', 'email_verified_at',
                'empresa', 'cargo', 'cnpj', 'faturamento_anual', 'desafio_principal',
                'desafio_secundario', 'marketing_opt_in', 'is_admin', 'bloqueado_em',
                'terms_version', 'privacy_version', 'alertas_operacionais',
                'alertas_monitoramento', 'resumo_periodico',
            ];
            $antes = $alvo->only($camposAuditados);
            $marketing = (bool) ($dados['marketing_opt_in'] ?? false);
            $emailVerificado = (bool) ($dados['email_verified'] ?? false);

            $payload = [
                'name' => trim((string) $dados['name']),
                'sobrenome' => trim((string) $dados['sobrenome']),
                'telefone' => $this->normalizarTelefone($dados['telefone'] ?? ''),
                'email' => Str::lower(trim((string) $dados['email'])),
                'email_verified_at' => $emailVerificado ? ($alvo->email_verified_at ?? now()) : null,
                'empresa' => $this->nullSeVazio($dados['empresa'] ?? null),
                'cargo' => $this->nullSeVazio($dados['cargo'] ?? null),
                'cnpj' => $this->nullSeVazio($dados['cnpj'] ?? null),
                'faturamento_anual' => $this->nullSeVazio($dados['faturamento_anual'] ?? null),
                'desafio_principal' => $this->nullSeVazio($dados['desafio_principal'] ?? null),
                'desafio_secundario' => $this->nullSeVazio($dados['desafio_secundario'] ?? null),
                'marketing_opt_in' => $marketing,
                'marketing_opt_in_at' => $marketing ? ($alvo->marketing_opt_in_at ?? now()) : null,
                'is_admin' => $tornarAdmin,
                'bloqueado_em' => $bloquear ? ($alvo->bloqueado_em ?? now()) : null,
                'alertas_operacionais' => (bool) ($dados['alertas_operacionais'] ?? false),
                'alertas_monitoramento' => (bool) ($dados['alertas_monitoramento'] ?? false),
                'resumo_periodico' => (bool) ($dados['resumo_periodico'] ?? false),
            ];

            if (! empty($dados['password'])) {
                $payload['password'] = (string) $dados['password'];
                $payload['remember_token'] = null;
            }

            if ((bool) ($dados['force_terms_reaccept'] ?? false)) {
                $payload['terms_accepted_at'] = null;
                $payload['terms_version'] = null;
                $payload['privacy_version'] = null;
            }

            $alvo->forceFill($payload)->save();
            $depois = $alvo->fresh()->only($camposAuditados);

            return $this->registrar($admin, $alvo, 'usuario_editar', $motivo, [
                'antes' => $antes,
                'depois' => $depois,
                'senha_alterada' => ! empty($dados['password']),
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function atualizarAssinatura(User $admin, User $alvo, ?SubscriptionPlan $plano, array $dados, string $motivo): AdminActionLog
    {
        return DB::transaction(function () use ($admin, $alvo, $plano, $dados, $motivo) {
            $assinatura = AccountSubscription::lockForUpdate()->where('user_id', $alvo->id)->first();
            $antes = $assinatura?->toArray();

            if ($plano === null) {
                $assinatura?->delete();

                return $this->registrar($admin, $alvo, 'assinatura_remover', $motivo, [
                    'antes' => $antes,
                    'depois' => null,
                ]);
            }

            $payload = [
                'subscription_plan_id' => $plano->id,
                'status' => (string) $dados['status'],
                'ciclo' => (string) $dados['ciclo'],
                'iniciada_em' => $this->nullSeVazio($dados['iniciada_em'] ?? null),
                'renova_em' => $this->nullSeVazio($dados['renova_em'] ?? null),
                'creditos_inclusos_saldo' => (int) ($dados['creditos_inclusos_saldo'] ?? 0),
                'limite_consumo_automatico' => $this->inteiroOuNull($dados['limite_consumo_automatico'] ?? null),
                'assentos_extras' => (int) ($dados['assentos_extras'] ?? 0),
                'mp_preapproval_id' => $this->nullSeVazio($dados['mp_preapproval_id'] ?? null),
                'proximo_grant_em' => $this->nullSeVazio($dados['proximo_grant_em'] ?? null),
                'ultimo_grant_em' => $this->nullSeVazio($dados['ultimo_grant_em'] ?? null),
                'proration_pendente' => null,
            ];

            if ($payload['status'] === AccountSubscription::STATUS_ATIVA && $payload['iniciada_em'] === null) {
                $payload['iniciada_em'] = now();
            }

            $assinatura = AccountSubscription::updateOrCreate(['user_id' => $alvo->id], $payload);

            return $this->registrar($admin, $alvo, 'assinatura_editar', $motivo, [
                'antes' => $antes,
                'depois' => $assinatura->fresh()->toArray(),
                'plano_codigo' => $plano->codigo,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function atualizarTrial(User $admin, User $alvo, array $dados, string $motivo): AdminActionLog
    {
        return DB::transaction(function () use ($admin, $alvo, $dados, $motivo) {
            $campos = [
                'trial_used', 'trial_started_at', 'trial_expires_at', 'trial_credits_granted',
                'trial_credits_remaining', 'trial_credits_expired', 'trial_source',
            ];
            $antes = $alvo->only($campos);

            $alvo->forceFill([
                'trial_used' => (bool) ($dados['trial_used'] ?? false),
                'trial_started_at' => $this->nullSeVazio($dados['trial_started_at'] ?? null),
                'trial_expires_at' => $this->nullSeVazio($dados['trial_expires_at'] ?? null),
                'trial_credits_granted' => (int) ($dados['trial_credits_granted'] ?? 0),
                'trial_credits_remaining' => (int) ($dados['trial_credits_remaining'] ?? 0),
                'trial_credits_expired' => (int) ($dados['trial_credits_expired'] ?? 0),
                'trial_source' => $this->nullSeVazio($dados['trial_source'] ?? null),
            ])->save();

            return $this->registrar($admin, $alvo, 'trial_editar', $motivo, [
                'antes' => $antes,
                'depois' => $alvo->fresh()->only($campos),
            ]);
        });
    }

    public function anonimizarUsuario(User $admin, User $alvo, string $motivo): AdminActionLog
    {
        $this->proibirAutoAlvo($admin, $alvo, 'anonimizar a própria conta');

        if ($alvo->is_admin) {
            throw new \InvalidArgumentException('Remova a permissão admin antes de anonimizar esta conta.');
        }

        if ($alvo->anonimizado_em !== null) {
            throw new \InvalidArgumentException('Esta conta já foi anonimizada.');
        }

        return DB::transaction(function () use ($admin, $alvo, $motivo) {
            $antes = $alvo->only([
                'name', 'sobrenome', 'email', 'telefone', 'empresa', 'cargo', 'cnpj',
                'faturamento_anual', 'desafio_principal', 'desafio_secundario',
                'marketing_opt_in', 'deletion_requested_at', 'anonimizado_em', 'bloqueado_em',
            ]);

            $alvo->forceFill([
                'name' => 'Titular',
                'sobrenome' => 'anonimizado',
                'email' => 'anon-'.$alvo->id.'@anonimizado.invalid',
                'telefone' => '',
                'empresa' => null,
                'cargo' => null,
                'cnpj' => null,
                'faturamento_anual' => null,
                'desafio_principal' => null,
                'desafio_secundario' => null,
                'marketing_opt_in' => false,
                'marketing_opt_in_at' => null,
                'password' => Str::random(48),
                'remember_token' => null,
                'bloqueado_em' => $alvo->bloqueado_em ?? now(),
                'deletion_requested_at' => $alvo->deletion_requested_at ?? now(),
                'anonimizado_em' => now(),
            ])->save();

            AccountSubscription::where('user_id', $alvo->id)
                ->update(['status' => AccountSubscription::STATUS_CANCELADA]);

            return $this->registrar($admin, $alvo, 'usuario_anonimizar', $motivo, [
                'antes' => $antes,
                'depois' => $alvo->fresh()->only(array_keys($antes)),
            ]);
        });
    }

    private function proibirAutoAlvo(User $admin, User $alvo, string $oQue): void
    {
        if ($admin->id === $alvo->id) {
            throw new \InvalidArgumentException("Operador não pode {$oQue}.");
        }
    }

    private function normalizarTelefone(?string $valor): string
    {
        return substr(preg_replace('/\D/', '', (string) $valor), 0, 20);
    }

    private function nullSeVazio(mixed $valor): mixed
    {
        if ($valor === null) {
            return null;
        }

        $valor = is_string($valor) ? trim($valor) : $valor;

        return $valor === '' ? null : $valor;
    }

    private function inteiroOuNull(mixed $valor): ?int
    {
        $valor = $this->nullSeVazio($valor);

        return $valor === null ? null : (int) $valor;
    }
}
