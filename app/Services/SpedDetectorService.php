<?php

namespace App\Services;

class SpedDetectorService
{
    public const TIPO_PIS_COFINS = 'EFD PIS/COFINS';

    public const TIPO_ICMS_IPI = 'EFD ICMS/IPI';

    private const DISCRIMINADORES_PIS_COFINS = ['A100', 'A170', 'F600', 'M100', 'M200', 'M600', '0110'];

    private const DISCRIMINADORES_ICMS_IPI = ['E100', 'E110', 'E200', 'D100', 'D190', 'C190'];

    /**
     * @return array{tipo: string|null, valido: bool, erros: array<int,string>}
     */
    public function detectar(string $conteudo): array
    {
        $erros = [];

        if (! mb_check_encoding($conteudo, 'UTF-8')) {
            $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'ISO-8859-1');
        }

        if (! preg_match('/[\x20-\x7E\xC0-\xFF\r\n\t|]{20,}/', $conteudo)) {
            return ['tipo' => null, 'valido' => false, 'erros' => ['Arquivo binario nao parece ser um SPED de texto.']];
        }

        $linhas = preg_split('/\r\n|\r|\n/', $conteudo);
        $registros = [];
        foreach ($linhas as $linha) {
            if (! preg_match('/^\|([A-Z0-9]+)\|/', $linha, $m)) {
                continue;
            }
            $registros[$m[1]] = ($registros[$m[1]] ?? 0) + 1;
        }

        if (! isset($registros['0000'])) {
            $erros[] = 'Arquivo nao parece ser um SPED valido (sem registro 0000).';
        }

        if (! isset($registros['9999'])) {
            $erros[] = 'Arquivo nao parece ser um SPED valido (sem registro 9999).';
        }

        if ($erros !== []) {
            return ['tipo' => null, 'valido' => false, 'erros' => $erros];
        }

        foreach (self::DISCRIMINADORES_PIS_COFINS as $reg) {
            if (isset($registros[$reg])) {
                return ['tipo' => self::TIPO_PIS_COFINS, 'valido' => true, 'erros' => []];
            }
        }

        foreach (self::DISCRIMINADORES_ICMS_IPI as $reg) {
            if (isset($registros[$reg])) {
                return ['tipo' => self::TIPO_ICMS_IPI, 'valido' => true, 'erros' => []];
            }
        }

        return ['tipo' => null, 'valido' => true, 'erros' => []];
    }

    /**
     * Extrai identidade do registro 0000 (cabeçalho).
     * Offsets confirmados contra arquivos reais. $c[i] = campo oficial i.
     *
     * @return array{tipo: string|null, valido: bool, erros: array<int,string>, cnpj: string|null, razao_social: string|null, uf: string|null, inscricao_estadual: string|null, codigo_municipal: string|null, inscricao_municipal: string|null, suframa: string|null, periodo_inicio: string|null, periodo_fim: string|null, retificadora: bool|null}
     */
    public function extrairCabecalho(string $conteudo): array
    {
        $deteccao = $this->detectar($conteudo);

        $base = [
            'tipo' => $deteccao['tipo'],
            'valido' => $deteccao['valido'],
            'erros' => $deteccao['erros'],
            'cnpj' => null,
            'razao_social' => null,
            'uf' => null,
            'inscricao_estadual' => null,
            'codigo_municipal' => null,
            'inscricao_municipal' => null,
            'suframa' => null,
            'periodo_inicio' => null,
            'periodo_fim' => null,
            'retificadora' => null,
        ];

        if (! $deteccao['valido'] || $deteccao['tipo'] === null) {
            return $base;
        }

        if (! mb_check_encoding($conteudo, 'UTF-8')) {
            $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'ISO-8859-1');
        }

        $linha0000 = null;
        foreach (preg_split('/\r\n|\r|\n/', $conteudo) as $linha) {
            if (str_starts_with($linha, '|0000|')) {
                $linha0000 = $linha;
                break;
            }
        }

        if ($linha0000 === null) {
            return $base;
        }

        $c = explode('|', $linha0000);

        if ($deteccao['tipo'] === self::TIPO_ICMS_IPI) {
            // |0000|COD_VER|COD_FIN|DT_INI|DT_FIN|NOME|CNPJ|CPF|UF|IE|COD_MUN|IM|SUFRAMA|...
            $finalidade = $c[3] ?? null;   // COD_FIN
            $dtIni = $c[4] ?? null;
            $dtFin = $c[5] ?? null;
            $nome = $c[6] ?? null;
            $cnpj = $c[7] ?? null;
            $uf = $c[9] ?? null;
            $ie = $c[10] ?? null;
            $codMun = $c[11] ?? null;
            $im = $c[12] ?? null;
            $suframa = $c[13] ?? null;
        } else {                            // PIS/COFINS
            // |0000|COD_VER|TIPO_ESCRIT|IND_SIT_ESP|NUM_REC_ANT|DT_INI|DT_FIN|NOME|CNPJ|UF|COD_MUN|SUFRAMA|...
            // O 0000 de contribuições NÃO traz IE nem IM.
            $finalidade = $c[3] ?? null;   // TIPO_ESCRIT
            $dtIni = $c[6] ?? null;
            $dtFin = $c[7] ?? null;
            $nome = $c[8] ?? null;
            $cnpj = $c[9] ?? null;
            $uf = $c[10] ?? null;
            $ie = null;
            $codMun = $c[11] ?? null;
            $im = null;
            $suframa = $c[12] ?? null;
        }

        $base['cnpj'] = ($cnpj !== null && $cnpj !== '') ? preg_replace('/\D/', '', $cnpj) : null;
        $base['razao_social'] = ($nome !== null && trim($nome) !== '') ? trim($nome) : null;
        // UF válida = 2 letras. Qualquer outra coisa (índice deslocado por SPED variante,
        // IE numérica no lugar) degrada pra null em vez de gravar lixo/estourar varchar(2).
        $uf = $uf !== null ? strtoupper(trim($uf)) : '';
        $base['uf'] = preg_match('/^[A-Z]{2}$/', $uf) ? $uf : null;
        $base['inscricao_estadual'] = $this->limpo($ie);
        // COD_MUN = código IBGE de 7 dígitos. Fora disso, descarta (índice deslocado).
        $codMun = $this->limpo($codMun);
        $base['codigo_municipal'] = ($codMun !== null && preg_match('/^\d{7}$/', $codMun)) ? $codMun : null;
        $base['inscricao_municipal'] = $this->limpo($im);
        $base['suframa'] = $this->limpo($suframa);
        $base['periodo_inicio'] = $this->parseDataSped($dtIni);
        $base['periodo_fim'] = $this->parseDataSped($dtFin);
        $base['retificadora'] = ($finalidade === '1');

        return $base;
    }

    /** Trim; string vazia vira null (campo opcional do 0000 não informado). */
    private function limpo(?string $v): ?string
    {
        $v = trim((string) $v);

        return $v === '' ? null : $v;
    }

    /** Converte DDMMAAAA do SPED para 'Y-m-d', ou null se inválida. */
    private function parseDataSped(?string $ddmmaaaa): ?string
    {
        if ($ddmmaaaa === null || ! preg_match('/^\d{8}$/', $ddmmaaaa)) {
            return null;
        }

        $dia = (int) substr($ddmmaaaa, 0, 2);
        $mes = (int) substr($ddmmaaaa, 2, 2);
        $ano = (int) substr($ddmmaaaa, 4, 4);

        if (! checkdate($mes, $dia, $ano)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
    }
}
