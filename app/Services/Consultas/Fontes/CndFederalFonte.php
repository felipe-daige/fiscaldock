<?php

namespace App\Services\Consultas\Fontes;

use App\Services\Consultas\Contracts\Fonte;

class CndFederalFonte implements Fonte
{
    public function chave(): string
    {
        return 'cnd_federal';
    }

    public function fornece(): array
    {
        return ['cnd_federal'];
    }

    public function provider(): string
    {
        return 'infosimples';
    }

    public function slug(): string
    {
        return 'receita-federal/pgfn';
    }

    public function params(array $alvo): array
    {
        return [
            'cnpj' => preg_replace('/[^0-9]/', '', (string) ($alvo['cnpj'] ?? '')),
            'preferencia_emissao' => '2via',
        ];
    }

    public function custoCreditos(): int
    {
        return (int) config('consultas.fontes.cnd_federal', 2);
    }

    public function normalizar(array $raw, string $status = 'sucesso'): array
    {
        if ($status === 'sucesso') {
            $d = $raw['data'][0] ?? [];

            return $this->bloco([
                // status = `tipo` da certidão (Negativa / Positiva com efeitos de negativa / Positiva).
                // Lido por ConsultaController via strtoupper(). Ver docs/compliance/infosimples/cnd-federal.md.
                'status' => $d['tipo'] ?? null,
                'certidao_codigo' => $d['certidao_codigo'] ?? null,
                'emissao_data' => $d['emissao_data'] ?? null,
                'data_validade' => $d['validade_data'] ?? ($d['validade'] ?? null),
                'conseguiu_emitir' => (bool) ($d['conseguiu_emitir_certidao_negativa'] ?? false),
                'debitos_pgfn' => (bool) ($d['debitos_pgfn'] ?? false),
                'debitos_rfb' => (bool) ($d['debitos_rfb'] ?? false),
                'mensagem' => $d['mensagem'] ?? null,
                'situacao' => $d['situacao'] ?? null,
            ]);
        }

        // 611: a fonte não emitiu por dados insuficientes — INDETERMINADO, nunca irregular.
        if ($status === 'indeterminado') {
            return $this->bloco(['status' => 'INDETERMINADO', 'mensagem' => $this->mensagem($raw)]);
        }

        if ($status === 'nao_encontrado') {
            return $this->bloco(['status' => 'NAO_ENCONTRADA', 'mensagem' => $this->mensagem($raw)]);
        }

        // retry/fatal/erro_participante: falha técnica/parâmetro — nada a persistir aqui
        // (a mensagem do erro é gravada em consulta_resultados.error_message pelo job).
        return [];
    }

    private function bloco(array $cndFederal): array
    {
        return [
            'cnd_federal' => $cndFederal,
            'consultas_realizadas' => ['cnd_federal'],
        ];
    }

    private function mensagem(array $raw): ?string
    {
        $m = $raw['code_message'] ?? null;
        if (! empty($raw['errors']) && is_array($raw['errors'])) {
            $m = trim(($m ? $m.' ' : '').implode('; ', $raw['errors']));
        }

        return $m ?: null;
    }
}
