<?php

namespace App\Services\Consultas\Fontes;

use App\Services\Consultas\Contracts\Fonte;

/**
 * Base comum a TODA fonte InfoSimples (certidões e não-certidões: sintegra...).
 * Centraliza provider, gate de cutover (pronta), params padrão, bloco e mensagem.
 * Cada fonte implementa `normalizar()` conforme seu shape.
 */
abstract class FonteInfoSimplesBase implements Fonte
{
    abstract public function chave(): string;

    abstract public function slug(): string;

    abstract public function custoCreditos(): float;

    abstract public function normalizar(array $raw, string $status = 'sucesso'): array;

    public function fornece(): array
    {
        return [$this->chave()];
    }

    public function provider(): string
    {
        return 'infosimples';
    }

    public function pronta(): bool
    {
        // Pausa na origem NÃO mora aqui: é decisão operacional por CHAVE, válida pra qualquer
        // fonte (inclusive as que não usam InfoSimples) — vive em FonteRegistry::pausada().
        // Este gate é o que de fato pertence ao provedor: liga/desliga + token.
        //
        // Só consulta o InfoSimples quando estiver explicitamente ativado
        // (pago/validado) E houver token. Enquanto false, as fontes InfoSimples não rodam.
        return (bool) config('consultas.infosimples_ativo', false)
            && filled(config('consultas.providers.infosimples.token'));
    }

    public function slugPara(array $alvo): string
    {
        return $this->slug();
    }

    public function aplicavelPara(array $alvo): bool
    {
        return true; // por padrão, aplica-se a todo CNPJ (cobertura nacional)
    }

    public function motivoIndisponivel(array $alvo): string
    {
        return 'Cobertura indisponível para este alvo no provedor.';
    }

    /**
     * Default: só PJ. Fontes PF-nativas (cadastro_pf, antecedentes, TSE...) sobrescrevem para
     * ['PF']; as judiciais/sanções que a origem indexa por CPF e CNPJ retornam ['PF','PJ'].
     */
    public function aceitaPessoa(): array
    {
        return ['PJ'];
    }

    /**
     * Para fontes historicamente PJ que já possuem branch CPF implementado, mas cuja liberação
     * depende de smoke real autorizado. A metadata da vitrine pode anunciar "CPF em manutenção";
     * a execução só passa a aceitar PF quando a chave entra em `advocacia.fontes_pf_liberadas`.
     *
     * @return list<string>
     */
    protected function tiposPessoaComPfValidado(): array
    {
        return in_array($this->chave(), (array) config('advocacia.fontes_pf_liberadas', []), true)
            ? ['PF', 'PJ']
            : ['PJ'];
    }

    /** Gate de fontes inteiramente novas: código registrado, vitrine em manutenção, zero cobrança. */
    protected function validadaParaPublico(): bool
    {
        return in_array(
            $this->chave(),
            (array) config('advocacia.fontes_publicas_liberadas', []),
            true,
        );
    }

    /**
     * Param do documento montado pelo TIPO do alvo: `cpf` (11 díg) quando `tipo_pessoa=PF`,
     * senão `cnpj`. Lê o alias específico (`alvo['cpf']`/`alvo['cnpj']`) e cai no `documento`
     * canônico — assim as fontes PJ existentes que fazem `parent::params()` seguem recebendo
     * a chave `cnpj`, e as PF recebem `cpf`, sem cada classe saber do branch.
     */
    public function params(array $alvo): array
    {
        if (strtoupper((string) ($alvo['tipo_pessoa'] ?? 'PJ')) === 'PF') {
            return ['cpf' => preg_replace('/[^0-9]/', '', (string) ($alvo['cpf'] ?? $alvo['documento'] ?? ''))];
        }

        return ['cnpj' => preg_replace('/[^0-9]/', '', (string) ($alvo['cnpj'] ?? $alvo['documento'] ?? ''))];
    }

    /**
     * Município do alvo em forma canônica (ascii, minúsculo, kebab) — chave de todo mapa por
     * cidade das fontes (slug da CND Municipal, TRT2 × TRT15 da CEAT). A minhareceita devolve
     * o município em caixa alta e com acento; os mapas são sempre acento-free.
     */
    public static function normalizarCidade(string $cidade): string
    {
        $cidade = trim($cidade);
        if ($cidade === '') {
            return '';
        }

        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $cidade);
        $cidade = $ascii !== false ? $ascii : $cidade;
        $cidade = strtolower($cidade);
        $cidade = preg_replace('/[^a-z0-9]+/', '-', $cidade);

        return trim((string) $cidade, '-');
    }

    /**
     * Bloco padrão para status não-consultado (nao_aplicavel: bloqueado por allowlist de teste
     * ou cobertura). Fontes de lista usam isto para mostrar INDISPONIVEL em vez de sumir.
     */
    protected function blocoIndisponivel(array $raw): array
    {
        return $this->bloco([
            'status' => 'INDISPONIVEL',
            'mensagem' => $raw['_motivo'] ?? $this->mensagem($raw) ?? 'Não consultado.',
        ]);
    }

    protected function bloco(array $dados): array
    {
        return [
            $this->chave() => $dados,
            'consultas_realizadas' => [$this->chave()],
        ];
    }

    protected function mensagem(array $raw): ?string
    {
        $m = $raw['code_message'] ?? null;
        if (! empty($raw['errors']) && is_array($raw['errors'])) {
            $m = trim(($m ? $m.' ' : '').implode('; ', $raw['errors']));
        }

        return $m ?: null;
    }
}
