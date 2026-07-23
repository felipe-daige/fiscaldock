<?php

namespace App\Services\Consultas\Fontes\Advocacia;

use App\Services\Consultas\Fontes\FonteCertidaoInfoSimples;
use App\Support\Cpf;

class QuitacaoEleitoralFonte extends FonteCertidaoInfoSimples
{
    public function chave(): string
    {
        return 'quitacao_eleitoral';
    }

    public function slug(): string
    {
        return 'tribunal/tse/certidao';
    }

    /**
     * Params (`cpf`, `birthdate`, `name`, `mother`) INFERIDOS — `tribunal/tse/certidao` não está
     * em `docs/infosimples/README.md`. A grafia mistura inglês aqui e português no BCB, então ao
     * menos uma das duas está errada; 606/607 são BILLABLE. Gate de smoke até confirmar no painel.
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
        return (float) config('consultas.fontes.quitacao_eleitoral', 1.00);
    }

    public function params(array $alvo): array
    {
        return array_filter([
            'cpf' => Cpf::digitos($alvo['cpf'] ?? $alvo['documento'] ?? ''),
            'birthdate' => trim((string) ($alvo['birthdate'] ?? '')),
            'name' => trim((string) ($alvo['nome'] ?? $alvo['razao_social'] ?? '')),
            'mother' => trim((string) ($alvo['nome_mae'] ?? '')),
            'father' => trim((string) ($alvo['nome_pai'] ?? '')),
            'titulo_eleitoral' => preg_replace('/\D/', '', (string) ($alvo['titulo_eleitoral'] ?? '')),
        ], fn ($valor) => $valor !== '');
    }

    public function aplicavelPara(array $alvo): bool
    {
        return Cpf::valido($alvo['cpf'] ?? $alvo['documento'] ?? null)
            && trim((string) ($alvo['birthdate'] ?? '')) !== ''
            && trim((string) ($alvo['nome'] ?? $alvo['razao_social'] ?? '')) !== '';
    }

    public function motivoIndisponivel(array $alvo): string
    {
        return 'CPF, nome completo e data de nascimento são obrigatórios para emitir a quitação eleitoral.';
    }

    protected function mapearSucesso(array $data): array
    {
        $quite = filter_var($data['quite'] ?? false, FILTER_VALIDATE_BOOL);

        return [
            'status' => $quite ? 'Negativa' : 'Positiva',
            'quite' => $quite,
            'certidao_codigo' => $data['autenticidade'] ?? null,
            'emissao_data' => $data['emissao_datahora'] ?? null,
            'nome' => $data['nome'] ?? null,
            'data_nascimento' => $data['data_nascimento'] ?? null,
            'titulo_eleitoral' => $data['titulo_eleitoral'] ?? null,
            'biometria_coletada' => $data['biometria_coletada'] ?? null,
            'domicilio_eleitoral' => array_filter([
                'uf' => $data['uf'] ?? null,
                'municipio' => $data['municipio'] ?? null,
                'zona' => $data['zona'] ?? null,
                'secao' => $data['secao'] ?? null,
            ], fn ($valor) => $valor !== null && $valor !== ''),
            'mensagem' => $quite
                ? 'Pessoa quite com a Justiça Eleitoral.'
                : 'Consta pendência de quitação eleitoral.',
        ];
    }
}
