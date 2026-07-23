<?php

namespace App\Services;

use App\Models\EfdDivergencia;
use App\Models\EfdImportacao;
use Illuminate\Support\Facades\DB;

/**
 * Reconcilia o SPED bruto (arquivo_base64) com o que o pipeline persistiu
 * em efd_notas / efd_notas_itens. Para cada discrepância gera um registro
 * em efd_divergencias com o motivo classificado.
 *
 * - C100 com COD_SIT ∈ {02,06,08} → divergência INFO (esperado descartar)
 * - C100 no SPED ausente do banco com COD_SIT='00' → ERRO (pipeline perdeu)
 * - C170 do SPED ausente do banco → AVISO (duplicação/constraint)
 *
 * Idempotente: rodar 2x não duplica linhas em efd_divergencias.
 */
class EfdAuditoriaService
{
    public function auditar(EfdImportacao $imp): array
    {
        $sped = $this->lerSpedBruto($imp);
        $registros = $this->parse($sped);

        $c100Sped = $registros['C100'] ?? [];
        $c170Sped = $registros['C170'] ?? [];

        $c100Banco = DB::table('efd_notas')
            ->where('user_id', $imp->user_id)
            ->where('importacao_id', $imp->id)
            ->get(['id', 'chave_acesso', 'numero', 'serie', 'modelo']);

        $c100BancoPorChave = $c100Banco->keyBy('chave_acesso');

        $resultado = [
            'c100_sped' => count($c100Sped),
            'c100_banco' => $c100Banco->count(),
            'canceladas' => 0,
            'c170_sped' => count($c170Sped),
            'c170_banco' => 0,
            'divergencias_geradas' => 0,
        ];

        // --- C100: canceladas e ausentes ---
        foreach ($c100Sped as $c100) {
            $codSit = $c100['COD_SIT'] ?? '00';
            $chave = $c100['CHV_NFE'] ?? null;
            $numero = isset($c100['NUM_DOC']) ? (int) $c100['NUM_DOC'] : null;

            $motivo = match ($codSit) {
                '02' => EfdDivergencia::MOTIVO_CANCELADA_DESCARTADA,
                '06' => EfdDivergencia::MOTIVO_COMPLEMENTAR_DESCARTADA,
                '08' => EfdDivergencia::MOTIVO_REGULARIZACAO_DESCARTADA,
                default => null,
            };

            if ($motivo) {
                $this->registrar($imp, [
                    'bloco' => 'C100',
                    'motivo' => $motivo,
                    'severidade' => EfdDivergencia::SEVERIDADE_INFO,
                    'chave_acesso' => $chave,
                    'numero_documento' => $numero,
                    'payload_descartado' => $c100,
                    'mensagem' => "C100 COD_SIT={$codSit} descartado pelo pipeline",
                ]);
                $resultado['canceladas']++;
                $resultado['divergencias_geradas']++;

                continue;
            }

            // C100 normal mas ausente no banco
            if ($chave && ! $c100BancoPorChave->has($chave)) {
                $this->registrar($imp, [
                    'bloco' => 'C100',
                    'motivo' => EfdDivergencia::MOTIVO_DUPLICADA_PROCESSAMENTO,
                    'severidade' => EfdDivergencia::SEVERIDADE_ERRO,
                    'chave_acesso' => $chave,
                    'numero_documento' => $numero,
                    'payload_descartado' => $c100,
                    'mensagem' => 'C100 presente no SPED mas ausente no banco',
                ]);
                $resultado['divergencias_geradas']++;
            }
        }

        // --- D100 (CT-e) e A100 (NFS-e): presença do documento por chave ---
        // Sem isto o oráculo era cego a TODO o bloco de contribuições (A100). $c100Banco
        // já contém TODAS as notas da importação (qualquer modelo), então serve de acervo.
        foreach (['D100' => $registros['D100'] ?? [], 'A100' => $registros['A100'] ?? []] as $bloco => $lista) {
            foreach ($lista as $doc) {
                $codSit = $doc['COD_SIT'] ?? '00';
                $chave = $doc['CHV'] ?? null;
                $numero = isset($doc['NUM_DOC']) ? (int) $doc['NUM_DOC'] : null;

                $motivo = match ($codSit) {
                    '02' => EfdDivergencia::MOTIVO_CANCELADA_DESCARTADA,
                    '06' => EfdDivergencia::MOTIVO_COMPLEMENTAR_DESCARTADA,
                    '08' => EfdDivergencia::MOTIVO_REGULARIZACAO_DESCARTADA,
                    default => null,
                };

                if ($motivo) {
                    $this->registrar($imp, [
                        'bloco' => $bloco,
                        'motivo' => $motivo,
                        'severidade' => EfdDivergencia::SEVERIDADE_INFO,
                        'chave_acesso' => $chave,
                        'numero_documento' => $numero,
                        'payload_descartado' => $doc,
                        'mensagem' => "{$bloco} COD_SIT={$codSit} descartado pelo pipeline",
                    ]);
                    $resultado['canceladas']++;
                    $resultado['divergencias_geradas']++;

                    continue;
                }

                // Documento normal mas ausente no banco (por chave, quando há chave).
                if ($chave && ! $c100BancoPorChave->has($chave)) {
                    $this->registrar($imp, [
                        'bloco' => $bloco,
                        'motivo' => EfdDivergencia::MOTIVO_DUPLICADA_PROCESSAMENTO,
                        'severidade' => EfdDivergencia::SEVERIDADE_ERRO,
                        'chave_acesso' => $chave,
                        'numero_documento' => $numero,
                        'payload_descartado' => $doc,
                        'mensagem' => "{$bloco} presente no SPED mas ausente no banco",
                    ]);
                    $resultado['divergencias_geradas']++;
                }
            }
        }

        // --- C170/A170: agrupar itens por documento pai e comparar com o banco ---
        // Reconstroi pai-filho percorrendo SPED ordenado: cada C100/A100 vira contexto.
        $itensSpedPorChave = $this->agruparItensPorPai($sped);

        $c170Banco = DB::table('efd_notas_itens')
            ->join('efd_notas', 'efd_notas.id', '=', 'efd_notas_itens.efd_nota_id')
            ->where('efd_notas.user_id', $imp->user_id)
            ->where('efd_notas.importacao_id', $imp->id)
            ->select('efd_notas.chave_acesso', 'efd_notas.numero', 'efd_notas_itens.numero_item')
            ->get();

        $resultado['c170_banco'] = $c170Banco->count();

        $itensBancoPorChave = [];
        foreach ($c170Banco as $r) {
            $itensBancoPorChave[$r->chave_acesso][$r->numero_item] = true;
        }

        // Coleta união de chaves do SPED e do banco — algumas podem só existir num lado
        $todasChaves = array_unique(array_merge(
            array_keys($itensSpedPorChave),
            array_keys($itensBancoPorChave)
        ));

        // Itens do SPED ausentes no banco (pipeline perdeu)
        foreach ($todasChaves as $chave) {
            // NFS-e sem chave (código de verificação vazio) não é auditável por chave: os
            // itens de várias notas colapsariam sob a chave vazia e cruzariam errado. Pula —
            // a presença da nota já é coberta por integridade()/pelo loop de pais.
            if ($chave === '' || $chave === null) {
                continue;
            }
            $itens = $itensSpedPorChave[$chave] ?? [];
            if (! $c100BancoPorChave->has($chave)) {
                continue;
            }
            $numerosSpedPorNota = [];
            foreach ($itens as $item) {
                $numItem = (int) ($item['NUM_ITEM'] ?? 0);
                $numerosSpedPorNota[$numItem] = true;

                if (! isset($itensBancoPorChave[$chave][$numItem])) {
                    $numeroDoc = $c100BancoPorChave->get($chave)?->numero;
                    $blocoItem = $item['REG'] ?? 'C170';
                    $this->registrar($imp, [
                        'bloco' => $blocoItem,
                        'motivo' => EfdDivergencia::MOTIVO_DUPLICADA_PROCESSAMENTO,
                        'severidade' => EfdDivergencia::SEVERIDADE_AVISO,
                        'chave_acesso' => $chave,
                        'numero_documento' => $numeroDoc,
                        'numero_item' => $numItem,
                        'payload_descartado' => $item,
                        'mensagem' => "{$blocoItem} num_item={$numItem} presente no SPED mas ausente no banco",
                    ]);
                    $resultado['divergencias_geradas']++;
                }
            }

            // Itens no banco que NÃO existem no SPED (duplicação que escapou ON CONFLICT)
            if (isset($itensBancoPorChave[$chave])) {
                foreach ($itensBancoPorChave[$chave] as $numItem => $_) {
                    if (! isset($numerosSpedPorNota[$numItem])) {
                        $numeroDoc = $c100BancoPorChave->get($chave)?->numero;
                        $this->registrar($imp, [
                            'bloco' => 'C170',
                            'motivo' => EfdDivergencia::MOTIVO_DUPLICADA_PROCESSAMENTO,
                            'severidade' => EfdDivergencia::SEVERIDADE_ERRO,
                            'chave_acesso' => $chave,
                            'numero_documento' => $numeroDoc,
                            'numero_item' => $numItem,
                            'payload_descartado' => ['origem' => 'banco', 'numero_item' => $numItem],
                            'mensagem' => "C170 num_item={$numItem} presente no banco mas ausente no SPED (duplicação)",
                        ]);
                        $resultado['divergencias_geradas']++;
                    }
                }
            }
        }

        return $resultado;
    }

    /**
     * Check LEVE de integridade pra rodar no finalize de TODA importação: conta as
     * chaves de nota fiscal válidas do SPED bruto (C100 NF-e/NFC-e + D100 CT-e, fora
     * as COD_SIT descartáveis) e compara com o que entrou em efd_notas. Se o pipeline
     * (n8n) dropou notas — como o Merge C100↔0150 dropando NFC-e sem COD_PART, bug
     * UTIDA 2026-07-21 — `faltando > 0` e `ok = false`, e o finalize marca a importação
     * como suspeita em vez de "concluído" mudo. Não gera divergências (isso é o
     * `auditar()` completo); é só a contagem, barata o suficiente pro caminho quente.
     *
     * Degrada seguro: sem arquivo_base64 retido → esperadas=0 → ok=true (sem alarme falso).
     *
     * @return array{esperadas:int,no_banco:int,faltando:int,ok:bool,amostra_faltando:array<int,string>}
     */
    public function integridade(EfdImportacao $imp): array
    {
        $sped = $this->lerSpedBruto($imp);

        // COD_SIT que o pipeline legitimamente NÃO persiste como nota válida por chave:
        // 02/03 cancelada, 04 denegada, 05 inutilizada, 06 complementar, 08 regularização.
        $descartaveis = ['02', '03', '04', '05', '06', '08'];

        $chavesEsperadas = [];
        foreach (preg_split('/\r\n|\r|\n/', $sped) as $linha) {
            if ($linha === '' || $linha[0] !== '|') {
                continue;
            }
            $c = explode('|', $linha);
            $reg = $c[1] ?? null;
            // Índice do COD_SIT e da chave por registro. A100 (NFS-e) entra também — sem ele
            // o guardrail era cego a TODO o bloco de contribuições (drop de NFS-e passava
            // 'concluído' com selo verde). A100: COD_SIT=$c[5], chave (cód. verificação)=$c[9].
            [$idxSit, $idxChave] = match ($reg) {
                'C100' => [6, 9],   // CHV_NFE
                'D100' => [6, 10],  // CHV_CTE
                'A100' => [5, 9],   // chave/cód. verificação da NFS-e
                default => [null, null],
            };
            if ($idxSit === null) {
                continue;
            }
            if (in_array($c[$idxSit] ?? '00', $descartaveis, true)) {
                continue;
            }
            $chave = trim((string) ($c[$idxChave] ?? ''));
            if ($chave !== '') {
                $chavesEsperadas[$chave] = true;
            }
        }

        // Escopo por CLIENTE (não importação): uma nota escriturada extemporaneamente e
        // deduplicada numa importação anterior existe no acervo do cliente — não é
        // "faltando" (alinha com PersistenciaEngine::mapaLinkagem). Sem cliente_id, cai
        // no escopo da própria importação.
        $chavesBanco = DB::table('efd_notas')
            ->where('user_id', $imp->user_id)
            ->when(
                $imp->cliente_id,
                fn ($q) => $q->where('cliente_id', $imp->cliente_id),
                fn ($q) => $q->where('importacao_id', $imp->id),
            )
            ->whereNotNull('chave_acesso')
            ->pluck('chave_acesso')
            ->flip();

        $faltando = [];
        foreach (array_keys($chavesEsperadas) as $chave) {
            if (! $chavesBanco->has($chave)) {
                $faltando[] = $chave;
            }
        }

        $esperadas = count($chavesEsperadas);
        $qtdFaltando = count($faltando);

        return [
            'esperadas' => $esperadas,
            'no_banco' => $esperadas - $qtdFaltando,
            'faltando' => $qtdFaltando,
            'ok' => $qtdFaltando === 0,
            'amostra_faltando' => array_slice($faltando, 0, 20),
        ];
    }

    /** Lê o SPED bruto (fonte única: EfdImportacao::conteudoSped). */
    private function lerSpedBruto(EfdImportacao $imp): string
    {
        return $imp->conteudoSped();
    }

    /**
     * Parse simples: agrupa registros por código (C100, C170, etc.).
     */
    private function parse(string $sped): array
    {
        $registros = [];
        $linhas = preg_split('/\r\n|\r|\n/', $sped);

        foreach ($linhas as $linha) {
            if ($linha === '' || $linha[0] !== '|') {
                continue;
            }
            $campos = explode('|', $linha);
            // posição 0 é vazia (antes do primeiro |), posição 1 é o REG
            $reg = $campos[1] ?? null;
            if (! $reg) {
                continue;
            }
            $registros[$reg][] = $this->mapearCampos($reg, $campos);
        }

        return $registros;
    }

    /**
     * Mapeia o array bruto pra nomes de campo conhecidos.
     * Foca em C100 e C170 (cobre os casos de auditoria atuais).
     */
    private function mapearCampos(string $reg, array $campos): array
    {
        if ($reg === 'C100') {
            return [
                'REG' => $reg,
                'IND_OPER' => $campos[2] ?? null,
                'IND_EMIT' => $campos[3] ?? null,
                'COD_PART' => $campos[4] ?? null,
                'COD_MOD' => $campos[5] ?? null,
                'COD_SIT' => $campos[6] ?? null,
                'SER' => $campos[7] ?? null,
                'NUM_DOC' => $campos[8] ?? null,
                'CHV_NFE' => $campos[9] ?? null,
                'DT_DOC' => $campos[10] ?? null,
                'DT_E_S' => $campos[11] ?? null,
                'VL_DOC' => $campos[12] ?? null,
            ];
        }
        if ($reg === 'C170') {
            return [
                'REG' => $reg,
                'NUM_ITEM' => $campos[2] ?? null,
                'COD_ITEM' => $campos[3] ?? null,
                'DESCR_COMPL' => $campos[4] ?? null,
                'QTD' => $campos[5] ?? null,
                'UNID' => $campos[6] ?? null,
                'VL_ITEM' => $campos[7] ?? null,
            ];
        }
        // D100 (CT-e): COD_SIT=$c[6], NUM_DOC=$c[9], CHV_CTE=$c[10].
        if ($reg === 'D100') {
            return [
                'REG' => $reg,
                'COD_SIT' => $campos[6] ?? null,
                'NUM_DOC' => $campos[9] ?? null,
                'CHV' => $campos[10] ?? null,
            ];
        }
        // A100 (NFS-e): COD_SIT=$c[5], NUM_DOC=$c[8], chave/cód. verificação=$c[9].
        if ($reg === 'A100') {
            return [
                'REG' => $reg,
                'COD_SIT' => $campos[5] ?? null,
                'NUM_DOC' => $campos[8] ?? null,
                'CHV' => $campos[9] ?? null,
            ];
        }
        // A170 (item de NFS-e): NUM_ITEM=$c[2], COD_ITEM=$c[3].
        if ($reg === 'A170') {
            return [
                'REG' => $reg,
                'NUM_ITEM' => $campos[2] ?? null,
                'COD_ITEM' => $campos[3] ?? null,
            ];
        }

        // fallback: devolve cru
        return ['REG' => $reg, 'raw' => $campos];
    }

    /**
     * Percorre o SPED de cima pra baixo associando itens ao documento-pai imediatamente
     * anterior. Cobre C170→C100 (chave $c[9]) e A170→A100 (chave $c[9]). Retorna
     * [chave_acesso => [item, ...]] (cada item traz REG p/ o bloco da divergência).
     */
    private function agruparItensPorPai(string $sped): array
    {
        $out = [];
        $chaveAtual = null;
        $linhas = preg_split('/\r\n|\r|\n/', $sped);
        foreach ($linhas as $linha) {
            if ($linha === '' || $linha[0] !== '|') {
                continue;
            }
            $campos = explode('|', $linha);
            $reg = $campos[1] ?? null;
            if ($reg === 'C100' || $reg === 'A100') {
                $chaveAtual = $campos[9] ?? null; // ambos têm a chave em $c[9]
            } elseif (($reg === 'C170' || $reg === 'A170') && $chaveAtual) {
                $out[$chaveAtual][] = $this->mapearCampos($reg, $campos);
            }
        }

        return $out;
    }

    private function registrar(EfdImportacao $imp, array $dados): void
    {
        EfdDivergencia::updateOrCreate(
            [
                'importacao_id' => $imp->id,
                'bloco' => $dados['bloco'],
                'motivo' => $dados['motivo'],
                'chave_acesso' => $dados['chave_acesso'] ?? null,
                'numero_item' => $dados['numero_item'] ?? null,
            ],
            [
                'user_id' => $imp->user_id,
                'severidade' => $dados['severidade'],
                'numero_documento' => $dados['numero_documento'] ?? null,
                'payload_descartado' => $dados['payload_descartado'],
                'mensagem' => $dados['mensagem'] ?? null,
                'detectado_em' => now(),
            ]
        );
    }
}
