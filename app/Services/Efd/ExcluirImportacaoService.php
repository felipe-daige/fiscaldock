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
     * Dos candidatos, os que NÃO são referenciados por outra importação nem por xml_notas.
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

            return ! $emOutraEfd && ! $emXml;
        })->values();
    }
}
