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
     * @return array{tipo: string|null, valido: bool, erros: array<int,string>, cnpj: string|null, periodo_inicio: string|null, periodo_fim: string|null, retificadora: bool|null}
     */
    public function extrairCabecalho(string $conteudo): array
    {
        $deteccao = $this->detectar($conteudo);

        $base = [
            'tipo' => $deteccao['tipo'],
            'valido' => $deteccao['valido'],
            'erros' => $deteccao['erros'],
            'cnpj' => null,
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
            $finalidade = $c[3] ?? null;   // COD_FIN
            $dtIni = $c[4] ?? null;
            $dtFin = $c[5] ?? null;
            $cnpj = $c[7] ?? null;
        } else {                            // PIS/COFINS
            $finalidade = $c[3] ?? null;   // TIPO_ESCRIT
            $dtIni = $c[6] ?? null;
            $dtFin = $c[7] ?? null;
            $cnpj = $c[9] ?? null;
        }

        $base['cnpj'] = ($cnpj !== null && $cnpj !== '') ? preg_replace('/\D/', '', $cnpj) : null;
        $base['periodo_inicio'] = $this->parseDataSped($dtIni);
        $base['periodo_fim'] = $this->parseDataSped($dtFin);
        $base['retificadora'] = ($finalidade === '1');

        return $base;
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
