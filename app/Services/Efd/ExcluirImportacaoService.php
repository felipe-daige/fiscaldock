<?php

namespace App\Services\Efd;

use App\Models\EfdImportacao;
use App\Models\EfdNota;
use App\Models\Participante;
use App\Models\XmlNota;
use Illuminate\Support\Facades\DB;

class ExcluirImportacaoService
{
    /**
     * Contagens de impacto para a prévia do modal.
     */
    public function preview(EfdImportacao $imp): array
    {
        $notaIds = EfdNota::where('importacao_id', $imp->id)->pluck('id');

        $candidatos = $this->participantesCandidatos($imp);
        $orfaos = $this->participantesOrfaos($imp, $candidatos);

        return [
            'notas' => $notaIds->count(),
            'itens' => DB::table('efd_notas_itens')->whereIn('efd_nota_id', $notaIds)->count(),
            'catalogo' => DB::table('efd_catalogo_itens')->where('importacao_id', $imp->id)->count(),
            'apuracoes' => DB::table('efd_apuracoes_contribuicoes')->where('importacao_id', $imp->id)->count()
                + DB::table('efd_apuracoes_icms')->where('importacao_id', $imp->id)->count(),
            'retencoes' => DB::table('efd_retencoes_fonte')->where('importacao_id', $imp->id)->count(),
            'divergencias' => DB::table('efd_divergencias')->where('importacao_id', $imp->id)->count(),
            'catalogo_historico' => DB::table('efd_catalogo_historico')->where('importacao_id', $imp->id)->count(),
            'participantes' => [
                'candidatos' => $candidatos->count(),
                'orfaos' => $orfaos->count(),
                'compartilhados' => $candidatos->count() - $orfaos->count(),
            ],
        ];
    }

    /**
     * Exclui a importação e todos os dados derivados, dentro de uma transação.
     * O cascade de FK (importacao_id) cuida de notas/itens/catálogo/apurações/retenções/divergências.
     *
     * @return array{participantes_excluidos:int, participantes_preservados:int}
     */
    public function execute(EfdImportacao $imp, bool $excluirParticipantes): array
    {
        return DB::transaction(function () use ($imp, $excluirParticipantes) {
            $candidatos = $this->participantesCandidatos($imp);
            $orfaos = $this->participantesOrfaos($imp, $candidatos);

            // Sem cascade: histórico do catálogo (importacao_id é nullOnDelete).
            DB::table('efd_catalogo_historico')->where('importacao_id', $imp->id)->delete();

            // Órfãos são apagados via FK cascade do banco (assinaturas, consultas, scores, pivot
            // grupos, consulta_resultados; notas SET NULL). O Participante não tem evento de model
            // no delete, então um único whereIn é suficiente e evita N+1.
            $excluidos = 0;
            if ($excluirParticipantes && $orfaos->isNotEmpty()) {
                $excluidos = Participante::whereIn('id', $orfaos)->delete();
            }

            // participantes.importacao_efd_id NÃO tem FK → zerar nos sobreviventes p/ não ficar dangling.
            Participante::where('importacao_efd_id', $imp->id)->update(['importacao_efd_id' => null]);

            // Dispara o cascade de todos os derivados via FK importacao_id.
            $imp->delete();

            return [
                'participantes_excluidos' => $excluidos,
                'participantes_preservados' => $candidatos->count() - $excluidos,
            ];
        });
    }

    /**
     * Participantes citados pelas notas desta importação ∪ criados por ela.
     *
     * @return \Illuminate\Support\Collection<int,int>
     */
    protected function participantesCandidatos(EfdImportacao $imp): \Illuminate\Support\Collection
    {
        $viaNotas = EfdNota::where('importacao_id', $imp->id)
            ->whereNotNull('participante_id')
            ->pluck('participante_id');

        $viaImportacao = Participante::where('importacao_efd_id', $imp->id)->pluck('id');

        return $viaNotas->merge($viaImportacao)->unique()->values();
    }

    /**
     * Dos candidatos, os que NÃO são referenciados por outra importação nem por xml_notas
     * e que não têm dado pago/derivado (consulta/score/monitoramento) — ver temDadosPagos().
     *
     * @param  \Illuminate\Support\Collection<int,int>  $candidatos
     * @return \Illuminate\Support\Collection<int,int>
     */
    protected function participantesOrfaos(EfdImportacao $imp, \Illuminate\Support\Collection $candidatos): \Illuminate\Support\Collection
    {
        return $candidatos->filter(function (int $pid) use ($imp) {
            $emOutraEfd = EfdNota::where('participante_id', $pid)
                ->where('importacao_id', '!=', $imp->id)
                ->exists();

            $emXml = XmlNota::where('emit_participante_id', $pid)
                ->orWhere('dest_participante_id', $pid)
                ->exists();

            return ! $emOutraEfd && ! $emXml && ! $this->temDadosPagos($pid);
        })->values();
    }

    /**
     * Participante com dado pago/derivado que NÃO deve ser destruído junto da importação:
     * consultas avulsas (consulta_resultados), monitoramento (assinaturas/consultas) e score
     * persistido. Esses participantes são tratados como compartilhados e preservados — excluí-los
     * cascatearia (FK) e apagaria histórico de consulta que o usuário pagou.
     */
    protected function temDadosPagos(int $pid): bool
    {
        return DB::table('consulta_resultados')->where('participante_id', $pid)->exists()
            || DB::table('monitoramento_consultas')->where('participante_id', $pid)->exists()
            || DB::table('monitoramento_assinaturas')->where('participante_id', $pid)->exists()
            || DB::table('participante_scores')->where('participante_id', $pid)->exists();
    }
}
