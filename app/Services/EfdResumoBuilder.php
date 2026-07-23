<?php

namespace App\Services;

use App\Models\EfdImportacao;
use App\Support\Efd\ModeloDocumento;
use Illuminate\Support\Facades\DB;

class EfdResumoBuilder
{
    public function build(EfdImportacao $importacao): array
    {
        $impId = $importacao->id;
        $userId = $importacao->user_id;

        $notas = $this->contarNotas($impId);
        $participantes = $this->contarParticipantes($impId);
        $catalogoTotal = (int) DB::table('efd_catalogo_itens')
            ->where('importacao_id', $impId)->count();

        $apuracaoIcms = $this->blocoApuracaoIcms($impId);
        $apuracaoPisCofins = $this->blocoApuracaoPisCofins($impId);
        $retencoes = $this->blocoRetencoes($impId);

        $totalRegulares = $notas['mercadorias']['total'] + $notas['consumidor']['total'] + $notas['transportes']['total'] + $notas['servicos']['total'];
        $totalValor = $notas['mercadorias']['valor'] + $notas['consumidor']['valor'] + $notas['transportes']['valor'] + $notas['servicos']['valor'];
        $totalNotas = $totalRegulares + $notas['canceladas'];

        $estatisticas = [
            'total_cnpjs_unicos' => $participantes['cnpjs'],
            'total_cpfs_unicos' => $participantes['cpfs'],
            'participantes_novos' => $participantes['novos'],
            'participantes_repetidos' => max(0, $participantes['total'] - $participantes['novos']),
            'total_participantes_processados' => $participantes['total'],
            'notas_novas' => $totalRegulares,
            'notas_duplicadas' => 0,
            'total_notas_processadas' => $totalNotas,
            'notas_canceladas' => $notas['canceladas'],
        ];

        $blocos = [
            'notas_servicos' => [
                'total_notas' => $notas['servicos']['total'],
                'valor_total' => $notas['servicos']['valor'],
            ],
            'notas_mercadorias' => [
                'total_notas' => $notas['mercadorias']['total'],
                'valor_total' => $notas['mercadorias']['valor'],
            ],
            'notas_consumidor' => [
                'total_notas' => $notas['consumidor']['total'],
                'valor_total' => $notas['consumidor']['valor'],
            ],
            'notas_transportes' => [
                'total_notas' => $notas['transportes']['total'],
                'valor_total' => $notas['transportes']['valor'],
            ],
            'catalogo' => ['total_itens' => $catalogoTotal],
        ];

        if ($apuracaoIcms) {
            $blocos['apuracao_icms'] = $apuracaoIcms;
        }
        if ($apuracaoPisCofins) {
            $blocos['apuracao_pis_cofins'] = $apuracaoPisCofins;
        }
        if ($retencoes) {
            $blocos['retencoes_fonte'] = $retencoes;
        }

        return [
            'user_id' => $userId,
            'cliente_id' => $importacao->cliente_id,
            'importacao_id' => $impId,
            'tipo_sped' => $importacao->tipo_efd,
            'participante_ids' => $participantes['ids'],
            'estatisticas' => $estatisticas,
            'blocos' => $blocos,
            'totais' => [
                'notas' => $totalNotas,
                'valor' => $totalValor,
            ],
            'mensagem' => $this->montarMensagem($notas, $participantes),
        ];
    }

    private function contarNotas(int $impId): array
    {
        $rows = DB::table('efd_notas')
            ->where('importacao_id', $impId)
            ->selectRaw('
                modelo,
                SUM(CASE WHEN cancelada = false THEN 1 ELSE 0 END) AS regulares,
                SUM(CASE WHEN cancelada = false THEN valor_total ELSE 0 END) AS valor,
                SUM(CASE WHEN cancelada = true  THEN 1 ELSE 0 END) AS canceladas
            ')
            ->groupBy('modelo')
            ->get()
            ->keyBy('modelo');

        // Bucket canônico (ModeloDocumento) de 4 vias: consumidor (NFC-e 65) separado de
        // mercadoria (NF-e 55 + avulsas), transporte (57/67), serviço (00). NFC-e tem bucket
        // próprio porque o mix B2B×varejo é informação fiscal (UTIDA fatura R$ 180k em 65 e
        // R$ 7 em 55 — juntar esconde). Fonte única — o strip de progresso e o BI leem o mesmo.
        $buckets = [
            'notas_mercadorias' => ['total' => 0, 'valor' => 0.0],
            'notas_consumidor' => ['total' => 0, 'valor' => 0.0],
            'notas_transportes' => ['total' => 0, 'valor' => 0.0],
            'notas_servicos' => ['total' => 0, 'valor' => 0.0],
        ];
        foreach ($rows as $modelo => $r) {
            $b = ModeloDocumento::bucket((string) $modelo);
            $buckets[$b]['total'] += (int) $r->regulares;
            $buckets[$b]['valor'] += (float) $r->valor;
        }

        return [
            'mercadorias' => $buckets['notas_mercadorias'],
            'consumidor' => $buckets['notas_consumidor'],
            'transportes' => $buckets['notas_transportes'],
            'servicos' => $buckets['notas_servicos'],
            'canceladas' => (int) $rows->sum('canceladas'),
        ];
    }

    /**
     * total/cnpjs/cpfs/ids = participantes REFERENCIADOS pelas notas desta importação
     * (movimentados). É o número estável e independente da ordem de importação — bate com
     * o que o arquivo declara no 0150. `novos` = participantes que ESTA importação inseriu
     * (`importacao_efd_id`); quando o CNPJ já existia de outra importação, conta como
     * "repetido", não "novo" (a dedup de participantes é por user_id+documento, não por
     * importação). Ver [[project_efd_nota_origem_arquivo_dedup]] e a auditoria de 2026-06-01.
     */
    private function contarParticipantes(int $impId): array
    {
        $referenciadosIds = DB::table('efd_notas')
            ->where('importacao_id', $impId)
            ->whereNotNull('participante_id')
            ->distinct()
            ->pluck('participante_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $docs = DB::table('participantes')
            ->whereIn('id', $referenciadosIds)
            ->pluck('documento');

        $cnpjs = $docs->filter(fn ($d) => strlen((string) $d) === 14)->unique()->count();
        $cpfs = $docs->filter(fn ($d) => strlen((string) $d) === 11)->unique()->count();

        $novos = (int) DB::table('participantes')
            ->where('importacao_efd_id', $impId)
            ->count();

        return [
            'total' => count($referenciadosIds),
            'novos' => $novos,
            'cnpjs' => $cnpjs,
            'cpfs' => $cpfs,
            'ids' => $referenciadosIds,
        ];
    }

    private function blocoApuracaoIcms(int $impId): ?array
    {
        $row = DB::table('efd_apuracoes_icms')->where('importacao_id', $impId)->first();
        if (! $row) {
            return null;
        }

        $valor = (float) ($row->icms_a_recolher ?? 0) + (float) ($row->st_icms_recolher ?? 0);

        return [
            'total_notas' => 1,
            'valor_total' => $valor,
            'label_count' => 'apuração',
        ];
    }

    private function blocoApuracaoPisCofins(int $impId): ?array
    {
        $row = DB::table('efd_apuracoes_contribuicoes')->where('importacao_id', $impId)->first();
        if (! $row) {
            return null;
        }

        $valor = (float) ($row->pis_total_recolher ?? 0) + (float) ($row->cofins_total_recolher ?? 0);

        return [
            'total_notas' => 1,
            'valor_total' => $valor,
            'label_count' => 'apuração',
        ];
    }

    private function blocoRetencoes(int $impId): ?array
    {
        $row = DB::table('efd_retencoes_fonte')
            ->where('importacao_id', $impId)
            ->selectRaw('COUNT(*) AS qtd, COALESCE(SUM(valor_total),0) AS valor')
            ->first();

        if (! $row || (int) $row->qtd === 0) {
            return null;
        }

        return [
            'total_notas' => (int) $row->qtd,
            'valor_total' => (float) $row->valor,
        ];
    }

    private function montarMensagem(array $notas, array $participantes): string
    {
        $parts = [];
        if ($participantes['total'] > 0) {
            $parts[] = $participantes['total'].' participantes';
        }
        if ($notas['mercadorias']['total'] > 0) {
            $parts[] = $notas['mercadorias']['total'].' NF-e';
        }
        if ($notas['consumidor']['total'] > 0) {
            $parts[] = $notas['consumidor']['total'].' NFC-e';
        }
        if ($notas['transportes']['total'] > 0) {
            $parts[] = $notas['transportes']['total'].' CT-e';
        }
        if ($notas['servicos']['total'] > 0) {
            $parts[] = $notas['servicos']['total'].' NFS-e';
        }
        if ($notas['canceladas'] > 0) {
            $parts[] = $notas['canceladas'].' canceladas';
        }

        return $parts ? 'Importação concluída: '.implode(', ', $parts).'.' : 'Importação concluída sem dados.';
    }
}
