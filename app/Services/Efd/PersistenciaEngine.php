<?php

namespace App\Services\Efd;

use App\Models\EfdImportacao;
use App\Services\Efd\Driver\SpedDriver;
use App\Services\Efd\Handlers\HandlerAgregador;
use App\Services\Efd\Handlers\SpedRegistroHandler;
use App\Services\Efd\Sped\Contexto;
use App\Services\Efd\Sped\SpedRecord;
use Illuminate\Support\Facades\DB;

/**
 * Persiste os registros de uma importação nas tabelas reais (schema NÃO muda) — L3,
 * 100% compartilhado entre EFD ICMS/IPI e PIS/COFINS. §L3/§10.5.
 *
 * Fluxo: roteia (registro, pai) → handler → bucket por tabela; grava participantes;
 * resolve COD_PART→participante_id; grava notas COM participante_id (o índice único de
 * NFS-e inclui participante_id — resolver depois colidiria); relê o mapa de linkagem
 * (chave_acesso quando há; senão modelo|numero|serie|cod_part, p/ NFS-e sem chave) e liga
 * itens/consolidados; grava retenções e a apuração (agregador). Transação por importação,
 * idempotente (reimportar não duplica).
 */
class PersistenciaEngine
{
    private const CHUNK = 1000;

    /** CT-e/transporte (bloco de progresso próprio). */
    private const MODELOS_TRANSPORTE = ['57', '67'];

    /** NFS-e (serviço) — bloco de progresso próprio. */
    private const MODELO_SERVICO = '00';

    /**
     * @param  iterable<array{0: SpedRecord, 1: ?Contexto}>  $pares  saída do ContextWalker
     * @return array<string,int> contagem persistida por bucket
     */
    public function executar(EfdImportacao $imp, SpedDriver $driver, iterable $pares, ?ProgressoEmitter $progresso = null): array
    {
        $handlers = $this->indexarHandlers($driver);

        $participantes = [];
        $catalogo = [];
        $notas = [];
        $itens = [];         // [link_key, row]
        $consolidados = [];   // [link_key, row]
        $retencoes = [];
        $codpartToDoc = [];   // COD_PART → documento (do 0150; COD_PART é escopado ao arquivo)
        /** @var array<string, HandlerAgregador> $agregadores */
        $agregadores = [];

        foreach ($pares as [$rec, $pai]) {
            $handler = $handlers[$rec->reg] ?? null;
            if ($handler === null) {
                continue;
            }

            if ($handler instanceof HandlerAgregador) {
                $handler->mapear($rec, $pai); // acumula estado; linha só em finalizar()
                $agregadores[$handler->tabela()] = $handler;

                continue;
            }

            $row = $handler->mapear($rec, $pai);
            if ($row === null) {
                continue;
            }

            $tabela = $handler->tabela();
            $linkPai = $pai !== null ? $this->linkKey($pai->chave, $pai->modelo, $pai->numero, $pai->serie, $pai->codPart) : null;

            match ($tabela) {
                'participantes' => $participantes[] = $row,
                'efd_catalogo_itens' => $catalogo[] = $row,
                'efd_notas' => $notas[] = $row,
                'efd_notas_itens' => $itens[] = [$linkPai, $row],
                'efd_notas_consolidados' => $consolidados[] = [$linkPai, $row],
                'efd_retencoes_fonte' => $retencoes[] = $row,
                default => null,
            };

            // COD_PART→documento (participante do arquivo): $p[2] do 0150 × documento mapeado.
            if ($tabela === 'participantes' && ! empty($row['documento'])) {
                $codPart = trim((string) $rec->campo(2));
                if ($codPart !== '') {
                    $codpartToDoc[$codPart] = $row['documento'];
                }
            }
        }

        return DB::transaction(function () use ($imp, $driver, $progresso, $participantes, $catalogo, $notas, $itens, $consolidados, $retencoes, $codpartToDoc, $agregadores) {
            $stats = [];

            $progresso?->bloco('participantes', 'inicio');
            $stats['participantes'] = $this->gravarParticipantes($imp, $participantes);
            $progresso?->bloco('participantes', $participantes === [] ? 'skip' : 'concluido', 100);

            // Precisa dos participantes já no banco: NFS-e carimba participante_id no insert.
            $codpartToId = $this->resolverCodpartId($imp, $codpartToDoc);

            $progresso?->bloco('catalogo', 'inicio');
            $stats['catalogo'] = $this->gravarCatalogo($imp, $catalogo);
            $progresso?->bloco('catalogo', $catalogo === [] ? 'skip' : 'concluido', 100);

            $presenca = $this->presencaBlocosNota($notas);
            foreach ($presenca as $bloco => $tem) {
                if ($tem) {
                    $progresso?->bloco($bloco, 'inicio');
                }
            }

            $stats['notas'] = $this->gravarNotas($imp, $driver, $notas, $codpartToId);

            $mapa = $this->mapaLinkagem($imp);
            $stats['itens'] = $this->gravarItens($imp, $itens, $mapa);
            $stats['consolidados'] = $this->gravarConsolidados($imp, $consolidados, $mapa);

            foreach ($presenca as $bloco => $tem) {
                $progresso?->bloco($bloco, $tem ? 'concluido' : 'skip', 100);
            }

            $stats['retencoes'] = $this->gravarRetencoes($imp, $retencoes);
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
     * @param  array<int, array<string, mixed>>  $notas
     * @return array<string, bool> bloco de progresso → presente?
     */
    private function presencaBlocosNota(array $notas): array
    {
        $servico = $mercadoria = $transporte = false;
        foreach ($notas as $n) {
            $modelo = (string) ($n['modelo'] ?? '');
            if ($modelo === self::MODELO_SERVICO) {
                $servico = true;
            } elseif (in_array($modelo, self::MODELOS_TRANSPORTE, true)) {
                $transporte = true;
            } else {
                $mercadoria = true;
            }
        }

        return [
            'notas_servicos' => $servico,
            'notas_mercadorias' => $mercadoria,
            'notas_transportes' => $transporte,
        ];
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

        $docToId = DB::table('participantes')
            ->where('user_id', $imp->user_id)
            ->whereNotNull('documento')
            ->pluck('id', 'documento');

        $map = [];
        foreach ($codpartToDoc as $codPart => $doc) {
            $id = $docToId[$doc] ?? $docToId[preg_replace('/\D/', '', (string) $doc)] ?? null;
            if ($id !== null) {
                $map[$codPart] = (int) $id;
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

        // Drift: reimportar atualiza cadastro do item (a trigger de histórico registra a mudança).
        $atualiza = ['descr_item', 'cod_barra', 'unid_inv', 'tipo_item', 'cod_ncm', 'cod_gen', 'aliq_icms', 'updated_at'];
        foreach (array_chunk($prep, self::CHUNK) as $chunk) {
            DB::table('efd_catalogo_itens')->upsert($chunk, ['cliente_id', 'cod_item'], $atualiza);
        }

        return count($prep);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, int>  $codpartToId
     */
    private function gravarNotas(EfdImportacao $imp, SpedDriver $driver, array $rows, array $codpartToId): int
    {
        if ($rows === []) {
            return 0;
        }

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
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // insertOrIgnore + trigger trg_efd_notas_ignore_duplicate: reimportação e NFS-e
        // duplicada degradam pra DO NOTHING em vez de estourar o índice único.
        $total = 0;
        foreach (array_chunk($prep, self::CHUNK) as $chunk) {
            $total += DB::table('efd_notas')->insertOrIgnore($chunk);
        }

        return $total;
    }

    /** link_key → efd_nota_id desta importação (chave, ou identidade lógica p/ NFS-e). */
    private function mapaLinkagem(EfdImportacao $imp): array
    {
        $map = [];
        $rows = DB::table('efd_notas')
            ->where('user_id', $imp->user_id)
            ->where('importacao_id', $imp->id)
            ->selectRaw("id, chave_acesso, modelo, numero, serie, metadados->>'cod_part' as cod_part")
            ->get();

        foreach ($rows as $r) {
            $map[$this->linkKey($r->chave_acesso, $r->modelo, $r->numero, $r->serie, $r->cod_part)] = (int) $r->id;
        }

        return $map;
    }

    /**
     * @param  array<int, array{0: ?string, 1: array<string, mixed>}>  $itens
     * @param  array<string, int>  $mapa
     */
    private function gravarItens(EfdImportacao $imp, array $itens, array $mapa): int
    {
        if ($itens === []) {
            return 0;
        }

        $prep = [];
        foreach ($itens as [$key, $row]) {
            $notaId = $key !== null ? ($mapa[$key] ?? null) : null;
            if ($notaId === null) {
                continue; // filho órfão (pai não persistido) — não quebra a caminhada
            }
            $row['metadados'] = isset($row['metadados']) ? json_encode($row['metadados']) : null;
            $prep[] = $row + [
                'efd_nota_id' => $notaId,
                'user_id' => $imp->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $total = 0;
        foreach (array_chunk($prep, self::CHUNK) as $chunk) {
            $total += DB::table('efd_notas_itens')->insertOrIgnore($chunk); // UNIQUE (efd_nota_id, numero_item)
        }

        return $total;
    }

    /**
     * @param  array<int, array{0: ?string, 1: array<string, mixed>}>  $consolidados
     * @param  array<string, int>  $mapa
     */
    private function gravarConsolidados(EfdImportacao $imp, array $consolidados, array $mapa): int
    {
        if ($consolidados === []) {
            return 0;
        }

        $prep = [];
        foreach ($consolidados as [$key, $row]) {
            $notaId = $key !== null ? ($mapa[$key] ?? null) : null;
            if ($notaId === null) {
                continue;
            }
            $prep[] = $row + [
                'efd_nota_id' => $notaId,
                'user_id' => $imp->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $total = 0;
        foreach (array_chunk($prep, self::CHUNK) as $chunk) {
            // UNIQUE (efd_nota_id, cst_icms, cfop, aliquota_icms) NULLS NOT DISTINCT
            $total += DB::table('efd_notas_consolidados')->insertOrIgnore($chunk);
        }

        return $total;
    }

    /**
     * F600 → efd_retencoes_fonte. Sem unique natural — DELETE por importação antes de
     * inserir garante idempotência (reprocessar o job não duplica).
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function gravarRetencoes(EfdImportacao $imp, array $rows): int
    {
        if ($rows === []) {
            return 0;
        }

        DB::table('efd_retencoes_fonte')->where('importacao_id', $imp->id)->delete();

        $prep = [];
        foreach ($rows as $row) {
            if (array_key_exists('dados_brutos', $row)) {
                $row['dados_brutos'] = $row['dados_brutos'] === null ? null : json_encode($row['dados_brutos']);
            }
            $prep[] = $row + [
                'importacao_id' => $imp->id,
                'user_id' => $imp->user_id,
                'cliente_id' => $imp->cliente_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($prep, self::CHUNK) as $chunk) {
            DB::table('efd_retencoes_fonte')->insert($chunk);
        }

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
