<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteInfoSimplesBase;
use DateTimeInterface;

/** Banco Central — existência de valores a receber, por CPF ou CNPJ. */
class BcbValoresReceberFonte extends FonteInfoSimplesBase
{
    public function chave(): string
    {
        return 'bcb_valores_receber';
    }

    public function slug(): string
    {
        return 'bcb/valores-receber';
    }

    public function pronta(): bool
    {
        return parent::pronta() && $this->validadaParaPublico();
    }

    public function aceitaPessoa(): array
    {
        return ['PF', 'PJ'];
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.bcb_valores_receber', 1.00);
    }

    public function aplicavelPara(array $alvo): bool
    {
        $tipo = strtoupper((string) ($alvo['tipo_pessoa'] ?? 'PJ'));
        $data = $tipo === 'PF'
            ? $this->dataIso($alvo['birthdate'] ?? $alvo['data_nascimento'] ?? null)
            : $this->dataIso($alvo['data_abertura_empresa'] ?? $alvo['data_inicio_atividade'] ?? null);

        return $data !== null;
    }

    public function motivoIndisponivel(array $alvo): string
    {
        return strtoupper((string) ($alvo['tipo_pessoa'] ?? 'PJ')) === 'PF'
            ? 'A consulta de Valores a Receber exige a data de nascimento do titular do CPF.'
            : 'A consulta de Valores a Receber exige a data de abertura da empresa.';
    }

    public function params(array $alvo): array
    {
        $params = parent::params($alvo);
        if (strtoupper((string) ($alvo['tipo_pessoa'] ?? 'PJ')) === 'PF') {
            $params['data_nascimento'] = $this->dataIso(
                $alvo['birthdate'] ?? $alvo['data_nascimento'] ?? null,
            );

            return $params;
        }

        $params['data_abertura_empresa'] = $this->dataIso(
            $alvo['data_abertura_empresa'] ?? $alvo['data_inicio_atividade'] ?? null,
        );

        return $params;
    }

    public function normalizar(array $raw, string $status = 'sucesso'): array
    {
        if ($status === 'sucesso') {
            $data = (array) ($raw['data'][0] ?? []);
            $possui = (bool) ($data['possui_valores_receber'] ?? false);

            return $this->bloco([
                'status' => $possui ? 'Positiva' : 'Negativa',
                'possui_valores_receber' => $possui,
                'nada_consta' => ! $possui,
                'mensagem' => $data['mensagem'] ?? null,
                'comprovante' => $data['site_receipt'] ?? ($raw['site_receipts'][0] ?? null),
            ]);
        }

        if ($status === 'indeterminado') {
            return $this->bloco(['status' => 'INDETERMINADO', 'mensagem' => $this->mensagem($raw)]);
        }

        // O contrato copiado não afirma que 612 equivale a "não possui valores". Preservamos a
        // ausência como não localizada para não produzir um falso negativo patrimonial.
        if ($status === 'nao_encontrado') {
            return $this->bloco(['status' => 'NAO_ENCONTRADO', 'mensagem' => $this->mensagem($raw)]);
        }

        if ($status === 'nao_aplicavel') {
            return $this->blocoIndisponivel($raw);
        }

        return [];
    }

    private function dataIso(mixed $valor): ?string
    {
        if ($valor instanceof DateTimeInterface) {
            return $valor->format('Y-m-d');
        }

        $data = trim((string) $valor);
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
            return null;
        }

        [$ano, $mes, $dia] = array_map('intval', explode('-', $data));

        return checkdate($mes, $dia, $ano) ? $data : null;
    }
}
