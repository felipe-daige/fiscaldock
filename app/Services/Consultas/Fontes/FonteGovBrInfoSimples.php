<?php

namespace App\Services\Consultas\Fontes;

/**
 * Base das fontes InfoSimples que exigem login GOV.BR do SOLICITANTE (SIGEF, situação fiscal PF).
 * O GOV.BR autentica o REQUERENTE (o advogado/sistema), não o alvo — o alvo vem por cpf/cnpj nos
 * params da subclasse. login_cpf+login_senha são obrigatórios em CADA chamada.
 *
 * Fonte da credencial (fase de validação): config('consultas.govbr') = credencial de sistema no
 * .env. MIGRAÇÃO para cert A1 por usuário: trocar `credencialGovBr()` para ler pkcs12_cert/pass do
 * usuário — as subclasses e o `params()` não mudam.
 *
 * pronta() exige a credencial presente: sem login GOV.BR a fonte não roda (não cobra 608 de login).
 */
abstract class FonteGovBrInfoSimples extends FonteCertidaoInfoSimples
{
    public function pronta(): bool
    {
        $cred = $this->credencialGovBr();

        return parent::pronta()
            && $this->validadaParaPublico()
            && $cred['login_cpf'] !== '' && $cred['login_senha'] !== '';
    }

    /**
     * Credencial GOV.BR injetada na chamada. Hoje: sistema (config/.env). Amanhã: cert A1 do
     * usuário. Retorna sempre login_cpf/login_senha (strings; vazias quando ausente).
     *
     * @return array{login_cpf:string,login_senha:string}
     */
    protected function credencialGovBr(): array
    {
        return [
            'login_cpf' => preg_replace('/\D/', '', (string) config('consultas.govbr.login_cpf', '')),
            'login_senha' => (string) config('consultas.govbr.login_senha', ''),
        ];
    }

    /**
     * Params base = documento do ALVO (cpf|cnpj por tipo_pessoa) + credencial GOV.BR do
     * solicitante. A subclasse acrescenta os campos próprios (codigo_imovel, pagina...).
     */
    public function params(array $alvo): array
    {
        return parent::params($alvo) + $this->credencialGovBr();
    }
}
