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

    /**
     * Detecta o 620 de AUTORIZAÇÃO DE PRIMEIRA VEZ: o serviço gov.br exige que o titular do login
     * autorize manualmente o acesso 1x (ex.: SIGEF pede login em sigef.incra.gov.br). É acionável
     * pelo usuário — depois de autorizar, a consulta passa. Distinto de um 620 de dado inválido.
     * Confirmado no smoke SIGEF 2026-07-24.
     */
    protected function ehAutorizacaoPendente(array $raw): bool
    {
        $msg = mb_strtolower((string) $this->mensagem($raw));

        return str_contains($msg, 'autorizar') && str_contains($msg, 'gov.br');
    }

    /**
     * Extrai a URL de autorização da mensagem (a InfoSimples a inclui entre parênteses), para o
     * card oferecer o link direto. Null quando não houver.
     */
    protected function urlAutorizacao(array $raw): ?string
    {
        if (preg_match('#https?://[^\s)]+#', (string) $this->mensagem($raw), $m)) {
            return $m[0];
        }

        return null;
    }

    public function normalizar(array $raw, string $status = 'sucesso'): array
    {
        // 620 de autorização pendente → bloco próprio (status AUTORIZACAO_PENDENTE) com orientação
        // e URL. Persiste (o card mostra "autorize uma vez", não "erro"). Sem flag indeterminado:
        // é estado acionável, não falha definitiva. A cobrança do 620 é da InfoSimples (billable).
        if ($status === 'erro_participante' && $this->ehAutorizacaoPendente($raw)) {
            return $this->bloco([
                'status' => 'AUTORIZACAO_PENDENTE',
                'mensagem' => 'Autorize uma vez o acesso a este serviço no GOV.BR e refaça a consulta.',
                'url_autorizacao' => $this->urlAutorizacao($raw),
                'detalhe_origem' => $this->mensagem($raw),
            ]);
        }

        return parent::normalizar($raw, $status);
    }
}
