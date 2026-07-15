<?php

namespace App\Services\Clientes;

use App\Models\Cliente;
use App\Models\ConsultaResultado;
use App\Models\Participante;
use App\Services\BiService;
use App\Services\Participantes\DossieParticipanteBuilder;

/**
 * Monta o payload do dossiê em lote: para cada cliente selecionado, o dossiê do
 * cliente seguido dos dossiês dos seus participantes com conteúdo (movimentação
 * EFD e/ou consulta de certidões). Sem efeitos colaterais — tudo derivado dos
 * builders existentes, que são read-only.
 */
final class DossieLoteBuilder
{
    /**
     * Teto duro de itens (clientes + participantes) no PDF — mesmo racional do
     * BiDossieAnexoService: evita PDF/timeout runaway no render síncrono do dompdf.
     */
    public const TETO_ITENS = 50;

    /** Opções válidas de participantes por cliente (top N por volume EFD, critério do BI). */
    public const TOPS_VALIDOS = [10, 20, 50];

    public function __construct(
        private DossieClienteBuilder $dossieCliente,
        private DossieParticipanteBuilder $dossieParticipante,
        private BiService $bi,
    ) {}

    /**
     * @param  array<int, int>  $clienteIds
     * @param  int  $top  Participantes por cliente (top N por volume EFD). Ver TOPS_VALIDOS.
     * @return array{grupos: list<array{dossie: array, participantes: list<array>, participantes_total: int}>, truncado: bool, top: int}|null
     *                                                                                                                                        null = nenhum cliente válido do usuário entre os ids.
     */
    public function montar(int $userId, array $clienteIds, int $top = 10): ?array
    {
        if (! in_array($top, self::TOPS_VALIDOS, true)) {
            $top = 10;
        }

        $clientes = Cliente::where('user_id', $userId)
            ->whereIn('id', $clienteIds)
            ->orderByRaw("COALESCE(razao_social, nome_fantasia, documento, '') asc")
            ->get();

        if ($clientes->isEmpty()) {
            return null;
        }

        $restantes = self::TETO_ITENS;
        $truncado = false;
        $grupos = [];

        foreach ($clientes as $cliente) {
            if ($restantes <= 0) {
                $truncado = true;
                break;
            }

            $restantes--; // o próprio cliente ocupa 1 item do teto

            $participantes = $this->participantesComConteudo($userId, $cliente->id);
            $incluidos = $participantes->take(min($top, max(0, $restantes)));
            $restantes -= $incluidos->count();
            // Truncado só quando o TETO global corta abaixo do top escolhido —
            // ficar no top N é escolha do usuário, não truncamento.
            if ($participantes->count() > $incluidos->count() && $incluidos->count() < $top) {
                $truncado = true;
            }

            $grupos[] = [
                'dossie' => $this->dossieCliente->montar($cliente),
                'participantes' => $incluidos
                    ->map(fn (Participante $p) => $this->dossieParticipante->montar($p))
                    ->values()->all(),
                'participantes_total' => $participantes->count(),
            ];
        }

        return ['grupos' => $grupos, 'truncado' => $truncado, 'top' => $top];
    }

    /**
     * Participantes do cliente que rendem dossiê: com movimentação EFD (ordenados
     * por volume desc, critério do BI) e/ou com consulta de certidões bem-sucedida
     * (anexados ao fim, em ordem alfabética). Sem conteúdo algum → fora do PDF.
     *
     * @return \Illuminate\Support\Collection<int, Participante>
     */
    private function participantesComConteudo(int $userId, int $clienteId): \Illuminate\Support\Collection
    {
        $porVolume = $this->bi->participantesPorVolume($userId, $clienteId);
        $idsVolume = $porVolume->pluck('id')->all();

        $soComConsulta = Participante::where('user_id', $userId)
            ->where('cliente_id', $clienteId)
            ->whereNotIn('id', $idsVolume)
            ->whereIn('id', ConsultaResultado::select('participante_id')
                ->whereNotNull('participante_id')
                ->where('status', ConsultaResultado::STATUS_SUCESSO))
            ->orderByRaw("COALESCE(razao_social, nome_fantasia, documento, '') asc")
            ->get();

        return $porVolume->concat($soComConsulta)->values();
    }
}
