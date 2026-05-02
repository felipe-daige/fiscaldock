<?php

namespace App\Services\Catalogo;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Compara itens declarados em uma nota (XML ou EFD) contra o catálogo
 * (`efd_catalogo_itens`, registro 0200). Devolve, por `codigo_item`, o
 * snapshot do catálogo + as divergências encontradas.
 *
 * Diferença vs `AlertasCatalogoService`: este é por nota individual (um
 * objeto por linha de item visível na tela), não agregado por usuário.
 *
 * Tolerância de alíquota e regra de "versão mais recente do catálogo"
 * espelham as do `AlertasCatalogoService` — ambos serviços precisam
 * concordar para a UI ficar consistente entre `/app/clearance/alertas`
 * e o detalhe da nota.
 */
final class CatalogoComparacaoService
{
    public const DIVERGENCIA_NCM = 'ncm';

    public const DIVERGENCIA_UNIDADE = 'unidade';

    public const DIVERGENCIA_ALIQUOTA = 'aliquota';

    /**
     * @param  Collection<int, object>  $itens  cada item precisa ter codigo_item, ncm, unidade_medida, aliquota_icms
     * @return array<string, array{cod_item: string, descr_item: ?string, cod_ncm: ?string, unid_inv: ?string, aliq_icms: ?float}>
     */
    public function indexarPorCodigos(int $userId, Collection $itens): array
    {
        $codigos = $itens
            ->pluck('codigo_item')
            ->filter()
            ->map(fn ($c) => (string) $c)
            ->unique()
            ->values()
            ->all();

        if ($codigos === []) {
            return [];
        }

        $linhas = DB::table('efd_catalogo_itens')
            ->where('user_id', $userId)
            ->whereIn('cod_item', $codigos)
            ->orderByDesc('id')
            ->get(['cod_item', 'descr_item', 'cod_ncm', 'aliq_icms', 'unid_inv']);

        $indexado = [];
        foreach ($linhas as $linha) {
            $codigo = (string) $linha->cod_item;
            // primeiro vence (ORDER BY id DESC = versão mais recente)
            if (! isset($indexado[$codigo])) {
                $indexado[$codigo] = [
                    'cod_item' => $codigo,
                    'descr_item' => $linha->descr_item,
                    'cod_ncm' => $linha->cod_ncm,
                    'unid_inv' => $linha->unid_inv,
                    'aliq_icms' => $linha->aliq_icms !== null ? (float) $linha->aliq_icms : null,
                ];
            }
        }

        return $indexado;
    }

    /**
     * Compara um item declarado contra o catálogo (versão mais recente do mesmo cod_item).
     *
     * @param  object  $item  precisa ter codigo_item, ncm, unidade_medida, aliquota_icms
     * @param  array<string, array<string, mixed>>  $catalogoIndexado  saída de indexarPorCodigos
     * @return array{cadastro: ?array<string, mixed>, divergencias: array<int, string>}
     */
    public function comparar(object $item, array $catalogoIndexado): array
    {
        $codigo = (string) ($item->codigo_item ?? '');
        $cadastro = $catalogoIndexado[$codigo] ?? null;

        if ($cadastro === null) {
            return ['cadastro' => null, 'divergencias' => []];
        }

        $divergencias = [];

        // NCM: só compara quando ambos os lados têm valor
        $ncmDeclarado = $item->ncm ?? null;
        if ($cadastro['cod_ncm'] && $ncmDeclarado && (string) $ncmDeclarado !== (string) $cadastro['cod_ncm']) {
            $divergencias[] = self::DIVERGENCIA_NCM;
        }

        // Unidade: idem
        $unidadeDeclarada = $item->unidade_medida ?? null;
        if ($cadastro['unid_inv'] && $unidadeDeclarada && (string) $unidadeDeclarada !== (string) $cadastro['unid_inv']) {
            $divergencias[] = self::DIVERGENCIA_UNIDADE;
        }

        // Alíquota: tolerância 0,5pp (mesma do AlertasCatalogoService)
        $aliqDeclarada = $item->aliquota_icms ?? null;
        if ($cadastro['aliq_icms'] !== null && $aliqDeclarada !== null) {
            if (abs((float) $cadastro['aliq_icms'] - (float) $aliqDeclarada) > AlertasCatalogoService::TOLERANCIA_ALIQUOTA_PP) {
                $divergencias[] = self::DIVERGENCIA_ALIQUOTA;
            }
        }

        return ['cadastro' => $cadastro, 'divergencias' => $divergencias];
    }

    /**
     * Conveniência: indexa a coleção de itens já com cadastro + divergências resolvidos.
     * Cada chave é o id do item (precisa ter $item->id).
     *
     * @param  Collection<int, object>  $itens
     * @return array<int, array{cadastro: ?array<string, mixed>, divergencias: array<int, string>}>
     */
    public function indexarComparacaoPorItem(int $userId, Collection $itens): array
    {
        $catalogo = $this->indexarPorCodigos($userId, $itens);

        $resultado = [];
        foreach ($itens as $item) {
            if (! isset($item->id)) {
                continue;
            }
            $resultado[(int) $item->id] = $this->comparar($item, $catalogo);
        }

        return $resultado;
    }
}
