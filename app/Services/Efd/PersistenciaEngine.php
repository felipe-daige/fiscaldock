<?php

namespace App\Services\Efd;

use App\Models\EfdImportacao;
use App\Services\Efd\Driver\SpedDriver;
use App\Services\Efd\Handlers\HandlerAgregador;
use App\Services\Efd\Handlers\SpedRegistroHandler;
use App\Support\Efd\ModeloDocumento;
use Illuminate\Support\Facades\DB;

/**
 * Persiste os registros de uma importação nas tabelas reais (schema NÃO muda) — L3,
 * 100% compartilhado entre EFD ICMS/IPI e PIS/COFINS. §L3/§10.5.
 *
 * STREAMING em 3 passadas sobre o arquivo (o parser é um generator; re-caminhar custa
 * um tokenize barato, mas evita segurar TODAS as linhas na memória — um SPED de 10 MB
 * acumulado estourava o worker). Cada passada mantém só buffers de tamanho CHUNK + mapas
 * limitados (participantes/link), nunca os ~50k itens/consolidados de uma vez:
 *  1) participantes + catálogo (limitados: contrapartes/SKUs) + agregadores + COD_PART→doc;
 *  2) notas (com participante_id carimbado no insert) + retenções, em chunks;
 *  3) itens + consolidados, ligados via o mapa de linkagem relido, em chunks.
 * Tudo numa transação por importação, idempotente (reimportar não duplica).
 */
class PersistenciaEngine
{
    private const CHUNK = 1000;

    /**
     * @param  callable(): iterable<array{0: SpedRecord, 1: ?Contexto}>  $paresFactory
     *                                                                                  produz um stream FRESCO (parser+walker) a cada chamada — chamado 3×.
     * @return array<string,int> contagem persistida por bucket
     */
    public function executar(EfdImportacao $imp, SpedDriver $driver, callable $paresFactory, ?ProgressoEmitter $progresso = null): array
    {
        $handlers = $this->indexarHandlers($driver);

        return DB::transaction(function () use ($imp, $driver, $handlers, $paresFactory, $progresso) {
            $stats = [
                'participantes' => 0, 'catalogo' => 0, 'notas' => 0,
                'itens' => 0, 'consolidados' => 0, 'retencoes' => 0, 'apuracao' => 0,
            ];

            // ── Passada 1: participantes + catálogo (limitados) + agregadores ──────────
            $participantes = [];
            $catalogo = [];
            $codpartToDoc = [];
            /** @var array<string, HandlerAgregador> $agregadores */
            $agregadores = [];

            foreach ($paresFactory() as [$rec, $pai]) {
                $handler = $handlers[$rec->reg] ?? null;
                if ($handler === null) {
                    continue;
                }
                if ($handler instanceof HandlerAgregador) {
                    $handler->mapear($rec, $pai); // acumula estado; linha só em finalizar()
                    $agregadores[$handler->tabela()] = $handler;

                    continue;
                }
                $tabela = $handler->tabela();
                if ($tabela === 'participantes') {
                    $row = $handler->mapear($rec, $pai);
                    if ($row === null) {
                        continue;
                    }
                    $participantes[] = $row;
                    if (! empty($row['documento'])) {
                        $codPart = trim((string) $rec->campo(2));
                        if ($codPart !== '') {
                            $codpartToDoc[$codPart] = $row['documento'];
                        }
                    }
                } elseif ($tabela === 'efd_catalogo_itens') {
                    $row = $handler->mapear($rec, $pai);
                    if ($row !== null) {
                        $catalogo[] = $row;
                    }
                }
            }

            $progresso?->bloco('participantes', 'inicio');
            $stats['participantes'] = $this->gravarParticipantes($imp, $participantes);
            $progresso?->bloco('participantes', $participantes === [] ? 'skip' : 'concluido', 100);
            $participantes = [];

            // Participantes já no banco: NFS-e carimba participante_id no insert das notas.
            $codpartToId = $this->resolverCodpartId($imp, $codpartToDoc);

            $progresso?->bloco('catalogo', 'inicio');
            $stats['catalogo'] = $this->gravarCatalogo($imp, $catalogo);
            $progresso?->bloco('catalogo', $catalogo === [] ? 'skip' : 'concluido', 100);
            $catalogo = [];

            // ── Passada 2: notas + retenções, em chunks ───────────────────────────────
            // F600 não tem unique natural: DELETE por importação uma vez garante idempotência.
            DB::table('efd_retencoes_fonte')->where('importacao_id', $imp->id)->delete();

            $bufNota = [];
            $bufRet = [];
            $presenca = ['notas_servicos' => false, 'notas_mercadorias' => false, 'notas_transportes' => false];

            foreach ($paresFactory() as [$rec, $pai]) {
                $handler = $handlers[$rec->reg] ?? null;
                if ($handler === null || $handler instanceof HandlerAgregador) {
                    continue;
                }
                $tabela = $handler->tabela();
                if ($tabela === 'efd_notas') {
                    $row = $handler->mapear($rec, $pai);
                    if ($row === null) {
                        continue;
                    }
                    $presenca[ModeloDocumento::bucketAgrupado((string) ($row['modelo'] ?? ''))] = true;
                    $bufNota[] = $row;
                    if (count($bufNota) >= self::CHUNK) {
                        $stats['notas'] += $this->inserirNotas($imp, $driver, $bufNota, $codpartToId);
                        $bufNota = [];
                    }
                } elseif ($tabela === 'efd_retencoes_fonte') {
                    $row = $handler->mapear($rec, $pai);
                    if ($row === null) {
                        continue;
                    }
                    $bufRet[] = $row;
                    if (count($bufRet) >= self::CHUNK) {
                        $stats['retencoes'] += $this->inserirRetencoes($imp, $bufRet);
                        $bufRet = [];
                    }
                }
            }
            if ($bufNota !== []) {
                $stats['notas'] += $this->inserirNotas($imp, $driver, $bufNota, $codpartToId);
            }
            if ($bufRet !== []) {
                $stats['retencoes'] += $this->inserirRetencoes($imp, $bufRet);
            }
            $bufNota = $bufRet = [];

            foreach ($presenca as $bloco => $tem) {
                $progresso?->bloco($bloco, $tem ? 'concluido' : 'skip', 100);
            }

            // ── Passada 3: itens + consolidados, ligados via o mapa relido, em chunks ──
            $mapa = $this->mapaLinkagem($imp, $driver->origemArquivo());
            $bufItem = [];
            $bufCons = [];

            foreach ($paresFactory() as [$rec, $pai]) {
                $handler = $handlers[$rec->reg] ?? null;
                if ($handler === null || $handler instanceof HandlerAgregador) {
                    continue;
                }
                $tabela = $handler->tabela();
                if ($tabela !== 'efd_notas_itens' && $tabela !== 'efd_notas_consolidados') {
                    continue;
                }
                $row = $handler->mapear($rec, $pai);
                if ($row === null) {
                    continue;
                }
                $linkPai = $pai !== null
                    ? $this->linkKey($pai->chave, $pai->modelo, $pai->numero, $pai->serie, $pai->codPart)
                    : null;
                $notaId = $linkPai !== null ? ($mapa[$linkPai] ?? null) : null;
                if ($notaId === null) {
                    continue; // filho órfão (pai não persistido) — não quebra a caminhada
                }
                if ($tabela === 'efd_notas_itens') {
                    $bufItem[] = [$notaId, $row];
                    if (count($bufItem) >= self::CHUNK) {
                        $stats['itens'] += $this->inserirItens($imp, $bufItem);
                        $bufItem = [];
                    }
                } else {
                    $bufCons[] = [$notaId, $row];
                    if (count($bufCons) >= self::CHUNK) {
                        $stats['consolidados'] += $this->inserirConsolidados($imp, $bufCons);
                        $bufCons = [];
                    }
                }
            }
            if ($bufItem !== []) {
                $stats['itens'] += $this->inserirItens($imp, $bufItem);
            }
            if ($bufCons !== []) {
                $stats['consolidados'] += $this->inserirConsolidados($imp, $bufCons);
            }
            $mapa = $bufItem = $bufCons = [];

            $stats['apuracao'] = $this->gravarAgregadores($imp, $agregadores, $progresso);

            return $stats;
        });
    }

    /** @return array<string, SpedRegistroHandler> reg → handler */
    private function indexarHandlers(SpedDriver $driver): array
    {
        $map = [];
        foreach ($driver->handlers() as $handler) {
            foreach ($handler->registros() as $reg) {
                $map[$reg] = $handler;
            }
        }

        return $map;
    }

    /**
     * Chave de linkagem pai↔filho. chave_acesso quando existe (NF-e/NFC-e/CT-e, única
     * global); senão a identidade lógica modelo|numero|serie|cod_part (NFS-e/A100 não tem
     * chave). Prefixo distingue os dois espaços.
     */
    private function linkKey(?string $chave, ?string $modelo, int|string|null $numero, ?string $serie, ?string $codPart): string
    {
        $chave = trim((string) $chave);
        if ($chave !== '') {
            return 'C:'.$chave;
        }

        return 'N:'.trim((string) $modelo).'|'.(int) $numero.'|'.trim((string) $serie).'|'.trim((string) $codPart);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function gravarParticipantes(EfdImportacao $imp, array $rows): int
    {
        if ($rows === []) {
            return 0;
        }

        $prep = $this->comEscopo($rows, [
            'user_id' => $imp->user_id,
            'cliente_id' => $imp->cliente_id,
            'importacao_efd_id' => $imp->id,
        ]);

        $total = 0;
        foreach (array_chunk($prep, self::CHUNK) as $chunk) {
            // Dedup por (user_id, documento): CNPJ já cadastrado de outra importação
            // não vira "novo" (mantém importacao_efd_id original).
            $total += DB::table('participantes')->insertOrIgnore($chunk);
        }

        return $total;
    }

    /**
     * COD_PART → participante_id, no escopo do usuário. Alimentado após o insert dos 0150.
     *
     * @param  array<string, string>  $codpartToDoc
     * @return array<string, int>
     */
    private function resolverCodpartId(EfdImportacao $imp, array $codpartToDoc): array
    {
        if ($codpartToDoc === []) {
            return [];
        }

        // Normaliza os DOIS lados por dígitos: o COD_PART do arquivo já vem em dígitos, mas
        // participantes.documento pode estar formatado (import via XML) — sem isso o stamp
        // inline falha e a nota nasce sem participante_id.
        $docToId = [];
        foreach (
            DB::table('participantes')
                ->where('user_id', $imp->user_id)
                ->whereNotNull('documento')
                ->select('id', 'documento')
                ->get() as $p
        ) {
            $d = preg_replace('/\D/', '', (string) $p->documento);
            if ($d !== '') {
                $docToId[$d] = (int) $p->id;
            }
        }

        $map = [];
        foreach ($codpartToDoc as $codPart => $doc) {
            $d = preg_replace('/\D/', '', (string) $doc);
            if ($d !== '' && isset($docToId[$d])) {
                $map[$codPart] = $docToId[$d];
            }
        }

        return $map;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function gravarCatalogo(EfdImportacao $imp, array $rows): int
    {
        if ($rows === []) {
            return 0;
        }

        // 0200 é UNIQUE (cliente_id, cod_item): dedup intra-arquivo (upsert do PG não
        // aceita o mesmo alvo 2× no mesmo statement), última versão vence.
        $porCod = [];
        foreach ($rows as $r) {
            $porCod[$r['cod_item']] = $r;
        }

        $prep = $this->comEscopo(array_values($porCod), [
            'user_id' => $imp->user_id,
            'cliente_id' => $imp->cliente_id,
            'importacao_id' => $imp->id,
        ]);

        // Drift: reimportar atualiza cadastro do item (a trigger de histórico registra a
        // mudança). `importacao_id` também é atualizado — senão o item conflitado mantém o
        // id da 1ª importação e a aba Catálogo do mês seguinte (que filtra por importacao_id)
        // renderiza vazia apesar de os itens terem sido atualizados.
        $atualiza = ['importacao_id', 'descr_item', 'cod_barra', 'unid_inv', 'tipo_item', 'cod_ncm', 'cod_gen', 'aliq_icms', 'updated_at'];
        foreach (array_chunk($prep, self::CHUNK) as $chunk) {
            DB::table('efd_catalogo_itens')->upsert($chunk, ['cliente_id', 'cod_item'], $atualiza);
        }

        return count($prep);
    }

    /**
     * Insere um buffer de notas (≤ CHUNK) com participante_id carimbado. `now()` içado
     * (uma vez por chunk, não por linha).
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, int>  $codpartToId
     */
    private function inserirNotas(EfdImportacao $imp, SpedDriver $driver, array $rows, array $codpartToId): int
    {
        if ($rows === []) {
            return 0;
        }

        $agora = now();
        $prep = [];
        foreach ($rows as $row) {
            // participante_id resolvido no insert: o índice único de NFS-e (sem chave)
            // inclui participante_id — deixá-lo null colidiria 2 emitentes de mesmo número.
            $codPart = $row['metadados']['cod_part'] ?? null;
            $participanteId = ($codPart !== null && isset($codpartToId[$codPart])) ? $codpartToId[$codPart] : null;

            $row['metadados'] = isset($row['metadados']) ? json_encode($row['metadados']) : null;
            $prep[] = $row + [
                'participante_id' => $participanteId,
                'user_id' => $imp->user_id,
                'cliente_id' => $imp->cliente_id,
                'importacao_id' => $imp->id,
                'origem_arquivo' => $driver->origemArquivo(),
                'created_at' => $agora,
                'updated_at' => $agora,
            ];
        }

        // insertOrIgnore + trigger trg_efd_notas_ignore_duplicate: reimportação e NFS-e
        // duplicada degradam pra DO NOTHING em vez de estourar o índice único.
        return DB::table('efd_notas')->insertOrIgnore($prep);
    }

    /**
     * link_key → efd_nota_id. Escopo por CLIENTE + ORIGEM_ARQUIVO. Cliente (não importação)
     * porque o dedup é global e uma nota escriturada extemporaneamente numa importação
     * ANTERIOR do mesmo tipo não é reinserida — seus filhos desta importação precisam achar
     * o id existente (bug real: imports 48/49). E FILTRADO por origem_arquivo porque a MESMA
     * NF-e (chave) é escriturada no EFD fiscal E no PIS/COFINS como linhas separadas (o índice
     * único inclui origem_arquivo): sem o filtro, a linkKey `C:chave` colidia entre as duas e
     * os C170 do contrib linkavam na nota fiscal (batendo no numero_item dela → dropados, bug
     * real da importação 423). O filho pertence à nota da MESMA origem que o driver corrente.
     */
    private function mapaLinkagem(EfdImportacao $imp, string $origemArquivo): array
    {
        $map = [];
        $rows = DB::table('efd_notas')
            ->where('user_id', $imp->user_id)
            ->where('origem_arquivo', $origemArquivo)
            ->when(
                $imp->cliente_id,
                fn ($q) => $q->where('cliente_id', $imp->cliente_id),
                fn ($q) => $q->where('importacao_id', $imp->id),
            )
            ->selectRaw("id, chave_acesso, modelo, numero, serie, metadados->>'cod_part' as cod_part")
            ->get();

        foreach ($rows as $r) {
            $map[$this->linkKey($r->chave_acesso, $r->modelo, $r->numero, $r->serie, $r->cod_part)] = (int) $r->id;
        }

        return $map;
    }

    /**
     * Insere um buffer de itens já ligados ([efd_nota_id, row]). `now()` içado.
     *
     * @param  array<int, array{0: int, 1: array<string, mixed>}>  $itens
     */
    private function inserirItens(EfdImportacao $imp, array $itens): int
    {
        if ($itens === []) {
            return 0;
        }

        $agora = now();
        $prep = [];
        foreach ($itens as [$notaId, $row]) {
            $row['metadados'] = isset($row['metadados']) ? json_encode($row['metadados']) : null;
            $prep[] = $row + [
                'efd_nota_id' => $notaId,
                'user_id' => $imp->user_id,
                'created_at' => $agora,
                'updated_at' => $agora,
            ];
        }

        return DB::table('efd_notas_itens')->insertOrIgnore($prep); // UNIQUE (efd_nota_id, numero_item)
    }

    /**
     * Insere um buffer de consolidados já ligados ([efd_nota_id, row]). `now()` içado.
     *
     * @param  array<int, array{0: int, 1: array<string, mixed>}>  $consolidados
     */
    private function inserirConsolidados(EfdImportacao $imp, array $consolidados): int
    {
        if ($consolidados === []) {
            return 0;
        }

        $agora = now();
        $prep = [];
        foreach ($consolidados as [$notaId, $row]) {
            $prep[] = $row + [
                'efd_nota_id' => $notaId,
                'user_id' => $imp->user_id,
                'created_at' => $agora,
                'updated_at' => $agora,
            ];
        }

        // UNIQUE (efd_nota_id, cst_icms, cfop, aliquota_icms) NULLS NOT DISTINCT
        return DB::table('efd_notas_consolidados')->insertOrIgnore($prep);
    }

    /**
     * Insere um buffer de retenções (F600). O DELETE de idempotência é feito UMA vez pela
     * `executar` antes da passada 2. `now()` içado.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function inserirRetencoes(EfdImportacao $imp, array $rows): int
    {
        if ($rows === []) {
            return 0;
        }

        $agora = now();
        $prep = [];
        foreach ($rows as $row) {
            if (array_key_exists('dados_brutos', $row)) {
                $row['dados_brutos'] = $row['dados_brutos'] === null ? null : json_encode($row['dados_brutos']);
            }
            $prep[] = $row + [
                'importacao_id' => $imp->id,
                'user_id' => $imp->user_id,
                'cliente_id' => $imp->cliente_id,
                'created_at' => $agora,
                'updated_at' => $agora,
            ];
        }

        DB::table('efd_retencoes_fonte')->insert($prep);

        return count($prep);
    }

    /**
     * @param  array<string, HandlerAgregador>  $agregadores
     */
    private function gravarAgregadores(EfdImportacao $imp, array $agregadores, ?ProgressoEmitter $progresso): int
    {
        $total = 0;
        foreach ($agregadores as $tabela => $handler) {
            $bloco = $tabela === 'efd_apuracoes_contribuicoes' ? 'apuracao_pis_cofins' : 'apuracao_icms';
            $progresso?->bloco($bloco, 'inicio');

            $row = $handler->finalizar();
            if ($row === null) {
                $progresso?->bloco($bloco, 'skip', 100);

                continue; // bloco não existiu no arquivo
            }

            foreach ($row as $col => $valor) {
                if (is_array($valor)) {
                    $row[$col] = json_encode($valor);
                }
            }

            $row += [
                'importacao_id' => $imp->id,
                'user_id' => $imp->user_id,
                'cliente_id' => $imp->cliente_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // UNIQUE (importacao_id): reprocessar o job não duplica a apuração.
            DB::table($tabela)->insertOrIgnore($row);
            $progresso?->bloco($bloco, 'concluido', 100);
            $total++;
        }

        return $total;
    }

    /**
     * Adiciona colunas de escopo/timestamps a cada linha do bucket.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $escopo
     * @return array<int, array<string, mixed>>
     */
    private function comEscopo(array $rows, array $escopo): array
    {
        $escopo += ['created_at' => now(), 'updated_at' => now()];

        return array_map(fn (array $r): array => $r + $escopo, $rows);
    }
}
