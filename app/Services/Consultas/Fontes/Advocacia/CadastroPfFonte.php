<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteInfoSimplesBase;
use App\Support\Cpf;

/**
 * Receita Federal / CPF. A própria resposta já contém a situação cadastral da PF; não duplicar
 * cobrança com `receita-federal/situacao`, que exige autenticação GOV.BR/certificado A1.
 */
class CadastroPfFonte extends FonteInfoSimplesBase
{
    public function chave(): string
    {
        return 'cadastro_pf';
    }

    public function slug(): string
    {
        return 'receita-federal/cpf';
    }

    /**
     * A grafia dos params (`cpf`, `birthdate`) foi INFERIDA — `receita-federal/cpf` não está em
     * `docs/infosimples/README.md`, a fonte canônica de params exatos. Param errado devolve
     * 606/607, que são BILLABLE. Fica atrás do gate de smoke como as demais fontes novas até o
     * contrato real ser colado na doc.
     */
    public function pronta(): bool
    {
        return parent::pronta() && $this->validadaParaPublico();
    }

    public function aceitaPessoa(): array
    {
        return ['PF'];
    }

    public function custoCreditos(): float
    {
        return (float) config('consultas.fontes.cadastro_pf', 0.20);
    }

    public function params(array $alvo): array
    {
        return [
            'cpf' => Cpf::digitos($alvo['cpf'] ?? $alvo['documento'] ?? ''),
            'birthdate' => trim((string) ($alvo['birthdate'] ?? '')),
        ];
    }

    public function aplicavelPara(array $alvo): bool
    {
        return Cpf::valido($alvo['cpf'] ?? $alvo['documento'] ?? null)
            && $this->dataIsoValida((string) ($alvo['birthdate'] ?? ''));
    }

    public function motivoIndisponivel(array $alvo): string
    {
        return 'CPF válido e data de nascimento são obrigatórios para consultar a Receita Federal.';
    }

    public function normalizar(array $raw, string $status = 'sucesso'): array
    {
        if ($status === 'sucesso') {
            $data = (array) ($raw['data'][0] ?? []);
            $anoObito = $data['normalizado_ano_obito'] ?? $data['ano_obito'] ?? null;

            return $this->bloco([
                'status' => $data['situacao_cadastral'] ?? null,
                'situacao_cadastral' => $data['situacao_cadastral'] ?? null,
                'cpf' => $data['normalizado_cpf'] ?? $data['cpf'] ?? null,
                'nome' => $data['nome'] ?? null,
                'nome_civil' => $data['nome_civil'] ?? null,
                'nome_social' => $data['nome_social'] ?? null,
                'data_nascimento' => $data['normalizado_data_nascimento'] ?? $data['data_nascimento'] ?? null,
                'data_inscricao' => $data['normalizado_data_inscricao'] ?? $data['data_inscricao'] ?? null,
                'ano_obito' => $anoObito,
                'falecido' => filled($anoObito),
                'consulta_em' => $data['normalizado_consulta_datahora'] ?? $data['consulta_datahora'] ?? null,
                'comprovante' => $data['consulta_comprovante'] ?? ($data['site_receipt'] ?? null),
            ]);
        }

        if ($status === 'indeterminado') {
            return $this->bloco(['status' => 'INDETERMINADO', 'mensagem' => $this->mensagem($raw)]);
        }

        if ($status === 'nao_encontrado') {
            return $this->bloco(['status' => 'NAO_ENCONTRADO', 'mensagem' => $this->mensagem($raw)]);
        }

        if ($status === 'nao_aplicavel') {
            return $this->bloco([
                'status' => 'INDISPONIVEL',
                'mensagem' => $raw['_motivo'] ?? $this->motivoIndisponivel([]),
            ]);
        }

        return [];
    }

    private function dataIsoValida(string $data): bool
    {
        $dt = \DateTimeImmutable::createFromFormat('!Y-m-d', $data);

        return $dt !== false && $dt->format('Y-m-d') === $data && $dt <= new \DateTimeImmutable('today');
    }
}
