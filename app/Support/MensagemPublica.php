<?php

namespace App\Support;

/**
 * Neutraliza mensagens exibidas ao usuário removendo qualquer referência ao provedor
 * terceirizado (InfoSimples). O usuário não deve saber que a consulta é intermediada —
 * a fonte oficial é citada normalmente ("Receita Federal", "SEFAZ"), o intermediário não.
 *
 * Aplicado em tempo de exibição (cobre dados já gravados + mensagens vindas da própria API),
 * além das strings corrigidas na origem.
 */
class MensagemPublica
{
    public static function neutralizar(?string $msg): ?string
    {
        if ($msg === null) {
            return null;
        }

        // "(InfoSimples)" / "(via InfoSimples)" parentético → remove (com o espaço antes).
        $t = preg_replace('/\s*\((?:via\s+)?infosimples\)/iu', '', $msg);
        // "via InfoSimples" → remove.
        $t = preg_replace('/\s*\bvia\s+infosimples\b/iu', '', $t);
        // Menção solta restante → "provedor" (ex.: "no InfoSimples" → "no provedor").
        $t = preg_replace('/\binfosimples\b/iu', 'provedor', $t);

        // Limpeza: espaço antes de pontuação e espaços duplicados.
        $t = preg_replace('/\s+([,.;:!?])/u', '$1', $t);
        $t = preg_replace('/\s{2,}/u', ' ', $t);

        return trim($t);
    }
}
