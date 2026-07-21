<?php

namespace App\Services\Efd\Sped;

/**
 * Caminha o stream de SpedRecord mantendo o documento-pai corrente e anexa esse
 * contexto aos registros-filho (o "Anotador" do n8n). 100% compartilhado (§L1).
 *
 * Emite pares [SpedRecord, ?Contexto]:
 *  - registro que ABRE documento (C100/D100): vira o pai corrente; emitido com pai null.
 *  - registro-filho (C170/C190/D190): emitido com o pai corrente.
 *  - qualquer outro (bloco 0/E/9…): encerra o pai corrente e emite com pai null.
 *
 * A ordem canônica do SPED garante que todo filho aparece logo após seu pai —
 * por isso "pai corrente" basta, sem casar por bloco. Filho órfão (arquivo
 * malformado) sai com pai null; o handler decide, mas nunca quebra a caminhada.
 */
class ContextWalker
{
    /** Registros que abrem um documento fiscal (setam o contexto-pai). */
    private const ABRE_DOCUMENTO = ['C100', 'D100'];
    // F5 (PIS/COFINS): incluir 'A100' aqui + o arm correspondente em montarContexto().

    /** Registros-filho que herdam o contexto do documento-pai corrente. */
    private const FILHOS = ['C170', 'C190', 'D190'];
    // F5 (PIS/COFINS): incluir 'A170'.

    /**
     * @param  iterable<SpedRecord>  $registros
     * @return iterable<array{0: SpedRecord, 1: ?Contexto}>
     */
    public function walk(iterable $registros): iterable
    {
        $pai = null;

        foreach ($registros as $rec) {
            if (in_array($rec->reg, self::ABRE_DOCUMENTO, true)) {
                $pai = $this->montarContexto($rec);
                yield [$rec, null];

                continue;
            }

            if (in_array($rec->reg, self::FILHOS, true)) {
                yield [$rec, $pai];

                continue;
            }

            // Registro fora de qualquer subárvore de documento: encerra o pai.
            $pai = null;
            yield [$rec, null];
        }
    }

    private function montarContexto(SpedRecord $rec): Contexto
    {
        return match ($rec->reg) {
            // §10.4: C100 $p[2]=IND_OPER, [5]=COD_MOD, [7]=SER, [8]=NUM_DOC, [9]=CHV_NFE
            'C100' => new Contexto(
                reg: 'C100',
                chave: $rec->campo(9),
                numero: $rec->campo(8),
                serie: $rec->campo(7),
                modelo: $rec->campo(5),
                tipoOperacao: $rec->campo(2),
            ),
            // §10.4: D100 $p[2]=IND_OPER, [5]=COD_MOD, [7]=SER, [9]=NUM_DOC, [10]=CHV_CTE
            'D100' => new Contexto(
                reg: 'D100',
                chave: $rec->campo(10),
                numero: $rec->campo(9),
                serie: $rec->campo(7),
                modelo: $rec->campo(5),
                tipoOperacao: $rec->campo(2),
            ),
        };
    }
}
