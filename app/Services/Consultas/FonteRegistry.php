<?php

namespace App\Services\Consultas;

use App\Services\Consultas\Contracts\Fonte;

class FonteRegistry
{
    /** @var array<string, Fonte> chave da fonte => Fonte */
    private array $fontes = [];

    /** @var array<string, Fonte> sub-atributo de consultas_incluidas => Fonte que o fornece */
    private array $porAtributo = [];

    /** @param Fonte[] $fontes */
    public function __construct(array $fontes = [])
    {
        foreach ($fontes as $f) {
            $this->fontes[$f->chave()] = $f;
            foreach ($f->fornece() as $atributo) {
                $this->porAtributo[$atributo] = $f;
            }
        }
    }

    public function get(string $chave): ?Fonte
    {
        return $this->fontes[$chave] ?? null;
    }

    public function aceitaTipo(string $chave, string $tipoPessoa): bool
    {
        $fonte = $this->get($chave);

        return $fonte !== null
            && in_array(strtoupper($tipoPessoa), array_map('strtoupper', $fonte->aceitaPessoa()), true);
    }

    /**
     * Fonte PAUSADA na origem (`consultas.fontes_pausadas`, env CONSULTAS_FONTES_PAUSADAS): a
     * InfoSimples despausa/pausa endpoints globalmente quando o site oficial está instável.
     *
     * O gate vive AQUI, no registry, e não numa classe base de provedor: pausa é decisão
     * OPERACIONAL sobre a chave da fonte, não característica do provedor. Enquanto morou em
     * `FonteInfoSimplesBase::pronta()`, toda fonte fora daquela hierarquia (ex.: AnaliseFiscalFonte,
     * derivada) ignorava o kill-switch em silêncio — o operador achava que tinha desligado a fonte
     * e ela seguia sendo vendida e executada.
     */
    public function pausada(string $chave): bool
    {
        return in_array($chave, (array) config('consultas.fontes_pausadas', []), true);
    }

    /**
     * True se TODOS os sub-atributos do plano (consultas_incluidas) são fornecidos
     * por uma fonte registrada E PRONTA (gate de liga/desliga do provider).
     */
    public function cobre(array $atributos): bool
    {
        if (empty($atributos)) {
            return false;
        }

        $inline = (array) config('consultas.atributos_inline', []);

        foreach ($atributos as $atributo) {
            // Atributos inline (ex: parecer_fiscal) são renderizados dos dados — não exigem fonte.
            if (in_array($atributo, $inline, true)) {
                continue;
            }
            $fonte = $this->porAtributo[$atributo] ?? null;
            if (! $fonte || ! $fonte->pronta() || $this->pausada($fonte->chave())) {
                return false;
            }
        }

        return true;
    }

    /**
     * Fontes (deduplicadas) necessárias para atender os sub-atributos do plano.
     *
     * @return Fonte[]
     */
    public function fontesDe(array $atributos, ?string $tipoPessoa = null): array
    {
        $out = [];
        foreach ($atributos as $atributo) {
            $fonte = $this->porAtributo[$atributo] ?? null;
            // Pausada na origem não roda nem cobra, mesmo que o atributo venha de um lote/plano
            // criado antes da pausa (`get()` segue devolvendo a fonte: o follow-up de 2 etapas
            // precisa resolver pedidos já pagos e em voo).
            if ($fonte
                && ! $this->pausada($fonte->chave())
                && ($tipoPessoa === null || $this->aceitaTipo($fonte->chave(), $tipoPessoa))) {
                $out[$fonte->chave()] = $fonte;
            }
        }

        return array_values($out);
    }
}
