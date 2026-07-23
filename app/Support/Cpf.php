<?php

namespace App\Support;

/**
 * Helpers stateless de CPF. Usado como identidade do SOLICITANTE (requerente) nas certidões
 * judiciais que exigem CPF de quem pede (ex.: CEAT TRT24 — 606 "CPF do solicitante é
 * obrigatório"). Valida o dígito verificador porque a InfoSimples/tribunal rejeita CPF inválido.
 */
final class Cpf
{
    /** Apenas os dígitos do documento. */
    public static function digitos(?string $doc): string
    {
        return preg_replace('/\D/', '', (string) $doc) ?? '';
    }

    /** Formata XXX.XXX.XXX-XX (CPF). Se não tiver 11 dígitos, devolve o valor original. */
    public static function formatar(string $cpf): string
    {
        $d = self::digitos($cpf);
        if (strlen($d) !== 11) {
            return $cpf;
        }

        return substr($d, 0, 3).'.'.substr($d, 3, 3).'.'.substr($d, 6, 3).'-'.substr($d, 9, 2);
    }

    /**
     * True se for um CPF válido: 11 dígitos, não todos iguais (000..., 111...) e dígitos
     * verificadores mod-11 corretos. Máscara/valor vazio → false.
     */
    public static function valido(?string $cpf): bool
    {
        $d = self::digitos($cpf);
        if (strlen($d) !== 11 || preg_match('/^(\d)\1{10}$/', $d)) {
            return false;
        }

        return self::dv(substr($d, 0, 9)) === substr($d, 9, 2);
    }

    /** Os 2 dígitos verificadores (mod-11) a partir dos 9 primeiros dígitos do CPF. */
    private static function dv(string $base9): string
    {
        $calc = static function (string $numeros): int {
            // dv1: pesos 10..2 sobre 9 dígitos; dv2: pesos 11..2 sobre 10 dígitos.
            $peso = strlen($numeros) + 1;
            $soma = 0;
            foreach (str_split($numeros) as $n) {
                $soma += (int) $n * $peso;
                $peso--;
            }
            $resto = $soma % 11;

            return $resto < 2 ? 0 : 11 - $resto;
        };

        $dv1 = $calc($base9);
        $dv2 = $calc($base9.$dv1);

        return (string) $dv1.(string) $dv2;
    }
}
