<?php

namespace Tests\Fixtures;

/**
 * Gera variantes de NF-e (emit/dest/chave distintos) a partir de 1 fixture base,
 * em memória — para testes de lote multi-cliente. O parser não valida o dígito
 * verificador da chave; a dedup compara a string da chave_acesso.
 */
class NfeFixtureMint
{
    private const BASE = 'tests/Fixtures/nfe/50240197551165000193550010000248021000214750-nfe.xml';

    private const CHAVE_BASE = '50240197551165000193550010000248021000214750';

    private const EMIT_BASE = '97551165000193';

    private const DEST_BASE = '44373108000600';

    private const EMIT_NOME_BASE = 'HIDRATOP COMERCIO DE PECAS E SERVICOS HIDRAULICOS';

    private const DEST_NOME_BASE = 'COCAL COMERCIO INDUSTRIA CANAA ACUCAR E ALCOOL LTDA';

    /**
     * @param  string  $chave  44 dígitos, único por nota (dedup compara essa string).
     */
    public static function make(
        string $emitDoc,
        string $destDoc,
        string $chave,
        ?string $emitNome = null,
        ?string $destNome = null,
    ): string {
        $xml = file_get_contents(base_path(self::BASE));
        // Chave PRIMEIRO (cobre Id="NFe...", <chNFe>, Reference URI) — antes dos CNPJs,
        // pois a chave base embute o emit base como substring.
        $xml = str_replace(self::CHAVE_BASE, $chave, $xml);
        $xml = str_replace('<CNPJ>'.self::EMIT_BASE.'</CNPJ>', '<CNPJ>'.$emitDoc.'</CNPJ>', $xml);
        $xml = str_replace('<CNPJ>'.self::DEST_BASE.'</CNPJ>', '<CNPJ>'.$destDoc.'</CNPJ>', $xml);
        if ($emitNome !== null) {
            $xml = str_replace(self::EMIT_NOME_BASE, $emitNome, $xml);
        }
        if ($destNome !== null) {
            $xml = str_replace(self::DEST_NOME_BASE, $destNome, $xml);
        }

        return $xml;
    }
}
