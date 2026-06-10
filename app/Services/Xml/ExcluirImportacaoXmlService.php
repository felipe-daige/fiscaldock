<?php

namespace App\Services\Xml;

use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\XmlImportacao;
use App\Models\XmlNota;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Exclusão de importação XML + derivados, espelhando ExcluirImportacaoService (EFD).
 *
 * Diferença chave: `xml_notas.importacao_xml_id → xml_importacoes` é SET NULL (não CASCADE),
 * então as NOTAS são apagadas explicitamente (itens cascateiam por `xml_notas_itens.xml_nota_id`).
 */
class ExcluirImportacaoXmlService
{
    /**
     * Contagens de impacto para a prévia do modal.
     */
    public function preview(XmlImportacao $imp): array
    {
        $notaIds = XmlNota::where('importacao_xml_id', $imp->id)->pluck('id');

        $candidatos = $this->participantesCandidatos($imp);
        $orfaos = $this->participantesOrfaos($imp, $candidatos);

        return [
            'notas' => $notaIds->count(),
            'itens' => DB::table('xml_notas_itens')->whereIn('xml_nota_id', $notaIds)->count(),
            'participantes' => [
                'candidatos' => $candidatos->count(),
                'orfaos' => $orfaos->count(),
                'compartilhados' => $candidatos->count() - $orfaos->count(),
            ],
        ];
    }

    /**
     * Exclui a importação e os dados derivados, dentro de uma transação.
     *
     * @return array{participantes_excluidos:int, participantes_preservados:int}
     */
    public function execute(XmlImportacao $imp, bool $excluirParticipantes): array
    {
        return DB::transaction(function () use ($imp, $excluirParticipantes) {
            $candidatos = $this->participantesCandidatos($imp);
            $orfaos = $this->participantesOrfaos($imp, $candidatos);

            // Órfãos sem dado pago: apagados via FK cascade do banco (consultas, scores, pivôs).
            $excluidos = 0;
            if ($excluirParticipantes && $orfaos->isNotEmpty()) {
                $excluidos = Participante::whereIn('id', $orfaos)->delete();
            }

            // SET NULL no FK só desvincula — as notas têm de ser apagadas (itens cascateiam).
            XmlNota::where('importacao_xml_id', $imp->id)->delete();

            // participantes.importacao_xml_id é SET NULL no FK → desvincula sozinho ao deletar a importação.
            $imp->delete();

            return [
                'participantes_excluidos' => $excluidos,
                'participantes_preservados' => $candidatos->count() - $excluidos,
            ];
        });
    }

    /**
     * Participantes citados pelas notas desta importação (emit/dest) ∪ criados por ela.
     *
     * @return Collection<int,int>
     */
    protected function participantesCandidatos(XmlImportacao $imp): Collection
    {
        $viaNotas = XmlNota::where('importacao_xml_id', $imp->id)
            ->get(['emit_participante_id', 'dest_participante_id'])
            ->flatMap(fn ($n) => [$n->emit_participante_id, $n->dest_participante_id])
            ->filter()
            ->unique()
            ->values();

        $viaImportacao = Participante::where('importacao_xml_id', $imp->id)->pluck('id');

        return $viaNotas->merge($viaImportacao)->unique()->values();
    }

    /**
     * Dos candidatos, os que NÃO são referenciados por outra importação XML nem por efd_notas
     * e que não têm dado pago/derivado (consulta/score/monitoramento) — ver temDadosPagos().
     *
     * @param  Collection<int,int>  $candidatos
     * @return Collection<int,int>
     */
    protected function participantesOrfaos(XmlImportacao $imp, Collection $candidatos): Collection
    {
        return $candidatos->filter(function (int $pid) use ($imp) {
            $emOutraXml = XmlNota::where('importacao_xml_id', '!=', $imp->id)
                ->where(fn ($q) => $q->where('emit_participante_id', $pid)->orWhere('dest_participante_id', $pid))
                ->exists();

            $emEfd = EfdNota::where('participante_id', $pid)->exists();

            return ! $emOutraXml && ! $emEfd && ! $this->temDadosPagos($pid);
        })->values();
    }

    /**
     * Participante com dado pago/derivado que NÃO deve ser destruído junto da importação:
     * consultas avulsas, monitoramento e score persistido. Preservados como compartilhados.
     */
    protected function temDadosPagos(int $pid): bool
    {
        return DB::table('consulta_resultados')->where('participante_id', $pid)->exists()
            || DB::table('monitoramento_consultas')->where('participante_id', $pid)->exists()
            || DB::table('monitoramento_assinaturas')->where('participante_id', $pid)->exists()
            || DB::table('participante_scores')->where('participante_id', $pid)->exists();
    }
}
