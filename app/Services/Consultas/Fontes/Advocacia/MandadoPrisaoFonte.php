<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteInfoSimplesBase;
use App\Support\Cpf;

/**
 * BNMP/CNJ. Dado pessoal sensível: default desligado até base legal, finalidade e retenção.
 */
class MandadoPrisaoFonte extends FonteInfoSimplesBase
{
    public function chave(): string
    {
        return 'mandado_prisao';
    }

    public function slug(): string
    {
        return 'cnj/mandados-prisao';
    }

    public function aceitaPessoa(): array
    {
        return ['PF'];
    }

    public function pronta(): bool
    {
        return parent::pronta() && (bool) config('advocacia.fontes_sensiveis.mandado_prisao', false);
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.mandado_prisao', 1.00);
    }

    public function params(array $alvo): array
    {
        return array_filter([
            'cpf' => Cpf::digitos($alvo['cpf'] ?? $alvo['documento'] ?? ''),
            'nome' => trim((string) ($alvo['nome'] ?? $alvo['razao_social'] ?? '')),
            'nome_mae' => trim((string) ($alvo['nome_mae'] ?? '')),
        ], fn ($valor) => $valor !== '');
    }

    public function aplicavelPara(array $alvo): bool
    {
        return Cpf::valido($alvo['cpf'] ?? $alvo['documento'] ?? null);
    }

    public function motivoIndisponivel(array $alvo): string
    {
        return 'CPF válido é obrigatório para consultar mandados de prisão.';
    }

    public function normalizar(array $raw, string $status = 'sucesso'): array
    {
        if ($status === 'nao_encontrado') {
            return $this->blocoNegativo();
        }

        if ($status === 'indeterminado') {
            return $this->bloco(['status' => 'INDETERMINADO', 'mensagem' => $this->mensagem($raw)]);
        }

        if ($status === 'nao_aplicavel') {
            return $this->bloco([
                'status' => 'INDISPONIVEL',
                'mensagem' => $raw['_motivo'] ?? $this->motivoIndisponivel([]),
            ]);
        }

        if ($status !== 'sucesso') {
            return [];
        }

        $data = array_values(array_filter((array) ($raw['data'] ?? []), 'is_array'));
        if (count($data) === 1) {
            foreach (['mandados', 'resultados', 'registros'] as $chave) {
                if (isset($data[0][$chave]) && is_array($data[0][$chave])) {
                    $data = array_values(array_filter($data[0][$chave], 'is_array'));
                    break;
                }
            }
        }

        if ($data === []) {
            return $this->blocoNegativo();
        }

        $campos = [
            'mandado', 'processo', 'situacao', 'tipo', 'especie_prisao', 'tribunal',
            'orgao_judicial', 'orgao_expedidor', 'expedicao_datahora', 'validade_data',
            'normalizado_validade_data', 'nome', 'cpf', 'mae', 'nascimento_data',
            'municipio', 'uf', 'tipificacao_penal', 'artigo', 'lei', 'regime',
        ];
        $registros = array_map(
            fn (array $registro) => array_intersect_key($registro, array_flip($campos)),
            array_slice($data, 0, 30),
        );

        return $this->bloco([
            'status' => 'Positiva',
            'nada_consta' => false,
            'total_registros' => count($data),
            'registros' => $registros,
            'mensagem' => 'Constam '.count($data).' mandado(s) vigente(s) aguardando cumprimento.',
        ]);
    }

    private function blocoNegativo(): array
    {
        return $this->bloco([
            'status' => 'Negativa',
            'nada_consta' => true,
            'total_registros' => 0,
            'registros' => [],
            'mensagem' => 'Nenhum mandado vigente encontrado para o CPF.',
        ]);
    }
}
