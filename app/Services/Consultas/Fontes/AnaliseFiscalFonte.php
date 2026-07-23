<?php

namespace App\Services\Consultas\Fontes;

use App\Services\Consultas\Contracts\Fonte;

/**
 * Análise Fiscal — consulta PAGA (R$ 1,00 default) que DESBLOQUEIA o raio-X tributário derivado
 * dos MESMOS dados da chamada cadastral grátis (minhareceita): regime tributário (+ histórico e
 * estimativa), histórico no Simples, QSA detalhado, CNAEs secundários e o parecer fiscal.
 *
 * É uma fonte DERIVADA (`provider() === 'derivado'`): NÃO faz chamada externa própria. O
 * ProcessarConsultaJob pula fontes derivadas no loop de provedores; a presença desta na seleção
 * (sentinela `regime_tributario` em consultasIncluidas) faz o ramo do cadastro manter o bloco
 * enriquecido em vez de descartá-lo. Custo interno 0 → nunca estorna pelo custo; o estorno do
 * lote avulso usa o preço de venda (precosVenda), mas como a fonte é pulada ela nunca falha.
 *
 * Migração escada→à la carte: docs/advocacia/consultas-certidoes.md + memory
 * project-consultas-a-la-carte-hibrido.
 */
class AnaliseFiscalFonte implements Fonte
{
    public const CHAVE = 'analise_fiscal';

    /**
     * Chaves do bloco normalizado do CADASTRO que só ficam quando esta análise foi comprada.
     * O job descarta estas do bloco `cadastro` quando `analise_fiscal` não está na seleção.
     *
     * @var list<string>
     */
    public const CHAVES_BLOQUEADAS = [
        'regime_tributario',
        'regime_tributario_nota',
        'regime_tributario_origem',
        'regime_tributario_historico',
        'historico_simples',
        'simples_nacional',
        'data_opcao_simples',
        'data_exclusao_simples',
        'mei',
    ];

    public function chave(): string
    {
        return self::CHAVE;
    }

    public function fornece(): array
    {
        // Sub-atributos de consultas_incluidas cobertos por esta análise. `regime_tributario` é a
        // sentinela lida pelo job para decidir manter/descartar o bloco enriquecido.
        return [
            'regime_tributario',
            'regime_tributario_historico',
            'historico_simples',
            'qsa_detalhado',
            'cnaes_secundarios',
            'parecer_fiscal',
        ];
    }

    public function provider(): string
    {
        return 'derivado'; // sem chamada externa — deriva dos dados do cadastro
    }

    public function slug(): string
    {
        return '';
    }

    public function slugPara(array $alvo): string
    {
        return '';
    }

    public function params(array $alvo): array
    {
        return [];
    }

    public function normalizar(array $raw, string $status = 'sucesso'): array
    {
        return []; // nunca produz bloco próprio — o dado vive no bloco `cadastro`
    }

    public function custoCreditos(): float
    {
        return 0; // derivada: sem custo interno de provedor
    }

    /** Deriva do cadastro/regime PJ (EFD, CNAE, S.A.); sem análogo PF. */
    public function aceitaPessoa(): array
    {
        return ['PJ'];
    }

    public function pronta(): bool
    {
        return true; // grátis no provedor (deriva do cadastro), sempre disponível
    }

    public function aplicavelPara(array $alvo): bool
    {
        return true;
    }

    public function motivoIndisponivel(array $alvo): string
    {
        return 'Análise fiscal indisponível.';
    }
}
