<?php

namespace App\Services\Clearance;

use App\Models\EfdNota;
use App\Models\XmlNota;
use Illuminate\Support\Collection;

class DivergenciaService
{
    public const TOLERANCIA_ABSOLUTA_RUIDO = 5.00;

    public const TOLERANCIA_PERCENTUAL_RUIDO = 0.5;

    public const LIMIAR_CRITICO_ABSOLUTO = 100.00;

    public const LIMIAR_CRITICO_PERCENTUAL = 10.0;

    /**
     * @param  Collection  $snapshots  coleção de nfe_consultas/cte_consultas já formatada por
     *                                 ClearanceController::listarConsultasDfePorLote (inclui
     *                                 chave_acesso, status_label, valor_total, emit/dest, etc.)
     */
    public function analisar(Collection $snapshots, int $userId, int $creditosCobrados): array
    {
        if ($snapshots->isEmpty()) {
            return [
                'veredito' => $this->verediticoVazio(),
                'kpis' => $this->kpisVazios($creditosCobrados),
                'breakdown' => $this->breakdownVazio(),
                'divergencias' => new Collection,
                'sem_divergencia' => new Collection,
                'ruido' => new Collection,
            ];
        }

        $chaves = $snapshots->pluck('chave_acesso')->filter()->unique()->values()->all();
        $declaradoMap = $this->buscarDeclaradoPorChave($userId, $chaves);

        $divergencias = new Collection;
        $semDivergencia = new Collection;
        $ruido = new Collection;

        $kpiEncontradas = 0;
        $kpiCanceladasDeclaradas = 0;
        $kpiDenegadas = 0;
        $kpiInutilizadas = 0;
        $valorCriticoTotal = 0.0;
        $breakdown = $this->breakdownVazio();

        foreach ($snapshots as $snapshot) {
            $chave = $snapshot->chave_acesso ?? null;
            $statusSefaz = strtoupper((string) ($snapshot->status_label ?? $snapshot->status ?? ''));
            $sefazValor = $snapshot->valor_total !== null ? (float) $snapshot->valor_total : null;
            $declarado = $chave !== null && isset($declaradoMap[$chave]) ? $declaradoMap[$chave] : null;
            $declaradoValor = $declarado['valor_total'] ?? null;

            if (! in_array($statusSefaz, ['NAO_ENCONTRADA', 'ERRO_PARAMETRO', 'ERRO_PROVEDOR', 'TIMEOUT'], true)) {
                $kpiEncontradas++;
            }
            if ($statusSefaz === 'CANCELADA' && $declaradoValor !== null && $declaradoValor > 0) {
                $kpiCanceladasDeclaradas++;
            }
            if ($statusSefaz === 'DENEGADA' && $declaradoValor !== null && $declaradoValor > 0) {
                $kpiDenegadas++;
            }
            if ($statusSefaz === 'INUTILIZADA' && $declaradoValor !== null && $declaradoValor > 0) {
                $kpiInutilizadas++;
            }

            $severidade = $this->classificarSeveridade($statusSefaz, $declaradoValor, $sefazValor);
            $sinais = $this->sinaisNovos($snapshot, $declarado, $statusSefaz);
            $severidade = $this->maxSeveridade($severidade, $sinais['piso']);
            $deltaValor = ($declaradoValor !== null && $sefazValor !== null) ? round($sefazValor - $declaradoValor, 2) : 0.0;
            $deltaPct = ($declaradoValor !== null && $declaradoValor > 0 && $sefazValor !== null)
                ? round((($sefazValor - $declaradoValor) / $declaradoValor) * 100, 2)
                : 0.0;

            $tiposLinha = array_values(array_unique(array_merge(
                $this->tiposDivergencia($statusSefaz, $declaradoValor, $severidade),
                $sinais['tipos']
            )));
            $motivos = array_values(array_filter(array_merge(
                $this->motivosStatus($tiposLinha, $statusSefaz, $deltaValor),
                $sinais['motivos']
            )));
            if ($motivos === [] && $severidade === 'ok') {
                $motivos = ['Documento confere com a SEFAZ — sem divergência.'];
            }

            $linha = (object) array_merge((array) $snapshot, [
                'declarado_valor' => $declaradoValor,
                'declarado_valor_label' => $declaradoValor !== null ? 'R$ '.number_format($declaradoValor, 2, ',', '.') : '—',
                'delta_valor' => $deltaValor,
                'delta_valor_label' => 'R$ '.number_format($deltaValor, 2, ',', '.'),
                'delta_percentual' => $deltaPct,
                'delta_percentual_label' => number_format($deltaPct, 1, ',', '.').'%',
                'severidade' => $severidade,
                'tipos_divergencia' => $tiposLinha,
                'motivos' => $motivos,
                'conferencias' => $sinais['conferencias'],
                'declarado_origem' => $declarado['origem'] ?? null,
            ]);

            if ($severidade === 'critica' || $severidade === 'revisar') {
                $divergencias->push($linha);
                if ($severidade === 'critica') {
                    if ($declaradoValor !== null && $sefazValor === null && $statusSefaz === 'NAO_ENCONTRADA') {
                        $valorCriticoTotal += $declaradoValor;
                    } else {
                        $valorCriticoTotal += abs($deltaValor);
                    }
                }
            } elseif ($severidade === 'ruido') {
                $ruido->push($linha);
            } else {
                $semDivergencia->push($linha);
            }

            foreach ($linha->tipos_divergencia as $tipo) {
                if (isset($breakdown[$tipo])) {
                    $breakdown[$tipo]['count']++;
                    $breakdown[$tipo]['valor'] += abs($deltaValor);
                }
            }
        }

        $totalCriticas = $divergencias->where('severidade', 'critica')->count();
        $totalRevisar = $divergencias->where('severidade', 'revisar')->count();
        $valorDivergente = round($valorCriticoTotal, 2);

        $veredito = [
            'severidade' => $totalCriticas > 0 ? 'critica' : ($totalRevisar > 0 ? 'revisar' : 'ok'),
            'total_criticas' => $totalCriticas,
            'total_revisar' => $totalRevisar,
            'valor_divergente' => $valorDivergente,
            'mensagem' => $this->mensagemVeredito($totalCriticas, $totalRevisar, $valorCriticoTotal),
        ];

        return [
            'veredito' => $veredito,
            'kpis' => [
                'existencia' => [
                    'total' => $snapshots->count(),
                    'encontradas' => $kpiEncontradas,
                    'nao_encontradas' => $snapshots->count() - $kpiEncontradas,
                ],
                'status' => [
                    'total' => $snapshots->count(),
                    'canceladas_declaradas' => $kpiCanceladasDeclaradas,
                    'denegadas' => $kpiDenegadas,
                    'inutilizadas' => $kpiInutilizadas,
                ],
                'valor' => [
                    'notas_divergentes' => $totalCriticas,
                    'valor_divergente' => $valorDivergente,
                ],
                'roi' => [
                    'creditos' => $creditosCobrados,
                    'custo_reais' => round($creditosCobrados * 0.20, 2),
                    'exposicao_reais' => $valorDivergente,
                    'multiplicador' => ($creditosCobrados > 0 && $valorDivergente > 0)
                        ? (int) round($valorDivergente / ($creditosCobrados * 0.20))
                        : 0,
                    'total_documentos' => $snapshots->count(),
                    'conformes' => $snapshots->count() - ($totalCriticas + $totalRevisar),
                ],
            ],
            'breakdown' => $breakdown,
            'divergencias' => $divergencias->sortByDesc('severidade')->values(),
            'sem_divergencia' => $semDivergencia->values(),
            'ruido' => $ruido->values(),
        ];
    }

    /** @return array<int, string> */
    private function tiposDivergencia(string $statusSefaz, ?float $declarado, string $severidade): array
    {
        $tipos = [];

        if ($statusSefaz === 'NAO_ENCONTRADA' && $declarado !== null && $declarado > 0) {
            $tipos[] = 'notas_frias';
        }
        if (in_array($statusSefaz, ['CANCELADA', 'DENEGADA', 'INUTILIZADA'], true) && $declarado !== null && $declarado > 0) {
            $tipos[] = 'canceladas_declaradas';
        }
        if ($severidade === 'critica' && ! in_array($statusSefaz, ['NAO_ENCONTRADA', 'CANCELADA', 'DENEGADA', 'INUTILIZADA'], true)) {
            $tipos[] = 'valor_divergente';
        }

        return $tipos;
    }

    private function mensagemVeredito(int $criticas, int $revisar, float $valorDivergente): string
    {
        if ($criticas === 0 && $revisar === 0) {
            return 'Nenhuma divergência acima da tolerância neste lote.';
        }

        $valor = 'R$ '.number_format($valorDivergente, 2, ',', '.');

        if ($criticas > 0) {
            return "{$criticas} ".($criticas === 1 ? 'divergência crítica' : 'divergências críticas')." encontrada(s) — {$valor} em exposição fiscal.";
        }

        return "{$revisar} ".($revisar === 1 ? 'divergência' : 'divergências')." a revisar — {$valor}.";
    }

    /**
     * Sinais novos (Declarado×SEFAZ) por documento → [tipos[], piso, motivos[], conferencias[]].
     * - partes_divergentes: contraparte declarada não aparece em emit/dest do SEFAZ.
     * - operacionais: homologação escriturada (crítica) ou data de emissão divergente (revisar).
     * - conferencias: TODAS as comparações campo-a-campo (CNPJ, razão social, UF, data de emissão),
     *   com veredito por campo — emitidas SEMPRE, mesmo quando conferem, pra o usuário auditar.
     *
     * @return array{tipos: array<int,string>, piso: string, motivos: array<int,string>, conferencias: array<int,array>}
     */
    private function sinaisNovos(object $snapshot, ?array $declarado, string $statusSefaz): array
    {
        $tipos = [];
        $motivos = [];
        $conferencias = [];
        $piso = 'ok';
        $relevante = ! in_array($statusSefaz, ['NAO_ENCONTRADA', 'ERRO_PARAMETRO', 'ERRO_PROVEDOR', 'TIMEOUT'], true);

        // Homologação escriturada → crítica.
        if ($relevante && str_contains(mb_strtoupper((string) ($snapshot->situacao_ambiente ?? '')), 'HOMOLOGA')) {
            $tipos[] = 'operacionais';
            $motivos[] = 'Nota emitida em homologação (ambiente de teste) e escriturada nos livros.';
            $piso = 'critica';
        }

        // ---- CNPJ da contraparte ----
        $contra = $declarado['contraparte_cnpj'] ?? null;
        $cnpjConfirmado = false; // CNPJ da contraparte confere com ALGUM lado do SEFAZ
        $bateEmit = ($relevante && $contra) ? $this->contraparteConfere($contra, (string) ($snapshot->emit_cnpj ?? '')) : null;
        $bateDest = ($relevante && $contra) ? $this->contraparteConfere($contra, (string) ($snapshot->dest_cnpj ?? '')) : null;
        $cnpjConfirmado = ($bateEmit === true || $bateDest === true);
        $cnpjStatus = match (true) {
            ! $declarado || ! $contra => 'sem_dado',
            ! $relevante => 'indeterminado',
            $cnpjConfirmado => 'confere',
            $bateEmit === false && $bateDest === false => 'difere',
            default => 'indeterminado',
        };
        $conferencias[] = [
            'campo' => 'CNPJ contraparte',
            'declarado' => $contra ? $this->fmtCnpj($contra) : '—',
            'sefaz' => $this->ladosSefaz($snapshot->emit_cnpj ?? null, $snapshot->dest_cnpj ?? null, fn ($v) => $this->fmtCnpj(preg_replace('/[^\d*]/', '', (string) $v))),
            'status' => $cnpjStatus,
        ];
        if ($declarado && $relevante && $contra && $bateEmit === false && $bateDest === false) {
            $tipos[] = 'partes_divergentes';
            $motivos[] = 'CNPJ da contraparte na SEFAZ difere do declarado na escrituração.';
            $piso = (($declarado['valor_total'] ?? 0) > 0) ? 'critica' : $this->maxSeveridade($piso, 'revisar');
        }

        // ---- Razão social ----
        // GATE: se o CNPJ já confere com um lado, é a MESMA empresa — nome divergente é drift de
        // cadastro (não divergência). Tolerante à máscara '*' e a sufixo societário (LTDA/ME).
        $nome = $declarado['contraparte_nome'] ?? null;
        $nomeEmit = ($relevante && $nome) ? $this->textoConfere($nome, (string) ($snapshot->emit_nome ?? '')) : null;
        $nomeDest = ($relevante && $nome) ? $this->textoConfere($nome, (string) ($snapshot->dest_nome ?? '')) : null;
        $nomeStatus = match (true) {
            ! $declarado || ! $nome => 'sem_dado',
            ! $relevante => 'indeterminado',
            $nomeEmit === true || $nomeDest === true => 'confere',
            $cnpjConfirmado => 'confere', // mesma empresa pelo CNPJ; nome é só drift
            $nomeEmit === false && $nomeDest === false => 'difere',
            default => 'indeterminado',
        };
        $conferencias[] = [
            'campo' => 'Razão social',
            'declarado' => $nome ?: '—',
            'sefaz' => $this->ladosSefaz($snapshot->emit_nome ?? null, $snapshot->dest_nome ?? null),
            'status' => $nomeStatus,
        ];
        if ($declarado && $relevante && $nome && ! $cnpjConfirmado && $nomeEmit === false && $nomeDest === false) {
            $tipos[] = 'partes_divergentes';
            $motivos[] = 'Razão social da contraparte na SEFAZ difere da declarada na escrituração.';
            $piso = $this->maxSeveridade($piso, 'revisar');
        }

        // ---- UF da contraparte ----
        // Só decide quando AMBOS os lados do SEFAZ têm UF. Mesmo gate do nome (CNPJ confirmado).
        $uf = strtoupper(trim((string) ($declarado['contraparte_uf'] ?? '')));
        $ufEmit = strtoupper(trim((string) ($snapshot->emit_uf ?? '')));
        $ufDest = strtoupper(trim((string) ($snapshot->dest_uf ?? '')));
        $ufComparavel = $relevante && $uf !== '' && $ufEmit !== '' && $ufDest !== '';
        $ufDifere = $ufComparavel && $uf !== $ufEmit && $uf !== $ufDest;
        $ufStatus = match (true) {
            ! $declarado || $uf === '' => 'sem_dado',
            ! $relevante => 'indeterminado',
            $cnpjConfirmado => 'confere', // CNPJ confirma a identidade → UF é da mesma empresa
            ! $ufComparavel => 'indeterminado',
            $ufDifere => 'difere',
            default => 'confere',
        };
        $conferencias[] = [
            'campo' => 'UF',
            'declarado' => $uf !== '' ? $uf : '—',
            'sefaz' => $this->ladosSefaz($ufEmit ?: null, $ufDest ?: null),
            'status' => $ufStatus,
        ];
        if ($declarado && ! $cnpjConfirmado && $ufDifere) {
            $tipos[] = 'partes_divergentes';
            $motivos[] = "UF da contraparte na SEFAZ ({$ufEmit}/{$ufDest}) difere da declarada ({$uf}).";
            $piso = $this->maxSeveridade($piso, 'revisar');
        }

        // ---- Inscrição Estadual ----
        // IE só existe no cadastro se veio do SINTEGRA (fonte paga). O SEFAZ só informa a IE do
        // EMITENTE (emit_ie) — não há IE do destinatário. Logo só dá pra confrontar quando a
        // contraparte declarada É o emitente ($bateEmit === true). Read-only: não dispara SINTEGRA.
        $ie = trim((string) ($declarado['contraparte_ie'] ?? ''));
        $emitIe = trim((string) ($snapshot->emit_ie ?? ''));
        $ieNota = null;
        $ieStatus = match (true) {
            ! $declarado || $ie === '' => 'sem_dado',
            ! $relevante => 'indeterminado',
            $bateEmit !== true || $emitIe === '' => 'indeterminado',
            preg_replace('/\D/', '', $ie) === preg_replace('/\D/', '', $emitIe) => 'confere',
            default => 'difere',
        };
        if ($ieStatus === 'sem_dado') {
            $ieNota = 'requer consulta SINTEGRA (plano Licitação+)';
        } elseif ($ieStatus === 'indeterminado' && $ie !== '' && $bateEmit !== true) {
            $ieNota = 'SEFAZ só informa IE do emitente';
        }
        $conferencias[] = [
            'campo' => 'Inscrição Estadual',
            'declarado' => $ie !== '' ? $ie : '—',
            'sefaz' => $emitIe !== '' ? $emitIe : '—',
            'status' => $ieStatus,
            'nota' => $ieNota,
            // Alvo do botão SINTEGRA (só faz sentido quando falta IE e há participante real).
            'participante_id' => $ieStatus === 'sem_dado' ? ($declarado['contraparte_participante_id'] ?? null) : null,
        ];
        if ($ieStatus === 'difere') {
            $tipos[] = 'operacionais';
            $motivos[] = "Inscrição Estadual do emitente na SEFAZ ({$emitIe}) difere da declarada ({$ie}).";
            $piso = $this->maxSeveridade($piso, 'revisar');
        }

        // ---- Data de emissão (por dia) ----
        $dDecl = $declarado['data_emissao'] ?? null;
        $dSefaz = $snapshot->data_emissao ? substr((string) $snapshot->data_emissao, 0, 10) : null;
        $dataStatus = match (true) {
            ! $declarado || ! $dDecl => 'sem_dado',
            ! $relevante || ! $dSefaz => 'indeterminado',
            $dDecl === $dSefaz => 'confere',
            default => 'difere',
        };
        $conferencias[] = [
            'campo' => 'Data de emissão',
            'declarado' => $dDecl ? $this->fmtData($dDecl) : '—',
            'sefaz' => $dSefaz ? $this->fmtData($dSefaz) : '—',
            'status' => $dataStatus,
        ];
        if ($declarado && $relevante && $dDecl && $dSefaz && $dDecl !== $dSefaz) {
            $tipos[] = 'operacionais';
            $motivos[] = "Data de emissão na SEFAZ ({$dSefaz}) difere da declarada ({$dDecl}).";
            $piso = $this->maxSeveridade($piso, 'revisar');
        }

        // ---- Número / Série / Modelo ----
        // A chave de acesso (44 díg.) CODIFICA modelo/série/número — fonte canônica, disponível
        // mesmo sem SEFAZ. SEFAZ tem precedência; cai pra chave quando o snapshot não traz o campo.
        // Divergência = metadado declarado aponta pra uma chave que não é aquele documento.
        $chaveParts = $this->extrairDaChave((string) ($snapshot->chave_acesso ?? ''));

        $sefazNumero = ($snapshot->numero ?? null) !== null && (string) $snapshot->numero !== ''
            ? (string) $snapshot->numero : ($chaveParts['numero'] ?? null);
        $sefazSerie = ($snapshot->serie ?? null) !== null && (string) $snapshot->serie !== ''
            ? (string) $snapshot->serie : ($chaveParts['serie'] ?? null);
        $sefazModelo = ($snapshot->modelo ?? null) !== null && (string) $snapshot->modelo !== ''
            ? (string) $snapshot->modelo : ($chaveParts['modelo'] ?? null);

        $declNumero = $declarado['numero'] ?? null;
        $declSerie = $declarado['serie'] ?? null;
        $declModelo = $declarado['modelo'] ?? null;

        // Número + Série num card só (identidade sequencial do documento).
        $numSerieDecl = $declNumero !== null ? ('Nº '.$declNumero.($declSerie !== null ? ' / Série '.$declSerie : '')) : '—';
        $numSerieSefaz = $sefazNumero !== null ? ('Nº '.$sefazNumero.($sefazSerie !== null ? ' / Série '.$sefazSerie : '')) : '—';
        $numDifere = $declNumero !== null && $sefazNumero !== null && $this->normNum($declNumero) !== $this->normNum($sefazNumero);
        $serieDifere = $declSerie !== null && $sefazSerie !== null && $this->normNum($declSerie) !== $this->normNum($sefazSerie);
        $numSerieStatus = match (true) {
            ! $declarado || $declNumero === null => 'sem_dado',
            $sefazNumero === null => 'indeterminado',
            $numDifere || $serieDifere => 'difere',
            default => 'confere',
        };
        $conferencias[] = [
            'campo' => 'Número / Série',
            'declarado' => $numSerieDecl,
            'sefaz' => $numSerieSefaz,
            'status' => $numSerieStatus,
        ];
        if ($numSerieStatus === 'difere') {
            $tipos[] = 'operacionais';
            $motivos[] = "Número/série declarado ({$numSerieDecl}) difere do documento na chave/SEFAZ ({$numSerieSefaz}).";
            $piso = $this->maxSeveridade($piso, 'revisar');
        }

        // Modelo (55 NF-e / 57 CT-e / 65 NFC-e).
        $modeloDifere = $declModelo !== null && $sefazModelo !== null && $this->normNum($declModelo) !== $this->normNum($sefazModelo);
        $modeloStatus = match (true) {
            ! $declarado || $declModelo === null => 'sem_dado',
            $sefazModelo === null => 'indeterminado',
            $modeloDifere => 'difere',
            default => 'confere',
        };
        $conferencias[] = [
            'campo' => 'Modelo',
            'declarado' => $declModelo ?? '—',
            'sefaz' => $sefazModelo ?? '—',
            'status' => $modeloStatus,
        ];
        if ($modeloStatus === 'difere') {
            $tipos[] = 'operacionais';
            $motivos[] = "Modelo declarado ({$declModelo}) difere do documento na chave/SEFAZ ({$sefazModelo}).";
            $piso = $this->maxSeveridade($piso, 'revisar');
        }

        return [
            'tipos' => array_values(array_unique($tipos)),
            'piso' => $piso,
            'motivos' => $motivos,
            'conferencias' => $conferencias,
        ];
    }

    /** Formata "E: <emit> · D: <dest>" pulando lados vazios; formatter opcional por valor. */
    private function ladosSefaz($emit, $dest, ?callable $fmt = null): string
    {
        $fmt ??= fn ($v) => trim((string) $v);
        $partes = [];
        $e = $emit !== null && trim((string) $emit) !== '' ? $fmt($emit) : null;
        $d = $dest !== null && trim((string) $dest) !== '' ? $fmt($dest) : null;
        if ($e) {
            $partes[] = "E: {$e}";
        }
        if ($d) {
            $partes[] = "D: {$d}";
        }

        return $partes === [] ? '—' : implode(' · ', $partes);
    }

    /** Formata CNPJ de 14 dígitos; mantém como está se tiver máscara '*' ou tamanho inesperado. */
    private function fmtCnpj(string $c): string
    {
        if (strlen($c) !== 14 || str_contains($c, '*')) {
            return $c !== '' ? $c : '—';
        }

        return substr($c, 0, 2).'.'.substr($c, 2, 3).'.'.substr($c, 5, 3).'/'.substr($c, 8, 4).'-'.substr($c, 12, 2);
    }

    /** ISO 'YYYY-MM-DD' → 'DD/MM/YYYY'; devolve original se não casar. */
    private function fmtData(string $d): string
    {
        return preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $d, $m) ? "{$m[3]}/{$m[2]}/{$m[1]}" : $d;
    }

    /**
     * Decompõe a chave de acesso NF-e/CT-e (44 díg.) nos campos que ela codifica:
     * cUF(2) AAMM(4) CNPJ(14) modelo(2) série(3) número(9) tpEmis(1) cNF(8) DV(1).
     * Devolve valores sem zeros à esquerda (número/série). Chave inválida → [].
     *
     * @return array{cuf?:string, aamm?:string, emit_cnpj?:string, modelo?:string, serie?:string, numero?:string}
     */
    private function extrairDaChave(string $chave): array
    {
        $c = preg_replace('/\D/', '', $chave);
        if (strlen((string) $c) !== 44) {
            return [];
        }

        return [
            'cuf' => substr($c, 0, 2),
            'aamm' => substr($c, 2, 4),
            'emit_cnpj' => substr($c, 6, 14),
            'modelo' => ltrim(substr($c, 20, 2), '0') ?: '0',
            'serie' => ltrim(substr($c, 22, 3), '0') ?: '0',
            'numero' => ltrim(substr($c, 25, 9), '0') ?: '0',
        ];
    }

    /** Normaliza número/série/modelo para comparação: só dígitos, sem zeros à esquerda. */
    private function normNum(string $v): string
    {
        $d = preg_replace('/\D/', '', $v);

        return ltrim((string) $d, '0') ?: '0';
    }

    /**
     * Justificativas legíveis dos sinais baseados em status/valor (fria, cancelada, valor).
     *
     * @param  array<int,string>  $tipos
     * @return array<int,string>
     */
    private function motivosStatus(array $tipos, string $statusSefaz, float $deltaValor): array
    {
        $motivos = [];

        if (in_array('notas_frias', $tipos, true)) {
            $motivos[] = 'Declarada na escrituração mas NÃO encontrada na SEFAZ (possível nota fria).';
        }
        if (in_array('canceladas_declaradas', $tipos, true)) {
            $rotulo = match ($statusSefaz) {
                'CANCELADA' => 'cancelada',
                'DENEGADA' => 'denegada',
                'INUTILIZADA' => 'inutilizada',
                default => 'sem validade',
            };
            $motivos[] = "Documento {$rotulo} na SEFAZ, mas escriturado nos livros.";
        }
        if (in_array('valor_divergente', $tipos, true)) {
            $motivos[] = 'Valor na SEFAZ difere do declarado (Δ R$ '.number_format($deltaValor, 2, ',', '.').').';
        }

        return $motivos;
    }

    /**
     * Compara o CNPJ da contraparte declarada com um lado (emit/dest) do SEFAZ, tolerando as
     * duas máscaras da consulta sem certificado: '*' posicional (ex.: 12.***.***\/0001-**) e
     * zeros à esquerda no destinatário. Compara só os DÍGITOS VISÍVEIS por posição.
     *
     * @return bool|null true = confere; false = difere (comparação confiável); null = indeterminado
     *                   (lado vazio ou mascarado sem dígitos visíveis suficientes)
     */
    public function contraparteConfere(string $contra, string $ladoSefaz): ?bool
    {
        // Mantém dígitos E '*' (máscara posicional), descarta só pontuação de formatação.
        $lado = preg_replace('/[^\d*]/', '', $ladoSefaz);

        if ($lado === '') {
            return null;
        }

        // Máscara com '*': alinha por posição, ignora as casas mascaradas. Exige >=6 dígitos
        // visíveis pra ser uma comparação confiável.
        if (str_contains($lado, '*')) {
            if (strlen($lado) !== 14 || strlen($contra) !== 14) {
                return null; // sem alinhamento confiável de 14 casas
            }
            $visiveis = 0;
            for ($i = 0; $i < 14; $i++) {
                if ($lado[$i] === '*') {
                    continue;
                }
                $visiveis++;
                if ($lado[$i] !== $contra[$i]) {
                    return false; // dígito visível diverge
                }
            }

            return $visiveis >= 6 ? true : null;
        }

        if (strlen($lado) !== 14) {
            return null;
        }
        if ($contra === $lado) {
            return true;
        }
        // Mascarado com zeros à esquerda (≥4): compara só a parte visível (sufixo).
        if (preg_match('/^0{4,}/', $lado)) {
            $visivel = ltrim($lado, '0');

            return strlen($visivel) >= 6 ? str_ends_with($contra, $visivel) : null;
        }

        return false; // comparação limpa e diferente
    }

    /**
     * Compara razão social declarada × lado (emit/dest) do SEFAZ, tolerando a máscara de '*'
     * do InfoSimples sem certificado e ruído de acento/pontuação/sufixo societário.
     *
     * @return bool|null true = confere; false = difere (comparação confiável); null = indeterminado
     *                   (lado vazio ou mascarado sem núcleo comparável)
     */
    public function textoConfere(string $declarado, string $ladoSefaz): ?bool
    {
        $lado = trim($ladoSefaz);
        if ($lado === '') {
            return null;
        }

        // Mascarado (tem '*'): compara só os blocos alfanuméricos visíveis. Se sobrar núcleo
        // curto demais, é indeterminado.
        if (str_contains($lado, '*')) {
            $visivel = $this->normalizarNome(str_replace('*', ' ', $lado));
            if (strlen($visivel) < 4) {
                return null;
            }
            $decl = $this->normalizarNome($declarado);

            return str_contains($decl, $visivel) ? true : false;
        }

        $a = $this->normalizarNome($declarado);
        $b = $this->normalizarNome($lado);
        if ($a === '' || $b === '') {
            return null;
        }

        // Um contém o outro (sufixo LTDA/ME/EIRELI, abreviação) → confere.
        return (str_contains($a, $b) || str_contains($b, $a)) ? true : false;
    }

    /** Normaliza nome para comparação: maiúsculas, sem acento, só [A-Z0-9], sufixos societários fora. */
    private function normalizarNome(string $nome): string
    {
        $s = mb_strtoupper($nome, 'UTF-8');
        $s = strtr($s, [
            'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A', 'Ä' => 'A',
            'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'È' => 'E',
            'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ì' => 'I',
            'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ò' => 'O',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ù' => 'U', 'Ç' => 'C',
        ]);
        $s = preg_replace('/\b(LTDA|ME|EPP|EIRELI|SA|S\/A|MEI|CIA)\b/', '', $s);
        $s = preg_replace('/[^A-Z0-9]/', '', (string) $s);

        return (string) $s;
    }

    private function maxSeveridade(string $a, string $b): string
    {
        $ordem = ['ok' => 0, 'ruido' => 1, 'revisar' => 2, 'critica' => 3];

        return ($ordem[$a] ?? 0) >= ($ordem[$b] ?? 0) ? $a : $b;
    }

    /**
     * Retorna map chave_acesso => ['valor_total','contraparte_cnpj','data_emissao','origem','id'].
     * `contraparte_cnpj` = CNPJ da contraparte declarada (participante na EFD; lado sem cliente_id
     * no XML). XML tem precedência sobre EFD quando ambos existem.
     */
    /**
     * Auditoria Declarado × SEFAZ de UM documento (tela de detalhe da nota).
     *
     * Mesma engine do lote (`sinaisNovos`): confere a CONTRAPARTE contra emit E dest do SEFAZ
     * — NÃO assume emit=emit, porque a orientação emitente/destinatário pode inverter e um dos
     * lados vem mascarado na consulta sem certificado. Gate de identidade: se o CNPJ confere com
     * algum lado, nome/UF divergentes viram drift de cadastro, não divergência. Emite TODAS as
     * conferências (mesmo as que conferem) para o usuário auditar campo a campo.
     *
     * @return array{severidade:string, delta_valor:float, delta_percentual:float,
     *   declarado_valor:?float, sefaz_valor:?float, motivos:array<int,string>,
     *   conferencias:array<int,array>, declarado_origem:?string, status:string}
     */
    public function auditarUmDocumento(int $userId, object $snapshot): array
    {
        $chave = $snapshot->chave_acesso ?? null;
        $statusSefaz = strtoupper((string) ($snapshot->status_label ?? $snapshot->status ?? ''));
        // CT-e não tem valor_total — cai no valor da prestação.
        $sefazValor = $snapshot->valor_total ?? ($snapshot->valor_prestacao ?? null);
        $sefazValor = $sefazValor !== null ? (float) $sefazValor : null;

        $declaradoMap = $chave ? $this->buscarDeclaradoPorChave($userId, [$chave]) : [];
        $declarado = ($chave && isset($declaradoMap[$chave])) ? $declaradoMap[$chave] : null;
        $declaradoValor = $declarado['valor_total'] ?? null;

        $severidade = $this->classificarSeveridade($statusSefaz, $declaradoValor, $sefazValor);
        $sinais = $this->sinaisNovos($snapshot, $declarado, $statusSefaz);
        $severidade = $this->maxSeveridade($severidade, $sinais['piso']);

        $deltaValor = ($declaradoValor !== null && $sefazValor !== null) ? round($sefazValor - $declaradoValor, 2) : 0.0;
        $deltaPct = ($declaradoValor !== null && $declaradoValor > 0 && $sefazValor !== null)
            ? round((($sefazValor - $declaradoValor) / $declaradoValor) * 100, 2) : 0.0;

        $tipos = array_values(array_unique(array_merge(
            $this->tiposDivergencia($statusSefaz, $declaradoValor, $severidade),
            $sinais['tipos']
        )));
        $motivos = array_values(array_filter(array_merge(
            $this->motivosStatus($tipos, $statusSefaz, $deltaValor),
            $sinais['motivos']
        )));
        if ($motivos === [] && $severidade === 'ok') {
            $motivos = ['Documento confere com a SEFAZ — sem divergência.'];
        }

        return [
            'severidade' => $severidade,
            'delta_valor' => $deltaValor,
            'delta_percentual' => $deltaPct,
            'declarado_valor' => $declaradoValor,
            'sefaz_valor' => $sefazValor,
            'motivos' => $motivos,
            'conferencias' => $sinais['conferencias'],
            'declarado_origem' => $declarado['origem'] ?? null,
            'status' => $statusSefaz,
        ];
    }

    public function buscarDeclaradoPorChave(int $userId, array $chaves): array
    {
        if (empty($chaves)) {
            return [];
        }

        $map = [];

        EfdNota::query()
            ->leftJoin('participantes', 'participantes.id', '=', 'efd_notas.participante_id')
            ->where('efd_notas.user_id', $userId)
            ->whereIn('efd_notas.chave_acesso', $chaves)
            ->get([
                'efd_notas.id', 'efd_notas.chave_acesso', 'efd_notas.valor_total',
                'efd_notas.data_emissao', 'efd_notas.numero', 'efd_notas.serie', 'efd_notas.modelo',
                'efd_notas.participante_id as contraparte_participante_id',
                'participantes.documento as contraparte_cnpj',
                'participantes.razao_social as contraparte_razao', 'participantes.nome_fantasia as contraparte_fantasia',
                'participantes.uf as contraparte_uf', 'participantes.inscricao_estadual as contraparte_ie',
            ])
            ->each(function ($nota) use (&$map) {
                $map[$nota->chave_acesso] = [
                    'valor_total' => (float) $nota->valor_total,
                    'contraparte_cnpj' => $nota->contraparte_cnpj ? preg_replace('/\D/', '', $nota->contraparte_cnpj) : null,
                    'contraparte_nome' => $nota->contraparte_razao ?: ($nota->contraparte_fantasia ?: null),
                    'contraparte_uf' => $nota->contraparte_uf ?: null,
                    'contraparte_ie' => $nota->contraparte_ie ?: null,
                    'contraparte_participante_id' => $nota->contraparte_participante_id ?: null,
                    'data_emissao' => $nota->data_emissao ? substr((string) $nota->data_emissao, 0, 10) : null,
                    'numero' => $nota->numero !== null ? (string) $nota->numero : null,
                    'serie' => $nota->serie !== null && $nota->serie !== '' ? (string) $nota->serie : null,
                    'modelo' => $nota->modelo !== null && $nota->modelo !== '' ? (string) $nota->modelo : null,
                    'origem' => 'efd',
                    'id' => $nota->id,
                ];
            });

        XmlNota::query()
            ->where('user_id', $userId)
            ->whereIn('chave_acesso', $chaves)
            ->get(['id', 'chave_acesso', 'valor_total', 'data_emissao', 'emit_documento', 'dest_documento', 'emit_cliente_id', 'dest_cliente_id', 'emit_razao_social', 'dest_razao_social', 'emit_uf', 'dest_uf', 'emit_ie', 'dest_ie', 'emit_participante_id', 'dest_participante_id', 'numero_documento', 'serie', 'modelo'])
            ->each(function ($nota) use (&$map) {
                // contraparte = lado SEM cliente_id (o outro lado é a empresa do usuário).
                $ladoDest = (bool) $nota->emit_cliente_id;
                $contraparte = $ladoDest ? $nota->dest_documento : $nota->emit_documento;
                $map[$nota->chave_acesso] = [
                    'valor_total' => (float) $nota->valor_total,
                    'contraparte_cnpj' => $contraparte ? preg_replace('/\D/', '', $contraparte) : null,
                    'contraparte_nome' => ($ladoDest ? $nota->dest_razao_social : $nota->emit_razao_social) ?: null,
                    'contraparte_uf' => ($ladoDest ? $nota->dest_uf : $nota->emit_uf) ?: null,
                    'contraparte_ie' => ($ladoDest ? $nota->dest_ie : $nota->emit_ie) ?: null,
                    'contraparte_participante_id' => ($ladoDest ? $nota->dest_participante_id : $nota->emit_participante_id) ?: null,
                    'data_emissao' => $nota->data_emissao ? substr((string) $nota->data_emissao, 0, 10) : null,
                    'numero' => $nota->numero_documento !== null ? (string) $nota->numero_documento : null,
                    'serie' => $nota->serie !== null && $nota->serie !== '' ? (string) $nota->serie : null,
                    'modelo' => $nota->modelo !== null && $nota->modelo !== '' ? (string) $nota->modelo : null,
                    'origem' => 'xml',
                    'id' => $nota->id,
                ];
            });

        return $map;
    }

    private function verediticoVazio(): array
    {
        return [
            'severidade' => 'ok',
            'total_criticas' => 0,
            'total_revisar' => 0,
            'valor_divergente' => 0.0,
            'mensagem' => 'Sem documentos auditados neste lote.',
        ];
    }

    private function kpisVazios(int $creditosCobrados): array
    {
        return [
            'existencia' => ['total' => 0, 'encontradas' => 0, 'nao_encontradas' => 0],
            'status' => ['total' => 0, 'canceladas_declaradas' => 0, 'denegadas' => 0, 'inutilizadas' => 0],
            'valor' => ['notas_divergentes' => 0, 'valor_divergente' => 0.0],
            'roi' => [
                'creditos' => $creditosCobrados,
                'custo_reais' => round($creditosCobrados * 0.20, 2),
                'exposicao_reais' => 0.0,
                'multiplicador' => 0,
                'total_documentos' => 0,
                'conformes' => 0,
            ],
        ];
    }

    private function breakdownVazio(): array
    {
        return [
            'notas_frias' => ['count' => 0, 'valor' => 0.0],
            'canceladas_declaradas' => ['count' => 0, 'valor' => 0.0],
            'valor_divergente' => ['count' => 0, 'valor' => 0.0],
            'partes_divergentes' => ['count' => 0, 'valor' => 0.0],
            'operacionais' => ['count' => 0, 'valor' => 0.0],
        ];
    }

    /**
     * @return 'critica'|'revisar'|'ruido'|'ok'
     */
    public function classificarSeveridade(string $statusSefaz, ?float $declarado, ?float $sefaz): string
    {
        $status = strtoupper($statusSefaz);

        if ($declarado !== null && $declarado > 0 && in_array($status, ['CANCELADA', 'DENEGADA', 'INUTILIZADA'], true)) {
            return 'critica';
        }

        if ($declarado !== null && $declarado > 0 && $status === 'NAO_ENCONTRADA') {
            return 'critica';
        }

        if ($declarado === null || $sefaz === null) {
            return 'ok';
        }

        $delta = abs($sefaz - $declarado);
        $deltaPct = $declarado > 0 ? ($delta / $declarado) * 100 : 0;

        if ($delta <= self::TOLERANCIA_ABSOLUTA_RUIDO || $deltaPct <= self::TOLERANCIA_PERCENTUAL_RUIDO) {
            return $delta === 0.0 ? 'ok' : 'ruido';
        }

        if ($delta > self::LIMIAR_CRITICO_ABSOLUTO && $deltaPct > self::LIMIAR_CRITICO_PERCENTUAL) {
            return 'critica';
        }

        return 'revisar';
    }
}
