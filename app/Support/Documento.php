<?php

namespace App\Support;

/**
 * Helpers de classificação de documento (CPF × CNPJ) para relatórios e planilhas.
 *
 * Regra de produto (2026-07-08): quando a contraparte é **CPF** (pessoa física), a coluna
 * de regularidade/situação/classificação NÃO deve exibir "não/nunca consultado" — isso
 * sugeriria uma pendência de consulta que não existe. CPF não tem certidão/situação
 * cadastral consultável como PJ, então marcamos explicitamente que é um CPF. Aplicar em
 * TODO export (PDF/XLSX/CSV) que renderize regularidade por contraparte.
 */
final class Documento
{
    /** Rótulo canônico exibido no lugar de "não consultado" quando o documento é um CPF. */
    public const LABEL_CPF = 'CPF (pessoa física)';

    /** Classe de regularidade canônica para CPF (mapeada em ReportTheme::OUTRO nos exports). */
    public const CLASSE_CPF = 'cpf';

    public static function ehCpf(?string $doc): bool
    {
        return strlen(Cnpj::digitos($doc)) === 11;
    }

    public static function ehCnpj(?string $doc): bool
    {
        return strlen(Cnpj::digitos($doc)) === 14;
    }

    /**
     * Rótulo de regularidade quando não há classificação/consulta: `LABEL_CPF` para CPF,
     * senão o padrão da tela ("não consultado", "nunca consultado", …).
     */
    public static function rotuloSemConsulta(?string $doc, string $padrao = 'não consultado'): string
    {
        return self::ehCpf($doc) ? self::LABEL_CPF : $padrao;
    }
}
