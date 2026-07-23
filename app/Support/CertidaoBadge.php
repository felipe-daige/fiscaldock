<?php

namespace App\Support;

/**
 * Classificador canônico de regularidade de certidões/fontes para badges da UI.
 *
 * Semântica de CERTIDÃO (não de palavra solta): "Negativa" e "Positiva com efeitos
 * de negativa" são REGULARES (sem débitos exigíveis); só "Positiva" pura é irregular.
 * Por isso não basta `str_contains('negativa')` — isso marcava Negativa como Irregular.
 *
 * Fonte única usada pela tabela do lote e pelo detalhe expansível, p/ não divergirem.
 *
 * @return array{label: string, hex: string, indeterminado?: bool, motivo?: ?string}
 */
class CertidaoBadge
{
    public const HEX_REGULAR = '#047857';

    public const HEX_IRREGULAR = '#dc2626';

    public const HEX_INDETERMINADO = '#b45309';

    public const HEX_NEUTRO = '#9ca3af';

    public const HEX_NAO_ENCONTRADA = '#6b7280';

    public const HEX_OUTRO = '#374151';

    // Fonte pedida pelo plano que NÃO retornou. Distinta de "Indisponível" (sem cobertura
    // p/ a UF/cidade) e de "—" (fora do plano). Dois sabores:
    //  - HEX_FALHOU          → falha na integração (a fonte externa não respondeu/recusou)
    //  - HEX_ERRO_INTERNO    → erro interno nosso (exceção no processamento)
    // amber-800: mais escuro que o âmbar de atenção/indeterminado (#b45309) — falha lê como
    // mais severa E o hex fica distinto, o que `ResultadoDetalhePresenter::bucketHex` exige
    // (o bucket é resolvido por valor de hex; colidir com HEX_INDETERMINADO fundia os buckets).
    public const HEX_FALHOU = '#92400e';

    public const HEX_ERRO_INTERNO = '#7c3aed';

    // Status canônico de PEDIDO PENDENTE (2 etapas / TRF assíncrono): a certidão foi solicitada
    // mas ainda não emitida. Fonte única do literal — usado pelas fontes que o PRODUZEM
    // (CertidaoTrfFonte/TjmsPedidoFonte), pelo CertidaoRegistro (que NÃO grava pendente) e aqui
    // no match do badge. Grafia exata: comparar por IGUALDADE, nunca por substring.
    public const STATUS_EM_ANDAMENTO = 'Em andamento';

    public static function classificar(mixed $valor, bool $aplicarIndeterminado = false): array
    {
        // CND Federal: o caso INDETERMINADO (611 / conseguiu_emitir=false) tem regra própria
        // e canônica — preserva o motivo. Tem precedência sobre a classificação textual.
        if ($aplicarIndeterminado) {
            $analise = CndFederal::analisar($valor);
            if ($analise['indeterminado']) {
                return [
                    'label' => $analise['label'],
                    'hex' => $analise['hex'],
                    'indeterminado' => true,
                    'motivo' => $analise['motivo'],
                ];
            }
        }

        $texto = self::extrairTexto($valor);

        if ($texto === '') {
            return ['label' => '—', 'hex' => self::HEX_NEUTRO];
        }

        $t = mb_strtolower($texto);

        if (str_contains($t, 'indetermin')) {
            return ['label' => 'Indeterminada', 'hex' => self::HEX_INDETERMINADO];
        }

        if (str_contains($t, 'nao_encontrad') || str_contains($t, 'não encontrad') || str_contains($t, 'nao encontrad')) {
            return ['label' => 'Não encontrada', 'hex' => self::HEX_NAO_ENCONTRADA];
        }

        if (str_contains($t, 'indisponiv') || str_contains($t, 'indisponív') || str_contains($t, 'nao consultad') || str_contains($t, 'não consultad')) {
            return ['label' => 'Indisponível', 'hex' => self::HEX_NEUTRO];
        }

        // Pedido aceito mas ainda não emitido (ex.: TRF unificada/CJF entrega por e-mail em até 6h).
        // NÃO é "indeterminado" (a fonte não recusou) — é pendente: âmbar de atenção, sem o flag
        // indeterminado (o card mostra o status real "Em andamento", não "Sem emissão online").
        // IGUALDADE contra o literal canônico (que as fontes produzem), NUNCA substring: um status
        // externo do provedor como "processo de baixa em andamento" NÃO pode virar pendente.
        if ($texto === self::STATUS_EM_ANDAMENTO) {
            return ['label' => self::STATUS_EM_ANDAMENTO, 'hex' => self::HEX_INDETERMINADO];
        }

        // "negativa" (certidão negativa / com efeitos de negativa) = SEM débitos = regular.
        $temNegativa = str_contains($t, 'negativa');
        $positivaPura = str_contains($t, 'positiva') && ! $temNegativa;

        $regular = ($temNegativa)
            || (str_contains($t, 'regular') && ! str_contains($t, 'irregular'))
            || str_contains($t, 'habilitad')
            || in_array($t, ['true', 'sim', 'ativa', 'ativo'], true);

        $irregular = str_contains($t, 'irregular')
            || str_contains($t, 'devedor')
            || str_contains($t, 'inapt')
            || $positivaPura
            || in_array($t, ['false', 'nao', 'não'], true);

        if ($regular) {
            return ['label' => 'Regular', 'hex' => self::HEX_REGULAR];
        }

        if ($irregular) {
            return ['label' => 'Irregular', 'hex' => self::HEX_IRREGULAR];
        }

        return ['label' => mb_strtoupper($texto), 'hex' => self::HEX_OUTRO];
    }

    /** Extrai o texto de status a partir de array (certidão), bool ou string. */
    private static function extrairTexto(mixed $valor): string
    {
        if ($valor === null || $valor === '') {
            return '';
        }

        if (is_array($valor)) {
            // `status` primeiro: nas certidões é a REGULARIDADE (Negativa/Positiva/Regular).
            // `situacao` costuma ser a validade do documento (ex.: CND Federal traz "Válida"),
            // que não diz nada sobre regularidade — só serve de fallback (ex.: SINTEGRA).
            return trim((string) ($valor['status'] ?? $valor['situacao'] ?? $valor['regularidade'] ?? ''));
        }

        if (is_bool($valor)) {
            return $valor ? 'sim' : 'nao';
        }

        return trim((string) $valor);
    }
}
