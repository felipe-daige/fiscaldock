<?php

namespace App\Services\Efd\Sped;

/**
 * Tokenizer streaming do arquivo SPED. Register-agnostic e 100% compartilhado
 * entre EFD ICMS/IPI e PIS/COFINS (motor-laravel.md §L1).
 *
 * - Quebra por \r\n | \r | \n; pula linha vazia e linha que não abre com '|'.
 * - Para no registro |9999| — o que vem depois é a assinatura PKCS#7 binária
 *   do arquivo, que não é texto SPED e não deve ser parseada.
 * - Não interpreta os campos (datas DDMMAAAA, decimais com vírgula e
 *   normalização semântica são do handler); só devolve SpedRecord{reg, campos}.
 */
class SpedParser
{
    /**
     * @return iterable<SpedRecord>
     */
    public function stream(string $conteudo): iterable
    {
        foreach (preg_split('/\r\n|\r|\n/', $conteudo) ?: [] as $linha) {
            // Toda linha de dados SPED começa com '|'. Vazia / lixo fica fora.
            if ($linha === '' || $linha[0] !== '|') {
                continue;
            }

            // EFD é tipicamente ISO-8859-1; normaliza para UTF-8 quando ainda não
            // for UTF-8 válido. Só nas linhas de dados — paramos antes da assinatura.
            if (! mb_check_encoding($linha, 'UTF-8')) {
                $linha = mb_convert_encoding($linha, 'UTF-8', 'ISO-8859-1');
            }

            $campos = explode('|', $linha);
            // campos[0]='' (antes do 1º pipe); campos[1]=REG.
            $reg = isset($campos[1]) ? trim($campos[1]) : '';
            if ($reg === '') {
                continue;
            }

            yield new SpedRecord($reg, $campos);

            // |9999| é o último registro textual do arquivo: o resto é a
            // assinatura digital binária. Encerra o stream aqui.
            if ($reg === '9999') {
                return;
            }
        }
    }
}
