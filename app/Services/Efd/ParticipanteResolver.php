<?php

namespace App\Services\Efd;

use App\Models\EfdImportacao;
use Illuminate\Support\Facades\DB;

/**
 * Preenche `efd_notas.participante_id` re-parseando o SPED bruto (`arquivo_base64`):
 * COD_PART do C100/D100 × 0150 → documento × `participantes(user_id, documento)` → id.
 * §L5 / §10.6 passo 4. Universal (fiscal e contribuições).
 *
 * Generaliza `BackfillParticipantesFiscal::parseArquivo` (que lê arquivos em disco) pra
 * operar sobre uma única importação a partir do conteúdo retido. NFC-e a consumidor
 * final não tem COD_PART: fica `participante_id` null (legítimo — não é erro).
 */
class ParticipanteResolver
{
    public function resolver(EfdImportacao $imp): int
    {
        $sped = $this->lerSpedBruto($imp);
        if ($sped === '') {
            return 0;
        }

        $chaveParaDoc = $this->mapearChaveDocumento($sped);
        if ($chaveParaDoc === []) {
            return 0;
        }

        $docParaId = $this->documentoParaParticipante((int) $imp->user_id);

        $atualizadas = 0;
        DB::table('efd_notas')
            ->where('user_id', $imp->user_id)
            ->where('importacao_id', $imp->id)
            ->whereNull('participante_id')
            ->whereNotNull('chave_acesso')
            ->select('id', 'chave_acesso')
            ->orderBy('id')
            ->chunk(1000, function ($notas) use ($chaveParaDoc, $docParaId, &$atualizadas) {
                foreach ($notas as $nota) {
                    $doc = $chaveParaDoc[$nota->chave_acesso] ?? null;
                    if ($doc === null) {
                        continue; // sem COD_PART no SPED (consumidor final) ou fora do arquivo
                    }
                    $pid = $docParaId[$doc] ?? null;
                    if ($pid === null) {
                        continue; // documento sem participante cadastrado
                    }
                    DB::table('efd_notas')->where('id', $nota->id)->update(['participante_id' => $pid]);
                    $atualizadas++;
                }
            });

        return $atualizadas;
    }

    /**
     * chave_acesso → documento (CNPJ/CPF, só dígitos) via COD_PART. COD_PART é escopado
     * ao arquivo, então resolvido em memória sobre o próprio SPED da importação.
     * 0150: |0150|COD_PART|NOME|COD_PAIS|CNPJ|CPF|… · C100 chave=$p[9], D100 chave=$p[10].
     *
     * @return array<string, string>
     */
    private function mapearChaveDocumento(string $sped): array
    {
        $codpartParaDoc = [];
        $documentos = []; // [chave, codpart]

        foreach (preg_split('/\r\n|\r|\n/', $sped) ?: [] as $linha) {
            if ($linha === '' || $linha[0] !== '|') {
                continue;
            }
            $p = explode('|', $linha);
            $reg = $p[1] ?? '';

            if ($reg === '0150') {
                $codpart = trim($p[2] ?? '');
                if ($codpart === '') {
                    continue;
                }
                $cnpj = preg_replace('/\D/', '', $p[5] ?? '');
                $cpf = preg_replace('/\D/', '', $p[6] ?? '');
                $doc = $cnpj !== '' ? $cnpj : $cpf;
                if ($doc !== '') {
                    $codpartParaDoc[$codpart] = $doc;
                }
            } elseif ($reg === 'C100' || $reg === 'D100') {
                $codpart = trim($p[4] ?? '');
                $chave = trim(($reg === 'C100' ? $p[9] : $p[10]) ?? '');
                if ($chave !== '' && $codpart !== '') {
                    $documentos[] = [$chave, $codpart];
                }
            } elseif ($reg === '9999') {
                break; // assinatura binária depois
            }
        }

        $map = [];
        foreach ($documentos as [$chave, $codpart]) {
            if (isset($codpartParaDoc[$codpart])) {
                $map[$chave] = $codpartParaDoc[$codpart];
            }
        }

        return $map;
    }

    /**
     * documento (só dígitos) → participante_id, no escopo do usuário.
     *
     * @return array<string, int>
     */
    private function documentoParaParticipante(int $userId): array
    {
        $map = [];
        $participantes = DB::table('participantes')
            ->where('user_id', $userId)
            ->whereNotNull('documento')
            ->select('id', 'documento')
            ->get();

        foreach ($participantes as $p) {
            $doc = preg_replace('/\D/', '', (string) $p->documento);
            if ($doc !== '') {
                $map[$doc] = (int) $p->id;
            }
        }

        return $map;
    }

    /** Lê o SPED de `arquivo_base64` (string JSON-encoded — mesmo contrato do auditar). */
    private function lerSpedBruto(EfdImportacao $imp): string
    {
        $raw = $imp->arquivo_base64;
        if (! $raw) {
            return '';
        }
        $decoded = json_decode($raw, true);

        return is_string($decoded) ? $decoded : (string) $raw;
    }
}
