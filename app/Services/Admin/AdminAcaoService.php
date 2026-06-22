<?php

namespace App\Services\Admin;

use App\Models\AdminActionLog;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Support\Facades\DB;

class AdminAcaoService
{
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

    private function proibirAutoAlvo(User $admin, User $alvo, string $oQue): void
    {
        if ($admin->id === $alvo->id) {
            throw new \InvalidArgumentException("Operador não pode {$oQue}.");
        }
    }
}
