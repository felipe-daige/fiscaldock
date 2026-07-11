<?php

namespace App\Services\Clearance;

use App\Models\Cliente;
use App\Models\Participante;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Resolve CNPJs mascarados pelo portal da SEFAZ (consulta pública, sem certificado):
 * a contraparte vem com os 5 primeiros dígitos zerados (ex.: RAIZEN 09538958000105
 * chega como 00000958000105) e o nome truncado ('RAIZ***'). O sufixo de 9 dígitos
 * (fim da raiz + filial + DV do CNPJ original) é real e permite casar contra o
 * acervo do usuário (participantes/clientes).
 *
 * Política de conflito de sufixo: o match só é aceito quando é ÚNICO dentro do
 * user_id — se dois cadastros terminam no mesmo sufixo, o prefixo do nome mascarado
 * ('RAIZ***' → 'RAIZ') desambigua; persistindo ambiguidade (ou nome não batendo em
 * match único), o resolver devolve null e o sistema trata como não identificado.
 * Nunca chuta.
 *
 * Consulta COM certificado A1 (consulta_sem_certificado=false): a SEFAZ devolve a
 * contraparte completa, estaMascarado() dá false e toda esta classe vira no-op por
 * construção — auto-cadastro cria normal, resultado exibe o dado da SEFAZ sem badge,
 * classificação de partes libera os dois lados. Não existe flag a ligar/desligar:
 * o gate é o próprio dado. Este resolver permanece como fallback permanente pras
 * consultas públicas (usuário sem certificado cadastrado).
 */
class CnpjMascaradoResolver
{
    private const PREFIXO_MASCARA = '00000';

    /**
     * CNPJ mascarado = 14 dígitos, 5 primeiros zerados e DV inválido (o DV veio do
     * CNPJ original, então não fecha sobre os dígitos mascarados). A checagem de DV
     * evita falso positivo com CNPJs reais de raiz baixa (ex.: Banco do Brasil
     * 00000000000191, cujo DV é válido).
     */
    public function estaMascarado(?string $documento): bool
    {
        $d = preg_replace('/\D/', '', (string) $documento);

        return strlen($d) === 14
            && str_starts_with($d, self::PREFIXO_MASCARA)
            && ! $this->dvValido($d);
    }

    /**
     * Participante real por trás de um documento mascarado, ou null se o documento
     * não está mascarado ou não resolve com segurança.
     */
    public function identificarParticipante(int $userId, ?string $documento, ?string $nomeMascarado = null): ?Participante
    {
        if (! $this->estaMascarado($documento)) {
            return null;
        }

        return $this->resolver(
            Participante::where('user_id', $userId),
            (string) $documento,
            $nomeMascarado
        );
    }

    /**
     * Cliente real por trás de um documento mascarado (a contraparte mascarada pode
     * ser uma empresa que o usuário já cadastrou como cliente).
     */
    public function identificarCliente(int $userId, ?string $documento, ?string $nomeMascarado = null): ?Cliente
    {
        if (! $this->estaMascarado($documento)) {
            return null;
        }

        return $this->resolver(
            Cliente::where('user_id', $userId),
            (string) $documento,
            $nomeMascarado
        );
    }

    private function resolver(Builder $query, string $documentoMascarado, ?string $nomeMascarado): ?Model
    {
        $sufixo = substr(preg_replace('/\D/', '', $documentoMascarado), strlen(self::PREFIXO_MASCARA));

        $candidatos = $query->where('documento', 'like', '%'.$sufixo)->get();

        if ($candidatos->isEmpty()) {
            return null;
        }

        $prefixoNome = $this->prefixoNomeMascarado($nomeMascarado);

        if ($prefixoNome !== null) {
            $candidatos = $candidatos->filter(
                fn (Model $c) => str_starts_with($this->normalizarNome((string) $c->razao_social), $prefixoNome)
            );
        }

        return $candidatos->count() === 1 ? $candidatos->first() : null;
    }

    /**
     * 'RAIZ***' → 'RAIZ'. Null quando o nome não segue o padrão de máscara ou o
     * trecho visível é curto demais pra desambiguar com segurança.
     */
    private function prefixoNomeMascarado(?string $nome): ?string
    {
        if ($nome === null || ! str_contains($nome, '***')) {
            return null;
        }

        $prefixo = $this->normalizarNome(strstr($nome, '***', true));

        return strlen($prefixo) >= 3 ? $prefixo : null;
    }

    private function normalizarNome(string $nome): string
    {
        $s = mb_strtoupper(trim($nome));
        $s = strtr($s, [
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
            'É' => 'E', 'Ê' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ô' => 'O',
            'Õ' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ç' => 'C',
        ]);

        return $s;
    }

    private function dvValido(string $cnpj): bool
    {
        foreach ([12, 13] as $posicao) {
            $pesos = $posicao === 12
                ? [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
                : [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

            $soma = 0;
            foreach ($pesos as $i => $peso) {
                $soma += ((int) $cnpj[$i]) * $peso;
            }

            $resto = $soma % 11;
            if ((int) $cnpj[$posicao] !== ($resto < 2 ? 0 : 11 - $resto)) {
                return false;
            }
        }

        return true;
    }
}
