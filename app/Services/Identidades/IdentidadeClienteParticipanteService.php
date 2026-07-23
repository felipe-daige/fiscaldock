<?php

namespace App\Services\Identidades;

use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class IdentidadeClienteParticipanteService
{
    public function instalarEConsolidar(): void
    {
        $arquivo = database_path('sql/identidade_cliente_participante.sql');
        $sql = file_get_contents($arquivo);

        if ($sql === false) {
            throw new RuntimeException("Não foi possível ler {$arquivo}.");
        }

        DB::transaction(static function () use ($sql): void {
            DB::unprepared($sql);
        });
    }

    public function clienteDoDocumento(int $userId, ?string $documento): ?Cliente
    {
        $documento = $this->normalizar($documento);
        if ($documento === '') {
            return null;
        }

        return Cliente::query()
            ->where('user_id', $userId)
            ->whereRaw(
                "regexp_replace(coalesce(documento, ''), '[^0-9]', '', 'g') = ?",
                [$documento]
            )
            ->first();
    }

    public function documentoPertenceACliente(int $userId, ?string $documento): bool
    {
        return $this->clienteDoDocumento($userId, $documento) !== null;
    }

    /**
     * Conta só DUPLICATA DE CADASTRO: participante que casa um cliente por documento E não tem
     * nenhuma nota vinculada. Participante COM nota é movimento legítimo (dois clientes do mesmo
     * usuário que negociam entre si) e coexiste com o cliente — mesma regra do trigger de
     * consolidação. Sem esse filtro o comando `identidades:consolidar-cliente-participante`
     * nunca convergiria para zero.
     */
    public function totalDuplicidades(): int
    {
        return DB::table('participantes as p')
            ->join('clientes as c', function ($join) {
                $join->on('c.user_id', '=', 'p.user_id')
                    ->whereRaw(
                        "regexp_replace(coalesce(c.documento, ''), '[^0-9]', '', 'g')"
                        ." = regexp_replace(coalesce(p.documento, ''), '[^0-9]', '', 'g')"
                    );
            })
            ->whereRaw("regexp_replace(coalesce(p.documento, ''), '[^0-9]', '', 'g') <> ''")
            ->whereNotExists(fn ($q) => $q->select(DB::raw('1'))
                ->from('efd_notas')->whereColumn('efd_notas.participante_id', 'p.id'))
            ->whereNotExists(fn ($q) => $q->select(DB::raw('1'))
                ->from('xml_notas')->whereColumn('xml_notas.emit_participante_id', 'p.id'))
            ->whereNotExists(fn ($q) => $q->select(DB::raw('1'))
                ->from('xml_notas')->whereColumn('xml_notas.dest_participante_id', 'p.id'))
            ->count();
    }

    private function normalizar(?string $documento): string
    {
        return preg_replace('/\D/', '', (string) $documento);
    }
}
