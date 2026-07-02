<?php

namespace App\Support;

class CndFederal
{
    public const LABEL_INDETERMINADO = 'Indeterminada';

    public const HEX_INDETERMINADO = '#d97706';

    /**
     * Analisa o retorno de uma certidão e isola o caso INDETERMINADO. Nasceu para a
     * CND Federal (PGFN/RFB), mas a regra vale para qualquer certidão (Estadual,
     * Municipal, CNDT, FGTS...) — CertidaoBadge a aplica a todas.
     *
     * Regra canônica: INDETERMINADO nunca é irregular — significa que a fonte
     * oficial não conseguiu emitir a certidão pela internet E não há documento
     * emitido. Se a certidão foi de fato emitida (nº/data presentes), ela é
     * conclusiva e o tipo dela manda (Positiva emitida = irregular de verdade).
     * A mensagem de origem é preservada, apenas normalizada na formatação.
     *
     * Para qualquer outro status retorna indeterminado=false e campos nulos,
     * deixando a classificação (Negativa/Positiva/etc.) a cargo de quem chamou.
     *
     * @return array{indeterminado: bool, label: ?string, hex: ?string, motivo: ?string}
     */
    public static function analisar(mixed $cnd): array
    {
        $vazio = ['indeterminado' => false, 'label' => null, 'hex' => null, 'motivo' => null];

        if (! is_array($cnd)) {
            return $vazio;
        }

        $status = strtoupper(trim((string) ($cnd['status'] ?? '')));
        $conseguiuEmitir = $cnd['conseguiu_emitir'] ?? null;

        // Um status REGULAR conclusivo (Negativa / "com efeitos de negativa" / Regular /
        // Habilitado) manda sobre conseguiu_emitir=false — ex.: FGTS volta REGULAR com
        // conseguiu_emitir=false, e é regular, não indeterminado. Já status vazio/positivo/
        // inconclusivo + falha de emissão segue INDETERMINADO (nunca vira irregular — caso 611).
        $statusRegular = $status !== ''
            && (str_contains($status, 'NEGATIVA') || str_contains($status, 'REGULAR') || str_contains($status, 'HABILITAD'))
            && ! str_contains($status, 'IRREGULAR');

        // Certidão de fato emitida (nº ou data presentes) é conclusiva: uma Positiva EMITIDA
        // aponta débito real e deve classificar como irregular — indeterminado é só quando a
        // fonte não emitiu nada (ex.: SEFAZ manda "procure a Agência Fazendária", sem certidão).
        $emitida = trim((string) ($cnd['certidao_codigo'] ?? '')) !== ''
            || trim((string) ($cnd['emissao_data'] ?? '')) !== '';

        $indeterminado = $status === 'INDETERMINADO'
            || ($conseguiuEmitir === false && ! $statusRegular && ! $emitida);

        if (! $indeterminado) {
            return $vazio;
        }

        return [
            'indeterminado' => true,
            'label' => self::LABEL_INDETERMINADO,
            'hex' => self::HEX_INDETERMINADO,
            'motivo' => self::normalizarMotivo($cnd),
        ];
    }

    private static function normalizarMotivo(array $cnd): ?string
    {
        $bruto = $cnd['mensagem'] ?? null;

        if (! is_string($bruto) || trim($bruto) === '') {
            $errors = $cnd['errors'] ?? null;
            $bruto = is_array($errors) ? ($errors[0] ?? null) : null;
        }

        if (! is_string($bruto) || trim($bruto) === '') {
            return null;
        }

        $texto = trim($bruto);
        $texto = preg_replace('/\s+/u', ' ', $texto);             // colapsa brancos múltiplos
        $texto = preg_replace('/\s+([,.;:!?])/u', '$1', $texto);    // remove espaço antes de pontuação

        return $texto;
    }
}
