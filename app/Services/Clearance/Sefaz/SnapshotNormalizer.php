<?php

namespace App\Services\Clearance\Sefaz;

abstract class SnapshotNormalizer
{
    /**
     * Normaliza o corpo bruto InfoSimples + status canônico do ClassificadorCodigo num
     * DocumentoSnapshot. `$status` ∈ sucesso|indeterminado|nao_encontrado|erro_participante|
     * retry|fatal (o caller já resolveu retry esgotado → 'retry').
     */
    abstract public function normalizar(array $raw, string $status, string $chaveAcesso, bool $billable): DocumentoSnapshot;

    /** 'NFE'|'NFCE'|'CTE' pelo modelo (posições 20-21 da chave). */
    protected function tipoPorChave(string $chave): string
    {
        $modelo = strlen($chave) === 44 ? substr($chave, 20, 2) : '';

        return match ($modelo) {
            '65' => 'NFCE',
            '57' => 'CTE',
            default => 'NFE',
        };
    }

    protected function digits(?string $s): string
    {
        return preg_replace('/\D/', '', (string) $s);
    }

    protected function limpar(?string $s): string
    {
        $t = (string) $s;
        $norm = \Normalizer::normalize($t, \Normalizer::FORM_D);
        $t = preg_replace('/[\x{0300}-\x{036f}]/u', '', $norm !== false ? $norm : $t);

        return mb_strtoupper(trim($t));
    }

    /** "1.234,56" | "51,110" | 51.11 → float|null */
    protected function parseBR($v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_numeric($v) && ! is_string($v)) {
            return (float) $v;
        }
        $n = (float) str_replace(',', '.', str_replace('.', '', (string) $v));

        return is_finite($n) ? $n : null;
    }

    /** BR "DD/MM/YYYY HH:mm:ss±TZ" ou ISO → ISO 8601 (corrige o bug "Invalid DateTime"). */
    protected function parseDataEmissao(?string $s): ?string
    {
        if ($s === null || trim((string) $s) === '') {
            return null;
        }
        $str = trim((string) $s);
        if (preg_match('#^(\d{2})/(\d{2})/(\d{4})\s+(\d{2}):(\d{2}):(\d{2})\s*([+-]\d{2}:?\d{2})?$#', $str, $m)) {
            [, $dd, $mm, $yyyy, $hh, $mi, $ss] = $m;
            $tz = $m[7] ?? '';
            $tzNorm = $tz !== '' ? (str_contains($tz, ':') ? $tz : substr($tz, 0, 3).':'.substr($tz, 3)) : '-03:00';

            return "{$yyyy}-{$mm}-{$dd}T{$hh}:{$mi}:{$ss}{$tzNorm}";
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $str)) {
            return $str;
        }
        $ts = strtotime($str);

        return $ts ? date('c', $ts) : null;
    }

    protected function resumoChave(string $chave): string
    {
        return strlen($chave) === 44 ? substr($chave, 0, 6).'...'.substr($chave, 40) : $chave;
    }

    /** Status-snapshot pros ramos NÃO-sucesso (sucesso é derivado do payload pela subclasse). */
    protected function statusNaoSucesso(string $statusCanonico): string
    {
        return match ($statusCanonico) {
            'nao_encontrado' => 'NAO_ENCONTRADA',
            'indeterminado' => 'INDETERMINADO',
            'erro_participante' => 'ERRO_PARAMETRO',
            'retry' => 'TIMEOUT',
            default => 'ERRO_INTEGRACAO', // fatal
        };
    }

    /**
     * Regra de estorno (Code Nodes): sucesso/611/612 nunca estornam; erro_parametro e fatal
     * sempre; timeout só se NÃO billable.
     */
    protected function estornavelPara(string $statusSnapshot, bool $billable): bool
    {
        return match ($statusSnapshot) {
            'ERRO_PARAMETRO', 'ERRO_INTEGRACAO' => true,
            'TIMEOUT' => ! $billable,
            default => false,
        };
    }

    protected function persistivelPara(string $statusSnapshot): bool
    {
        return in_array($statusSnapshot, ['AUTORIZADA', 'CANCELADA', 'DENEGADA', 'INUTILIZADA', 'NAO_ENCONTRADA', 'INDETERMINADO'], true);
    }
}
