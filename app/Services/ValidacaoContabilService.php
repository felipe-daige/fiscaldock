<?php

namespace App\Services;

use App\Models\NotaFiscal;
use App\Models\Participante;
use App\Models\ParticipanteScore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ValidacaoContabilService
{
    // Pesos para cada categoria de validacao (soma = 1.0)
    private array $pesos = [
        'cadastral' => 0.20,
        'tributacao' => 0.25,
        'cfop_cst' => 0.20,
        'integridade' => 0.15,
        'ncm' => 0.10,
        'operacoes' => 0.10,
    ];

    // NCMs invalidos ou genericos
    private array $ncmsProblematicos = [
        '00000000',
        '99999999',
        '00000001',
        '99990000',
    ];

    // CFOPs de entrada (iniciam com 1, 2 ou 3)
    private array $cfopEntrada = ['1', '2', '3'];

    // CFOPs de saida (iniciam com 5, 6 ou 7)
    private array $cfopSaida = ['5', '6', '7'];

    public function __construct(
        protected ?RiskScoreService $riskScoreService = null
    ) {}

    /**
     * Valida uma nota fiscal individual.
     */
    public function validarNota(NotaFiscal $nota, bool $incluirOperacoes = true): array
    {
        $alertas = [];
        $scores = [];

        // 1. Validacao Cadastral (CRT, situacao RF)
        $scores['cadastral'] = $this->validarCadastral($nota, $alertas);

        // 2. Validacao de Tributacao (aliquotas vs regime)
        $scores['tributacao'] = $this->validarTributacao($nota, $alertas);

        // 3. Validacao CFOP/CST (combinacoes validas)
        $scores['cfop_cst'] = $this->validarCfopCst($nota, $alertas);

        // 4. Validacao de Integridade de Valores
        $scores['integridade'] = $this->validarIntegridade($nota, $alertas);

        // 5. Validacao de NCM
        $scores['ncm'] = $this->validarNcm($nota, $alertas);

        // 6. Validacao de Operacoes com Participantes de Risco
        if ($incluirOperacoes) {
            $scores['operacoes'] = $this->validarOperacoes($nota, $alertas);
        } else {
            $scores['operacoes'] = 0;
        }

        $scoreTotal = $this->calcularScoreTotal($scores);

        return [
            'score_total' => $scoreTotal,
            'classificacao' => $this->classificar($scoreTotal, $alertas),
            'scores' => $scores,
            'alertas' => $alertas,
            'validado_em' => now()->toISOString(),
        ];
    }

    /**
     * Valida todas as notas de uma importacao.
     */
    public function validarImportacao(int $importacaoId, int $userId): array
    {
        $notas = NotaFiscal::where('importacao_xml_id', $importacaoId)
            ->where('user_id', $userId)
            ->get();

        if ($notas->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Nenhuma nota encontrada para esta importacao',
                'total' => 0,
            ];
        }

        $resultados = [];
        $totais = [
            'total' => $notas->count(),
            'validadas' => 0,
            'conforme' => 0,
            'atencao' => 0,
            'irregular' => 0,
            'critico' => 0,
            'alertas_bloqueantes' => 0,
            'alertas_atencao' => 0,
            'alertas_info' => 0,
        ];

        DB::beginTransaction();
        try {
            foreach ($notas as $nota) {
                $resultado = $this->validarNota($nota);
                $nota->update(['validacao' => $resultado]);
                $resultados[] = $resultado;

                $totais['validadas']++;
                $totais[$resultado['classificacao']]++;

                foreach ($resultado['alertas'] as $alerta) {
                    $key = 'alertas_' . strtolower($alerta['nivel']);
                    if (isset($totais[$key])) {
                        $totais[$key]++;
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "{$totais['validadas']} nota(s) validada(s)",
                'totais' => $totais,
                'score_medio' => $this->calcularScoreMedio($resultados),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao validar importacao', [
                'importacao_id' => $importacaoId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao validar notas: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Valida notas por IDs especificos.
     */
    public function validarNotas(array $notaIds, int $userId): array
    {
        $notas = NotaFiscal::whereIn('id', $notaIds)
            ->where('user_id', $userId)
            ->get();

        if ($notas->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Nenhuma nota encontrada',
                'total' => 0,
            ];
        }

        $resultados = [];
        $totais = [
            'total' => $notas->count(),
            'validadas' => 0,
            'conforme' => 0,
            'atencao' => 0,
            'irregular' => 0,
            'critico' => 0,
        ];

        DB::beginTransaction();
        try {
            foreach ($notas as $nota) {
                $resultado = $this->validarNota($nota);
                $nota->update(['validacao' => $resultado]);
                $resultados[] = $resultado;

                $totais['validadas']++;
                $totais[$resultado['classificacao']]++;
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "{$totais['validadas']} nota(s) validada(s)",
                'totais' => $totais,
                'score_medio' => $this->calcularScoreMedio($resultados),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao validar notas', [
                'nota_ids' => $notaIds,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erro ao validar notas: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calcula o custo de validacao baseado nos participantes unicos.
     */
    public function calcularCusto(array $notaIds, int $userId, string $tipo = 'completa'): array
    {
        $notas = NotaFiscal::whereIn('id', $notaIds)
            ->where('user_id', $userId)
            ->get();

        // Coletar participantes unicos
        $participanteIds = $notas
            ->flatMap(fn ($nota) => [$nota->emit_participante_id, $nota->dest_participante_id])
            ->filter()
            ->unique()
            ->values();

        $totalParticipantes = $participanteIds->count();
        $totalNotas = $notas->count();

        // Calcular custo (local = gratuito)
        $custoUnitario = match ($tipo) {
            'local' => 0,
            'deep' => 3,
            default => 1, // 'completa'
        };
        $custoTotal = $totalParticipantes * $custoUnitario;

        return [
            'notas' => $totalNotas,
            'participantes_unicos' => $totalParticipantes,
            'tipo' => $tipo,
            'custo_unitario' => $custoUnitario,
            'custo_total' => $custoTotal,
            'custo_reais' => number_format($custoTotal * 0.26, 2, ',', '.'),
        ];
    }

    /**
     * Obtem estatisticas de validacao para um usuario.
     */
    public function getEstatisticas(int $userId): array
    {
        $totais = NotaFiscal::where('user_id', $userId)
            ->select(
                DB::raw('COUNT(*) as total'),
                DB::raw("COUNT(CASE WHEN validacao IS NOT NULL THEN 1 END) as validadas"),
                DB::raw("COUNT(CASE WHEN validacao->>'classificacao' = 'conforme' THEN 1 END) as conforme"),
                DB::raw("COUNT(CASE WHEN validacao->>'classificacao' = 'atencao' THEN 1 END) as atencao"),
                DB::raw("COUNT(CASE WHEN validacao->>'classificacao' = 'irregular' THEN 1 END) as irregular"),
                DB::raw("COUNT(CASE WHEN validacao->>'classificacao' = 'critico' THEN 1 END) as critico"),
                DB::raw("AVG((validacao->>'score_total')::int) as media_score")
            )
            ->first();

        return [
            'total_notas' => (int) ($totais->total ?? 0),
            'total_validadas' => (int) ($totais->validadas ?? 0),
            'conforme' => (int) ($totais->conforme ?? 0),
            'atencao' => (int) ($totais->atencao ?? 0),
            'irregular' => (int) ($totais->irregular ?? 0),
            'critico' => (int) ($totais->critico ?? 0),
            'media_score' => round((float) ($totais->media_score ?? 0), 1),
            'percentual_validado' => $totais->total > 0
                ? round(($totais->validadas / $totais->total) * 100, 1)
                : 0,
        ];
    }

    /**
     * Retorna os pesos configurados.
     */
    public function getPesos(): array
    {
        return $this->pesos;
    }

    /**
     * Retorna descricao das categorias.
     */
    public function getCategorias(): array
    {
        return [
            'cadastral' => [
                'nome' => 'Consistencia Cadastral',
                'peso' => $this->pesos['cadastral'],
                'descricao' => 'CRT declarado vs situacao real na Receita Federal',
            ],
            'tributacao' => [
                'nome' => 'Tributacao',
                'peso' => $this->pesos['tributacao'],
                'descricao' => 'Aliquotas compativeis com regime tributario',
            ],
            'cfop_cst' => [
                'nome' => 'CFOP/CST',
                'peso' => $this->pesos['cfop_cst'],
                'descricao' => 'Combinacoes validas de CFOP e CST/CSOSN',
            ],
            'integridade' => [
                'nome' => 'Integridade de Valores',
                'peso' => $this->pesos['integridade'],
                'descricao' => 'Soma dos tributos vs total declarado',
            ],
            'ncm' => [
                'nome' => 'NCM',
                'peso' => $this->pesos['ncm'],
                'descricao' => 'NCMs validos e compativeis com operacao',
            ],
            'operacoes' => [
                'nome' => 'Operacoes com Risco',
                'peso' => $this->pesos['operacoes'],
                'descricao' => 'Participantes em listas restritivas',
            ],
        ];
    }

    // ============ Metodos de validacao individual ============

    private function validarCadastral(NotaFiscal $nota, array &$alertas): int
    {
        $score = 0;
        $payload = $nota->payload ?? [];

        // Obter CRT do emitente
        $crtXml = $payload['emit']['CRT'] ?? null;

        // Buscar participante emitente
        $participante = null;
        if ($nota->emit_participante_id) {
            $participante = Participante::find($nota->emit_participante_id);
        }

        // Validar situacao cadastral do emitente
        if ($participante) {
            $situacao = strtoupper($participante->situacao_cadastral ?? 'ATIVA');

            if (in_array($situacao, ['BAIXADA', 'INAPTA', 'NULA'])) {
                $alertas[] = [
                    'categoria' => 'cadastral',
                    'nivel' => 'bloqueante',
                    'codigo' => 'EMIT_BAIXADA',
                    'mensagem' => "Emitente com situacao cadastral: {$situacao}",
                    'detalhe' => "CNPJ {$nota->emit_cnpj} esta {$situacao} na Receita Federal",
                ];
                $score += 100;
            } elseif ($situacao === 'SUSPENSA') {
                $alertas[] = [
                    'categoria' => 'cadastral',
                    'nivel' => 'atencao',
                    'codigo' => 'EMIT_SUSPENSA',
                    'mensagem' => 'Emitente com situacao SUSPENSA',
                    'detalhe' => "CNPJ {$nota->emit_cnpj} esta suspenso",
                ];
                $score += 50;
            }

            // Validar CRT vs regime real
            if ($crtXml && $participante->crt) {
                if ((int) $crtXml !== (int) $participante->crt) {
                    $alertas[] = [
                        'categoria' => 'cadastral',
                        'nivel' => 'atencao',
                        'codigo' => 'CRT_DIVERGENTE',
                        'mensagem' => 'CRT no XML diverge do cadastro',
                        'detalhe' => "XML: CRT={$crtXml}, Cadastro: CRT={$participante->crt}",
                    ];
                    $score += 30;
                }
            }
        }

        // Validar destinatario tambem
        if ($nota->dest_participante_id) {
            $destParticipante = Participante::find($nota->dest_participante_id);
            if ($destParticipante) {
                $situacaoDest = strtoupper($destParticipante->situacao_cadastral ?? 'ATIVA');

                if (in_array($situacaoDest, ['BAIXADA', 'INAPTA', 'NULA'])) {
                    $alertas[] = [
                        'categoria' => 'cadastral',
                        'nivel' => 'atencao',
                        'codigo' => 'DEST_BAIXADA',
                        'mensagem' => "Destinatario com situacao cadastral: {$situacaoDest}",
                        'detalhe' => "CNPJ {$nota->dest_cnpj} esta {$situacaoDest}",
                    ];
                    $score += 40;
                }
            }
        }

        return min(100, $score);
    }

    private function validarTributacao(NotaFiscal $nota, array &$alertas): int
    {
        $score = 0;
        $payload = $nota->payload ?? [];

        // Obter CRT do emitente
        $crt = (int) ($payload['emit']['CRT'] ?? 3);

        // Valores dos tributos
        $icmsValor = (float) ($nota->icms_valor ?? 0);
        $pisValor = (float) ($nota->pis_valor ?? 0);
        $cofinsValor = (float) ($nota->cofins_valor ?? 0);
        $ipiValor = (float) ($nota->ipi_valor ?? 0);
        $valorTotal = (float) ($nota->valor_total ?? 0);

        if ($valorTotal <= 0) {
            return 50; // Nao e possivel validar sem valor
        }

        // Calcular aliquotas efetivas
        $aliquotaIcms = ($icmsValor / $valorTotal) * 100;
        $aliquotaPis = ($pisValor / $valorTotal) * 100;
        $aliquotaCofins = ($cofinsValor / $valorTotal) * 100;

        // Simples Nacional (CRT = 1)
        if ($crt === 1) {
            // Simples normalmente nao destaca ICMS (usa CSOSN)
            if ($aliquotaIcms > 5) {
                $alertas[] = [
                    'categoria' => 'tributacao',
                    'nivel' => 'atencao',
                    'codigo' => 'SIMPLES_ICMS_ALTO',
                    'mensagem' => 'Simples Nacional com ICMS destacado acima de 5%',
                    'detalhe' => "Aliquota efetiva: " . number_format($aliquotaIcms, 2) . "%",
                ];
                $score += 40;
            }

            // Simples nao deve ter PIS/COFINS destacado separadamente (vai no DAS)
            if ($aliquotaPis > 0 || $aliquotaCofins > 0) {
                $alertas[] = [
                    'categoria' => 'tributacao',
                    'nivel' => 'info',
                    'codigo' => 'SIMPLES_PIS_COFINS',
                    'mensagem' => 'Simples Nacional com PIS/COFINS destacado',
                    'detalhe' => 'Verificar se operacao permite destaque',
                ];
                $score += 10;
            }
        }

        // Lucro Real/Presumido (CRT = 3)
        if ($crt === 3) {
            // Verificar se PIS/COFINS esta zerado (pode ser suspensao, isencao ou erro)
            if ($aliquotaPis == 0 && $aliquotaCofins == 0 && $valorTotal > 1000) {
                $alertas[] = [
                    'categoria' => 'tributacao',
                    'nivel' => 'info',
                    'codigo' => 'PIS_COFINS_ZERO',
                    'mensagem' => 'PIS/COFINS zerados em nota de Lucro Real/Presumido',
                    'detalhe' => 'Verificar CST de PIS/COFINS (pode ser isento/suspenso)',
                ];
                $score += 10;
            }
        }

        // IPI em operacao que normalmente nao tem
        $natureza = strtoupper($nota->natureza_operacao ?? '');
        if ($ipiValor > 0 && (str_contains($natureza, 'SERVICO') || str_contains($natureza, 'SERVIÇO'))) {
            $alertas[] = [
                'categoria' => 'tributacao',
                'nivel' => 'bloqueante',
                'codigo' => 'IPI_EM_SERVICO',
                'mensagem' => 'IPI destacado em operacao de servico',
                'detalhe' => "IPI: R$ " . number_format($ipiValor, 2, ',', '.'),
            ];
            $score += 80;
        }

        return min(100, $score);
    }

    private function validarCfopCst(NotaFiscal $nota, array &$alertas): int
    {
        $score = 0;
        $payload = $nota->payload ?? [];

        // Extrair itens
        $itens = $payload['det'] ?? [];
        if (! is_array($itens)) {
            return 0;
        }

        // Se nao for array de itens, encapsular
        if (isset($itens['prod'])) {
            $itens = [$itens];
        }

        foreach ($itens as $index => $item) {
            $cfop = $item['prod']['CFOP'] ?? null;
            $itemNum = $index + 1;

            if (! $cfop) {
                continue;
            }

            $cfopStr = (string) $cfop;
            $primeiroDig = substr($cfopStr, 0, 1);

            // Verificar consistencia CFOP vs tipo_nota
            $tipoNota = $nota->tipo_nota;

            // Entrada (tipo_nota = 0) deve ter CFOPs de entrada (1, 2, 3)
            if ($tipoNota === 0 && in_array($primeiroDig, $this->cfopSaida)) {
                $alertas[] = [
                    'categoria' => 'cfop_cst',
                    'nivel' => 'bloqueante',
                    'codigo' => 'CFOP_TIPO_INCONSISTENTE',
                    'mensagem' => "Item {$itemNum}: CFOP de saida em nota de entrada",
                    'detalhe' => "CFOP {$cfop} nao e compativel com nota de entrada",
                ];
                $score += 50;
            }

            // Saida (tipo_nota = 1) deve ter CFOPs de saida (5, 6, 7)
            if ($tipoNota === 1 && in_array($primeiroDig, $this->cfopEntrada)) {
                $alertas[] = [
                    'categoria' => 'cfop_cst',
                    'nivel' => 'bloqueante',
                    'codigo' => 'CFOP_TIPO_INCONSISTENTE',
                    'mensagem' => "Item {$itemNum}: CFOP de entrada em nota de saida",
                    'detalhe' => "CFOP {$cfop} nao e compativel com nota de saida",
                ];
                $score += 50;
            }

            // Verificar CFOP interestadual vs UFs
            $cfopInterestadual = in_array($primeiroDig, ['2', '6']);
            $emitUf = $nota->emit_uf;
            $destUf = $nota->dest_uf;

            if ($emitUf && $destUf) {
                $mesmoEstado = strtoupper($emitUf) === strtoupper($destUf);

                if ($cfopInterestadual && $mesmoEstado) {
                    $alertas[] = [
                        'categoria' => 'cfop_cst',
                        'nivel' => 'atencao',
                        'codigo' => 'CFOP_UF_INCONSISTENTE',
                        'mensagem' => "Item {$itemNum}: CFOP interestadual em operacao interna",
                        'detalhe' => "CFOP {$cfop} indica interestadual, mas emit/dest sao do mesmo estado ({$emitUf})",
                    ];
                    $score += 30;
                }

                $cfopInterno = in_array($primeiroDig, ['1', '5']);
                if ($cfopInterno && ! $mesmoEstado) {
                    $alertas[] = [
                        'categoria' => 'cfop_cst',
                        'nivel' => 'atencao',
                        'codigo' => 'CFOP_UF_INCONSISTENTE',
                        'mensagem' => "Item {$itemNum}: CFOP interno em operacao interestadual",
                        'detalhe' => "CFOP {$cfop} indica interno, mas emit ({$emitUf}) != dest ({$destUf})",
                    ];
                    $score += 30;
                }
            }

            // Extrair CST do ICMS
            $icms = $item['imposto']['ICMS'] ?? [];
            $cst = null;
            foreach ($icms as $tipo => $dados) {
                if (isset($dados['CST'])) {
                    $cst = $dados['CST'];
                    break;
                }
                if (isset($dados['CSOSN'])) {
                    $cst = 'CSOSN_' . $dados['CSOSN'];
                    break;
                }
            }

            // Validar combinacoes CFOP + CST
            // CFOP 5102/6102 (venda) com CST 60 (ICMS cobrado por ST) e incomum
            if (in_array($cfop, ['5102', '6102']) && $cst === '60') {
                $alertas[] = [
                    'categoria' => 'cfop_cst',
                    'nivel' => 'info',
                    'codigo' => 'CFOP_CST_ATIPICO',
                    'mensagem' => "Item {$itemNum}: Venda com ICMS-ST",
                    'detalhe' => "CFOP {$cfop} com CST 60 - verificar se produto e ST",
                ];
                $score += 10;
            }
        }

        return min(100, $score);
    }

    private function validarIntegridade(NotaFiscal $nota, array &$alertas): int
    {
        $score = 0;
        $payload = $nota->payload ?? [];

        // Valores da nota
        $valorTotal = (float) ($nota->valor_total ?? 0);

        // Valores declarados nos campos resumidos
        $icmsValor = (float) ($nota->icms_valor ?? 0);
        $icmsStValor = (float) ($nota->icms_st_valor ?? 0);
        $pisValor = (float) ($nota->pis_valor ?? 0);
        $cofinsValor = (float) ($nota->cofins_valor ?? 0);
        $ipiValor = (float) ($nota->ipi_valor ?? 0);
        $tributosTotal = (float) ($nota->tributos_total ?? 0);

        // Soma calculada
        $somaCalculada = $icmsValor + $icmsStValor + $pisValor + $cofinsValor + $ipiValor;

        // Tributos do payload (vTotTrib)
        $vTotTrib = (float) ($payload['total']['ICMSTot']['vTotTrib'] ?? 0);

        // Verificar se tributos_total bate com vTotTrib
        if ($tributosTotal > 0 && $vTotTrib > 0) {
            $diferencaPerc = abs($tributosTotal - $vTotTrib) / max($vTotTrib, 0.01) * 100;

            if ($diferencaPerc > 5) {
                $alertas[] = [
                    'categoria' => 'integridade',
                    'nivel' => 'bloqueante',
                    'codigo' => 'TRIBUTOS_DIVERGENTES',
                    'mensagem' => 'Divergencia superior a 5% nos tributos',
                    'detalhe' => "Campo: R$ " . number_format($tributosTotal, 2, ',', '.') .
                        " | XML vTotTrib: R$ " . number_format($vTotTrib, 2, ',', '.'),
                ];
                $score += 70;
            } elseif ($diferencaPerc > 1) {
                $alertas[] = [
                    'categoria' => 'integridade',
                    'nivel' => 'atencao',
                    'codigo' => 'TRIBUTOS_DIVERGENTES_LEVE',
                    'mensagem' => 'Divergencia entre 1% e 5% nos tributos',
                    'detalhe' => "Diferenca de " . number_format($diferencaPerc, 2) . "%",
                ];
                $score += 30;
            }
        }

        // Verificar se tributos > valor total (impossivel)
        if ($somaCalculada > $valorTotal && $valorTotal > 0) {
            $alertas[] = [
                'categoria' => 'integridade',
                'nivel' => 'bloqueante',
                'codigo' => 'TRIBUTOS_MAIOR_TOTAL',
                'mensagem' => 'Soma dos tributos maior que valor total',
                'detalhe' => "Tributos: R$ " . number_format($somaCalculada, 2, ',', '.') .
                    " | Total: R$ " . number_format($valorTotal, 2, ',', '.'),
            ];
            $score += 100;
        }

        // Verificar consistencia dos totais no payload
        $vNF = (float) ($payload['total']['ICMSTot']['vNF'] ?? 0);
        if ($vNF > 0 && $valorTotal > 0) {
            $diferencaVNF = abs($vNF - $valorTotal) / max($valorTotal, 0.01) * 100;

            if ($diferencaVNF > 1) {
                $alertas[] = [
                    'categoria' => 'integridade',
                    'nivel' => 'atencao',
                    'codigo' => 'VALOR_TOTAL_DIVERGENTE',
                    'mensagem' => 'Valor total diverge do XML',
                    'detalhe' => "Campo: R$ " . number_format($valorTotal, 2, ',', '.') .
                        " | XML vNF: R$ " . number_format($vNF, 2, ',', '.'),
                ];
                $score += 20;
            }
        }

        return min(100, $score);
    }

    private function validarNcm(NotaFiscal $nota, array &$alertas): int
    {
        $score = 0;
        $payload = $nota->payload ?? [];

        // Extrair itens
        $itens = $payload['det'] ?? [];
        if (! is_array($itens)) {
            return 0;
        }

        // Se nao for array de itens, encapsular
        if (isset($itens['prod'])) {
            $itens = [$itens];
        }

        $ncmsEncontrados = [];

        foreach ($itens as $index => $item) {
            $ncm = $item['prod']['NCM'] ?? null;
            $itemNum = $index + 1;

            if (! $ncm) {
                $alertas[] = [
                    'categoria' => 'ncm',
                    'nivel' => 'atencao',
                    'codigo' => 'NCM_AUSENTE',
                    'mensagem' => "Item {$itemNum}: NCM nao informado",
                    'detalhe' => 'NCM e obrigatorio para NF-e',
                ];
                $score += 20;
                continue;
            }

            $ncmStr = preg_replace('/[^0-9]/', '', (string) $ncm);
            $ncmsEncontrados[] = $ncmStr;

            // NCMs problematicos (genericos ou invalidos)
            if (in_array($ncmStr, $this->ncmsProblematicos)) {
                $alertas[] = [
                    'categoria' => 'ncm',
                    'nivel' => 'atencao',
                    'codigo' => 'NCM_GENERICO',
                    'mensagem' => "Item {$itemNum}: NCM generico ou invalido",
                    'detalhe' => "NCM {$ncm} e considerado generico",
                ];
                $score += 30;
            }

            // NCM com menos de 8 digitos
            if (strlen($ncmStr) < 8) {
                $alertas[] = [
                    'categoria' => 'ncm',
                    'nivel' => 'atencao',
                    'codigo' => 'NCM_INCOMPLETO',
                    'mensagem' => "Item {$itemNum}: NCM incompleto",
                    'detalhe' => "NCM {$ncm} deve ter 8 digitos",
                ];
                $score += 20;
            }

            // Verificar se NCM de servico em NFe de mercadoria (comeca com 99)
            if (substr($ncmStr, 0, 2) === '99' && $nota->tipo_documento === 'NFE') {
                $alertas[] = [
                    'categoria' => 'ncm',
                    'nivel' => 'info',
                    'codigo' => 'NCM_SERVICO_NFE',
                    'mensagem' => "Item {$itemNum}: NCM de servico em NF-e",
                    'detalhe' => "NCM {$ncm} e tipicamente de servicos",
                ];
                $score += 15;
            }
        }

        return min(100, $score);
    }

    private function validarOperacoes(NotaFiscal $nota, array &$alertas): int
    {
        $score = 0;

        // Verificar emitente
        if ($nota->emit_participante_id) {
            $scoreEmit = ParticipanteScore::where('participante_id', $nota->emit_participante_id)->first();

            if ($scoreEmit) {
                // Participante em lista restritiva (compliance = 100)
                if ($scoreEmit->score_compliance >= 100) {
                    $alertas[] = [
                        'categoria' => 'operacoes',
                        'nivel' => 'bloqueante',
                        'codigo' => 'EMIT_LISTA_RESTRITIVA',
                        'mensagem' => 'Emitente em lista restritiva (CEIS/CNEP/TCU)',
                        'detalhe' => "CNPJ {$nota->emit_cnpj} consta em cadastro de empresas inidoneas",
                    ];
                    $score += 100;
                }

                // Trabalho escravo (ESG = 100)
                if ($scoreEmit->score_esg >= 100) {
                    $alertas[] = [
                        'categoria' => 'operacoes',
                        'nivel' => 'bloqueante',
                        'codigo' => 'EMIT_TRABALHO_ESCRAVO',
                        'mensagem' => 'Emitente em lista de trabalho escravo',
                        'detalhe' => "CNPJ {$nota->emit_cnpj} consta na lista suja",
                    ];
                    $score += 100;
                }

                // Risco critico
                if ($scoreEmit->classificacao === 'critico') {
                    $alertas[] = [
                        'categoria' => 'operacoes',
                        'nivel' => 'atencao',
                        'codigo' => 'EMIT_RISCO_CRITICO',
                        'mensagem' => 'Emitente classificado como risco critico',
                        'detalhe' => "Score de risco: {$scoreEmit->score_total}/100",
                    ];
                    $score += 50;
                }
            }
        }

        // Verificar destinatario (para notas de entrada, o dest pode ser problema tambem)
        if ($nota->dest_participante_id && $nota->tipo_nota === 0) {
            $scoreDest = ParticipanteScore::where('participante_id', $nota->dest_participante_id)->first();

            if ($scoreDest && $scoreDest->score_compliance >= 100) {
                $alertas[] = [
                    'categoria' => 'operacoes',
                    'nivel' => 'atencao',
                    'codigo' => 'DEST_LISTA_RESTRITIVA',
                    'mensagem' => 'Destinatario em lista restritiva',
                    'detalhe' => "CNPJ {$nota->dest_cnpj} em cadastro restritivo",
                ];
                $score += 40;
            }
        }

        return min(100, $score);
    }

    // ============ Metodos auxiliares ============

    private function calcularScoreTotal(array $scores): int
    {
        $total = 0;

        foreach ($this->pesos as $key => $peso) {
            $total += ($scores[$key] ?? 0) * $peso;
        }

        return (int) round($total);
    }

    private function classificar(int $scoreTotal, array $alertas): string
    {
        // Se tem alerta bloqueante, automaticamente e critico ou irregular
        $temBloqueante = collect($alertas)->contains('nivel', 'bloqueante');

        if ($temBloqueante) {
            return $scoreTotal >= 50 ? 'critico' : 'irregular';
        }

        return match (true) {
            $scoreTotal <= 10 => 'conforme',
            $scoreTotal <= 30 => 'atencao',
            $scoreTotal <= 60 => 'irregular',
            default => 'critico',
        };
    }

    private function calcularScoreMedio(array $resultados): float
    {
        if (empty($resultados)) {
            return 0;
        }

        $soma = array_sum(array_column($resultados, 'score_total'));

        return round($soma / count($resultados), 1);
    }
}
