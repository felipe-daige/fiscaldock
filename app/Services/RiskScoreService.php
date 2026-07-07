<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Participante;
use App\Models\ParticipanteScore;
use App\Support\CertidaoBadge;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RiskScoreService
{
    /**
     * Pesos por categoria. A soma NÃO precisa dar 1.0 — o total é renormalizado
     * dinamicamente sobre as categorias efetivamente avaliadas (ver calcularScoreTotal).
     * Para reintroduzir uma categoria, basta adicionar o peso aqui (a renormalização
     * cuida do resto).
     */
    private array $pesos = [
        'cadastral' => 0.15,
        'cnd_federal' => 0.20,
        'cnd_estadual' => 0.15,
        'fgts' => 0.10,
        'trabalhista' => 0.10,
    ];

    /**
     * Penalidade (subscore de risco) aplicada quando a fonte aponta IRREGULAR.
     * Regular = 0; INDETERMINADO / não consultado = null (não avaliado, fora do cálculo).
     */
    private array $penalidadeIrregular = [
        'cnd_federal' => 70,
        'cnd_estadual' => 70,
        'fgts' => 50,
        'trabalhista' => 40,
    ];

    /**
     * Gravidade de uma certidão de regularidade quando IRREGULAR (Positiva). Fonte única
     * consumida pelo piso de classificação (aqui) e pela severidade do alerta de certidão
     * positiva (AlertaCentralService). Sem um piso, uma única certidão positiva era diluída
     * pela média ponderada e classificava o fornecedor como "baixo risco" — ver
     * docs/score-fiscal/README.md.
     *
     * @var array<string, array{severidade: string, piso: string}>
     */
    public const GRAVIDADE_CERTIDAO = [
        'cnd_federal' => ['severidade' => 'alta', 'piso' => 'alto'],
        'cnd_estadual' => ['severidade' => 'alta', 'piso' => 'alto'],
        'fgts' => ['severidade' => 'media', 'piso' => 'medio'],
        'trabalhista' => ['severidade' => 'media', 'piso' => 'medio'],
    ];

    /** Severidade de classificação por ordem crescente — base do "maior vence" no piso. */
    private const RANK_CLASSIFICACAO = [
        'nao_avaliado' => 0,
        'inconclusivo' => 1,
        'baixo' => 2,
        'medio' => 3,
        'alto' => 4,
        'critico' => 5,
    ];

    /**
     * Subscores por categoria a partir do `resultado_dados` (shape aninhado real).
     * Cada valor é 0..100 (0 = ótimo, 100 = pior) OU null = não avaliado.
     *
     * @param  array  $dados  Conteúdo de consulta_resultados.resultado_dados
     * @return array<string, int|null>
     */
    public function calcularScores(array $dados): array
    {
        return [
            'cadastral' => $this->scoreCadastral($dados),
            'cnd_federal' => $this->subscoreCertidao($dados['cnd_federal'] ?? null, $this->penalidadeIrregular['cnd_federal']),
            'cnd_estadual' => $this->subscoreCertidao($dados['cnd_estadual'] ?? null, $this->penalidadeIrregular['cnd_estadual']),
            'fgts' => $this->subscoreCertidao($dados['crf_fgts'] ?? $dados['fgts'] ?? null, $this->penalidadeIrregular['fgts']),
            'trabalhista' => $this->subscoreCertidao($dados['cndt'] ?? null, $this->penalidadeIrregular['trabalhista']),
        ];
    }

    /**
     * Total ponderado com renormalização dinâmica: pondera apenas as categorias avaliadas
     * (não-null), normalizando os pesos para somar 1.0 sobre elas. Nenhuma avaliada → null.
     *
     * @param  array<string, int|null>  $scores
     */
    public function calcularScoreTotal(array $scores): ?int
    {
        $somaPesos = 0.0;
        $acumulado = 0.0;

        foreach ($this->pesos as $categoria => $peso) {
            $valor = $scores[$categoria] ?? null;
            if ($valor === null) {
                continue;
            }
            $acumulado += $valor * $peso;
            $somaPesos += $peso;
        }

        if ($somaPesos <= 0) {
            return null;
        }

        return (int) round($acumulado / $somaPesos);
    }

    /**
     * Classifica o risco baseado no score total. Total null = nada avaliado.
     */
    public function classificar(?int $scoreTotal): string
    {
        if ($scoreTotal === null) {
            return 'nao_avaliado';
        }

        return match (true) {
            $scoreTotal <= 20 => 'baixo',
            $scoreTotal <= 50 => 'medio',
            $scoreTotal <= 80 => 'alto',
            default => 'critico',
        };
    }

    /** Certidões de regularidade que compõem o score (cadastral NÃO é certidão). */
    private const CERTIDOES_SCORE = ['cnd_federal', 'cnd_estadual', 'fgts', 'trabalhista'];

    /**
     * Cobertura mínima p/ o score ser conclusivo: CND Federal avaliada + ao menos 2 certidões
     * de regularidade avaliadas. Cadastral sozinho (ou sem a federal) não basta — ver
     * classificarComCobertura.
     *
     * @param  array<string, int|null>  $scores
     */
    public function coberturaSuficiente(array $scores): bool
    {
        if (($scores['cnd_federal'] ?? null) === null) {
            return false;
        }

        $certAvaliadas = 0;
        foreach (self::CERTIDOES_SCORE as $c) {
            if (($scores[$c] ?? null) !== null) {
                $certAvaliadas++;
            }
        }

        return $certAvaliadas >= 2;
    }

    /**
     * Classificação com limiar de cobertura: 'nao_avaliado' se nada foi avaliado; 'inconclusivo'
     * se algo foi avaliado mas sem cobertura mínima (ex.: só cadastro, ou sem CND Federal + 2
     * certidões) — evita dizer "Baixo Risco" sem regularidade fiscal consultada; caso contrário,
     * a classificação numérica normal.
     *
     * @param  array<string, int|null>  $scores
     */
    public function classificarComCobertura(array $scores): string
    {
        // Piso por certidão irregular (Positiva) vence a média ponderada e a cobertura:
        // um débito conhecido nunca é "baixo risco" nem "inconclusivo".
        $piso = $this->pisoPorCertidoes($scores);
        $total = $this->calcularScoreTotal($scores);

        if ($total === null) {
            // Nada ponderável avaliado; se algo irregular foi avaliado, o piso ainda vale.
            return $piso ?? 'nao_avaliado';
        }

        $base = $this->coberturaSuficiente($scores) ? $this->classificar($total) : 'inconclusivo';

        if ($piso !== null && self::RANK_CLASSIFICACAO[$piso] > self::RANK_CLASSIFICACAO[$base]) {
            return $piso;
        }

        return $base;
    }

    /**
     * Piso de classificação derivado das certidões/situação IRREGULARES avaliadas.
     * Retorna a classificação mínima ('critico'|'alto'|'medio') ou null se nada irregular.
     * Cadastral INAPTA/BAIXADA/NULA (100) → 'critico'; SUSPENSA (50) → 'alto'.
     *
     * @param  array<string, int|null>  $scores
     */
    public function pisoPorCertidoes(array $scores): ?string
    {
        $piso = null;
        $aplicar = function (string $candidato) use (&$piso) {
            if ($piso === null || self::RANK_CLASSIFICACAO[$candidato] > self::RANK_CLASSIFICACAO[$piso]) {
                $piso = $candidato;
            }
        };

        $cad = $scores['cadastral'] ?? null;
        if ($cad !== null && $cad >= 100) {
            $aplicar('critico');
        } elseif ($cad !== null && $cad >= 50) {
            $aplicar('alto');
        }

        // Subscore de certidão: 0 = regular, penalidade (>0) = irregular, null = não avaliado.
        foreach (self::GRAVIDADE_CERTIDAO as $categoria => $gravidade) {
            $valor = $scores[$categoria] ?? null;
            if ($valor !== null && $valor > 0) {
                $aplicar($gravidade['piso']);
            }
        }

        return $piso;
    }

    /**
     * Eixo CRÉDITO IBS/CBS (Reforma Tributária) — ortogonal ao score de conformidade.
     * Fração do imposto que o regime do fornecedor permite virar crédito para o comprador:
     * 1.0 = crédito integral (Regime Normal) | reduzido = Simples sem opção (config) |
     * 0.0 = MEI | null = regime não identificado.
     * Espelha a precedência de ConsultaResultado::getRegimeTributarioLabel().
     */
    public function fatorCreditoRegime(?Model $alvo, array $dados): ?float
    {
        return match ($this->categoriaRegime($dados, $alvo)) {
            'normal' => 1.0,
            'simples' => (float) config('reforma.fator_simples_sem_opcao'),
            'mei' => 0.0,
            default => null,
        };
    }

    /**
     * Sub-score de crédito IBS/CBS (0-100): 0 = gera crédito integral, 100 = não gera.
     * null = regime não identificado. Eixo separado — NÃO entra em calcularScoreTotal().
     */
    public function scoreCreditoReforma(array $dados, ?Model $alvo = null): ?int
    {
        $fator = $this->fatorCreditoRegime($alvo, $dados);

        return $fator === null ? null : (int) round((1 - $fator) * 100);
    }

    /** Categoria de regime do fornecedor: 'normal' | 'simples' | 'mei' | null. MEI sempre vence Simples. */
    private function categoriaRegime(array $dados, ?Model $alvo): ?string
    {
        $texto = $this->primeiroRegimeTexto([$dados['regime_tributario'] ?? null, $alvo?->regime_tributario]);

        if ($this->flagVerdadeira($dados['mei'] ?? null) || $this->regimeContem($texto, 'mei')) {
            return 'mei';
        }

        if ($this->flagVerdadeira($dados['simples_nacional'] ?? null) || $this->regimeContem($texto, 'simples')) {
            return 'simples';
        }

        $crt = $this->parseCrt($dados['crt'] ?? null) ?? $this->parseCrt($alvo?->crt);
        if ($crt !== null) {
            return $crt === 3 ? 'normal' : 'simples';
        }

        if ($this->regimeContem($texto, 'normal', 'lucro', 'presumido', 'real')) {
            return 'normal';
        }

        return null;
    }

    private function primeiroRegimeTexto(array $candidatos): ?string
    {
        foreach ($candidatos as $candidato) {
            if (is_string($candidato) && trim($candidato) !== '') {
                return $candidato;
            }
        }

        return null;
    }

    private function regimeContem(?string $texto, string ...$agulhas): bool
    {
        if ($texto === null) {
            return false;
        }

        $texto = mb_strtolower($texto);

        foreach ($agulhas as $agulha) {
            if (str_contains($texto, $agulha)) {
                return true;
            }
        }

        return false;
    }

    private function flagVerdadeira(mixed $valor): bool
    {
        if (is_bool($valor)) {
            return $valor;
        }

        return in_array($valor, [1, '1', 'true', 'sim', 'S'], true);
    }

    /** CRT (Código de Regime Tributário) válido: 1/2 = Simples, 3 = Regime Normal. */
    private function parseCrt(mixed $valor): ?int
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        $crt = (int) $valor;

        return in_array($crt, [1, 2, 3], true) ? $crt : null;
    }

    /**
     * Retorna a cor CSS para a classificacao.
     */
    public function getCorClassificacao(string $classificacao): string
    {
        return match ($classificacao) {
            'baixo' => 'green',
            'medio' => 'yellow',
            'alto' => 'orange',
            'critico' => 'red',
            default => 'gray', // inconclusivo, nao_avaliado e desconhecidos
        };
    }

    /**
     * Retorna o label legivel para a classificacao.
     */
    public function getLabelClassificacao(string $classificacao): string
    {
        return match ($classificacao) {
            'baixo' => 'Baixo Risco',
            'medio' => 'Médio Risco',
            'alto' => 'Alto Risco',
            'critico' => 'Risco Crítico',
            'inconclusivo' => 'Risco Não Conclusivo',
            default => 'Não Avaliado',
        };
    }

    /**
     * Atualiza ou cria o score de um participante (contraparte).
     */
    public function atualizarScore(Participante $participante, array $dados): ParticipanteScore
    {
        return $this->persistirScore(['participante_id' => $participante->id], $participante->user_id, $dados, $participante);
    }

    /**
     * Atualiza ou cria o score de um cliente (empresa gerida/própria).
     */
    public function atualizarScoreCliente(Cliente $cliente, array $dados): ParticipanteScore
    {
        return $this->persistirScore(['cliente_id' => $cliente->id], $cliente->user_id, $dados, $cliente);
    }

    /**
     * Persiste o score do alvo (participante OU cliente) — UPSERT pela chave do alvo.
     * NÃO toca em campos do alvo (situacao/regime/razao/ultima_consulta_em) — ver CLAUDE.md
     * "Laravel não atualiza participantes". A frescura vive em participante_scores.ultima_consulta_em.
     *
     * @param  array{participante_id?: int, cliente_id?: int}  $chaveAlvo
     */
    private function persistirScore(array $chaveAlvo, int $userId, array $dados, ?Model $alvo = null): ParticipanteScore
    {
        // Consulta parcial não pode apagar avaliação anterior: uma consulta só-cadastral
        // posterior zerava os subscores de certidão já persistidos e todo score virava
        // 'inconclusivo'. Mescla os dados novos sobre os já consultados — chave presente
        // na consulta nova prevalece; ausente mantém o dado anterior (a certidão antiga
        // segue datada: emissão/validade vivem dentro do próprio bloco).
        $existente = ParticipanteScore::where($chaveAlvo)->first();
        if ($existente !== null && is_array($existente->dados_consultados)) {
            $dados = array_merge($existente->dados_consultados, $dados);
        }

        $scores = $this->calcularScores($dados);
        $scoreTotal = $this->calcularScoreTotal($scores);
        $classificacao = $this->classificarComCobertura($scores);

        return ParticipanteScore::updateOrCreate(
            $chaveAlvo,
            [
                'user_id' => $userId,
                'score_cadastral' => $scores['cadastral'],
                'score_cnd_federal' => $scores['cnd_federal'],
                'score_cnd_estadual' => $scores['cnd_estadual'],
                'score_fgts' => $scores['fgts'],
                'score_trabalhista' => $scores['trabalhista'],
                'score_total' => $scoreTotal,
                'score_credito_reforma' => $this->scoreCreditoReforma($dados, $alvo),
                'classificacao' => $classificacao,
                'ultima_consulta_em' => now(),
                'dados_consultados' => $dados,
            ]
        );
    }

    /**
     * Obtem estatisticas de risco para um usuario. O $escopo opcional permite filtrar
     * (ex.: por cliente) — recebe o query builder de ParticipanteScore.
     */
    public function getEstatisticas(int $userId, ?\Closure $escopo = null): array
    {
        $query = ParticipanteScore::where('user_id', $userId);

        if ($escopo) {
            $escopo($query);
        }

        $totais = $query
            ->select(
                DB::raw('COUNT(CASE WHEN score_total IS NOT NULL THEN 1 END) as total'),
                DB::raw("COUNT(CASE WHEN classificacao = 'baixo' THEN 1 END) as baixo"),
                DB::raw("COUNT(CASE WHEN classificacao = 'medio' THEN 1 END) as medio"),
                DB::raw("COUNT(CASE WHEN classificacao = 'alto' THEN 1 END) as alto"),
                DB::raw("COUNT(CASE WHEN classificacao = 'critico' THEN 1 END) as critico"),
                DB::raw("COUNT(CASE WHEN classificacao = 'inconclusivo' THEN 1 END) as inconclusivo"),
                DB::raw("COUNT(CASE WHEN classificacao = 'nao_avaliado' OR score_total IS NULL THEN 1 END) as nao_avaliado"),
                DB::raw('AVG(score_total) as media_score')
            )
            ->first();

        return [
            'total_avaliados' => (int) ($totais->total ?? 0),
            'baixo_risco' => (int) ($totais->baixo ?? 0),
            'medio_risco' => (int) ($totais->medio ?? 0),
            'alto_risco' => (int) ($totais->alto ?? 0),
            'critico' => (int) ($totais->critico ?? 0),
            'inconclusivo' => (int) ($totais->inconclusivo ?? 0),
            'nao_avaliado' => (int) ($totais->nao_avaliado ?? 0),
            'media_score' => round((float) ($totais->media_score ?? 0), 1),
        ];
    }

    /**
     * Rótulos canônicos das categorias avaliáveis. Ordem = ordem de $this->pesos.
     * Fonte única — ParticipanteScore::scores_detalhados e a UI consomem daqui.
     *
     * @return array<string,string>
     */
    public static function categoriaLabels(): array
    {
        return [
            'cadastral' => 'Situação Cadastral',
            'cnd_federal' => 'CND Federal',
            'cnd_estadual' => 'CND Estadual',
            'fgts' => 'FGTS/CRF',
            'trabalhista' => 'CNDT (Trabalhista)',
        ];
    }

    /**
     * Cor (hex) do subscore por faixa de risco. null = não avaliado (neutro).
     * Replica a closure histórica de risk/show.blade.php — usa `>=` (não `<=`).
     */
    public static function hexSubscore(?int $score): string
    {
        if ($score === null) {
            return '#9ca3af';
        }

        return match (true) {
            $score >= 80 => '#b91c1c',
            $score >= 50 => '#ea580c',
            $score >= 20 => '#d97706',
            default => '#047857',
        };
    }

    /**
     * Decompõe os subscores em linhas prontas pra exibição (perfil web + PDF).
     * Fonte única de label + peso + cor por categoria. Ordem fixa = ordem de $this->pesos.
     *
     * @param  array<string,int|null>  $scores  saída de calcularScores()
     * @return array<string, array{label:string, peso_pct:int, score:int|null, avaliado:bool, hex:string}>
     */
    public function detalhar(array $scores): array
    {
        $labels = self::categoriaLabels();
        $out = [];

        foreach ($this->pesos as $categoria => $peso) {
            $valor = $scores[$categoria] ?? null;
            $out[$categoria] = [
                'label' => $labels[$categoria] ?? $categoria,
                'peso_pct' => (int) round($peso * 100),
                'score' => $valor,
                'avaliado' => $valor !== null,
                'hex' => self::hexSubscore($valor),
            ];
        }

        return $out;
    }

    /**
     * Retorna os pesos configurados.
     */
    public function getPesos(): array
    {
        return $this->pesos;
    }

    // ============ Adapter: resultado_dados (aninhado) -> subscore por categoria ============

    /**
     * Situação cadastral → subscore. Ausente/desconhecida = null (não avaliado).
     * null nunca é tratado como irregular (ver Participante::classificarSituacao).
     */
    private function scoreCadastral(array $dados): ?int
    {
        $situacao = $dados['situacao_cadastral'] ?? null;

        if (empty($situacao)) {
            return null;
        }

        return match (mb_strtoupper((string) $situacao)) {
            'ATIVA' => 0,
            'SUSPENSA' => 50,
            'INAPTA', 'BAIXADA', 'NULA' => 100,
            default => null,
        };
    }

    /**
     * Certidão (CND/CRF/CNDT) → subscore, classificada pelo padrão canônico CertidaoBadge.
     * Regular → 0; Irregular → penalidade; INDETERMINADA/indisponível/ausente → null.
     */
    private function subscoreCertidao(mixed $valor, int $penalidade): ?int
    {
        if ($valor === null || $valor === '' || $valor === []) {
            return null;
        }

        $hex = CertidaoBadge::classificar($valor, aplicarIndeterminado: true)['hex'];

        return match ($hex) {
            CertidaoBadge::HEX_REGULAR => 0,
            CertidaoBadge::HEX_IRREGULAR => $penalidade,
            default => null, // indeterminado, neutro, não encontrada → não avaliado
        };
    }

}
