<?php

namespace App\Services;

use App\Models\XmlRegraClassificacao;
use App\Models\XmlDocumento;
use App\Models\XmlLancamento;

class XmlClassificationService
{
    protected RegimeTributarioService $regimeTributarioService;

    public function __construct(RegimeTributarioService $regimeTributarioService)
    {
        $this->regimeTributarioService = $regimeTributarioService;
    }

    /**
     * Classifica um documento XML e retorna sugestões de lançamento
     *
     * @param XmlDocumento $documento
     * @return array Sugestões de classificação
     */
    public function classificar(XmlDocumento $documento): array
    {
        // Busca regime tributário do emitente
        $regimeEmitente = $this->regimeTributarioService->consultarRegimeTributario(
            $documento->cnpj_emitente
        );

        // Busca regras aplicáveis
        $regras = $this->buscarRegrasAplicaveis(
            $documento->cnpj_emitente,
            $documento->cfop,
            $regimeEmitente
        );

        // Se encontrou regra específica, aplica
        if ($regras->isNotEmpty()) {
            $regra = $regras->first();
            $sugestao = $this->aplicarRegra($regra, $documento);
            
            // Incrementa contador de uso
            $regra->incrementarUso();
            
            return $sugestao;
        }

        // Caso contrário, aplica regras padrão baseadas em CFOP
        return $this->aplicarRegrasPadrao($documento, $regimeEmitente);
    }

    /**
     * Busca regras aplicáveis para o documento
     */
    private function buscarRegrasAplicaveis(
        string $cnpjFornecedor,
        ?string $cfop,
        ?string $regimeTributario
    ) {
        return XmlRegraClassificacao::where('ativo', true)
            ->orderBy('prioridade', 'desc')
            ->orderBy('vezes_usada', 'desc') // Prioriza regras mais usadas
            ->get()
            ->filter(function ($regra) use ($cnpjFornecedor, $cfop, $regimeTributario) {
                return $this->regraAplicavel($regra, $cnpjFornecedor, $cfop, $regimeTributario);
            });
    }

    /**
     * Verifica se uma regra é aplicável
     */
    private function regraAplicavel(
        XmlRegraClassificacao $regra,
        string $cnpjFornecedor,
        ?string $cfop,
        ?string $regimeTributario
    ): bool {
        $condicoes = $regra->condicoes;

        // Verifica CNPJ do fornecedor
        if (isset($condicoes['cnpj_fornecedor']) && 
            !empty($condicoes['cnpj_fornecedor']) &&
            $condicoes['cnpj_fornecedor'] !== $cnpjFornecedor) {
            return false;
        }

        // Verifica CFOP
        if (isset($condicoes['cfop']) && 
            !empty($condicoes['cfop']) &&
            $condicoes['cfop'] !== $cfop) {
            return false;
        }

        // Verifica regime tributário
        if (isset($condicoes['regime_tributario']) && 
            !empty($condicoes['regime_tributario']) &&
            $condicoes['regime_tributario'] !== $regimeTributario) {
            return false;
        }

        return true;
    }

    /**
     * Aplica uma regra específica
     */
    private function aplicarRegra(XmlRegraClassificacao $regra, XmlDocumento $documento): array
    {
        $acao = $regra->acao;

        return [
            'natureza_operacao' => $acao['natureza_operacao'] ?? $this->sugerirNaturezaPorCfop($documento->cfop),
            'conta_debito' => $acao['conta_debito'] ?? null,
            'conta_credito' => $acao['conta_credito'] ?? null,
            'regra_id' => $regra->id,
            'regra_nome' => $regra->nome_regra,
        ];
    }

    /**
     * Aplica regras padrão baseadas em CFOP
     */
    private function aplicarRegrasPadrao(XmlDocumento $documento, ?string $regimeTributario): array
    {
        $naturezaOperacao = $this->sugerirNaturezaPorCfop($documento->cfop);
        $contas = $this->sugerirContasPorCfop($documento->cfop, $regimeTributario);

        return [
            'natureza_operacao' => $naturezaOperacao,
            'conta_debito' => $contas['debito'] ?? null,
            'conta_credito' => $contas['credito'] ?? null,
            'regra_id' => null,
            'regra_nome' => 'Regra Padrão',
        ];
    }

    /**
     * Sugere natureza da operação baseada no CFOP
     */
    private function sugerirNaturezaPorCfop(?string $cfop): string
    {
        if (empty($cfop)) {
            return 'Operação não identificada';
        }

        // CFOPs de compra (1xxx, 2xxx)
        if (str_starts_with($cfop, '1') || str_starts_with($cfop, '2')) {
            $cfopInt = (int)$cfop;
            
            // Compra para comercialização
            if (in_array($cfopInt, [1102, 1202, 1403, 2403])) {
                return 'Compra para Comercialização';
            }
            
            // Compra para industrialização
            if (in_array($cfopInt, [1101, 1201, 1401, 2401])) {
                return 'Compra para Industrialização';
            }
            
            // Compra para consumo
            if (in_array($cfopInt, [1551, 2551])) {
                return 'Compra para Consumo';
            }
            
            return 'Compra de Mercadoria';
        }

        // CFOPs de venda (5xxx, 6xxx, 7xxx)
        if (str_starts_with($cfop, '5') || str_starts_with($cfop, '6') || str_starts_with($cfop, '7')) {
            $cfopInt = (int)$cfop;
            
            // Venda de produção do estabelecimento
            if (in_array($cfopInt, [5101, 5102, 6101, 6102])) {
                return 'Venda de Produção do Estabelecimento';
            }
            
            // Venda de mercadoria adquirida
            if (in_array($cfopInt, [5103, 6103])) {
                return 'Venda de Mercadoria Adquirida';
            }
            
            return 'Venda de Mercadoria';
        }

        // CFOPs de devolução (1xxx, 2xxx específicos)
        if (in_array((int)$cfop, [1202, 1203, 2202, 2203, 5202, 5203, 6202, 6203])) {
            return 'Devolução de Venda';
        }

        return 'Operação Fiscal';
    }

    /**
     * Sugere contas contábeis baseadas no CFOP e regime tributário
     */
    private function sugerirContasPorCfop(?string $cfop, ?string $regimeTributario): array
    {
        if (empty($cfop)) {
            return ['debito' => null, 'credito' => null];
        }

        // CFOPs de compra
        if (str_starts_with($cfop, '1') || str_starts_with($cfop, '2')) {
            return [
                'debito' => '1.1.01.001', // Estoque ou Custo de Mercadorias Vendidas (exemplo)
                'credito' => '2.1.01.001', // Fornecedores (exemplo)
            ];
        }

        // CFOPs de venda
        if (str_starts_with($cfop, '5') || str_starts_with($cfop, '6') || str_starts_with($cfop, '7')) {
            return [
                'debito' => '1.1.02.001', // Clientes (exemplo)
                'credito' => '3.1.01.001', // Receita de Vendas (exemplo)
            ];
        }

        return ['debito' => null, 'credito' => null];
    }

    /**
     * Cria um lançamento sugerido para o documento
     */
    public function criarLancamentoSugerido(XmlDocumento $documento, array $sugestao): XmlLancamento
    {
        return XmlLancamento::create([
            'xml_documento_id' => $documento->id,
            'natureza_operacao' => $sugestao['natureza_operacao'],
            'conta_debito' => $sugestao['conta_debito'],
            'conta_credito' => $sugestao['conta_credito'],
            'valor' => $documento->valor_total,
            'data_competencia' => $documento->data_emissao,
            'status' => 'sugerido',
        ]);
    }

    /**
     * Cria uma nova regra de classificação baseada em um ajuste manual
     */
    public function criarRegraDeAjuste(
        string $nomeRegra,
        string $cnpjFornecedor,
        ?string $cfop,
        ?string $regimeTributario,
        string $naturezaOperacao,
        ?string $contaDebito,
        ?string $contaCredito
    ): XmlRegraClassificacao {
        return XmlRegraClassificacao::create([
            'nome_regra' => $nomeRegra,
            'condicoes' => [
                'cnpj_fornecedor' => $cnpjFornecedor,
                'cfop' => $cfop,
                'regime_tributario' => $regimeTributario,
            ],
            'acao' => [
                'natureza_operacao' => $naturezaOperacao,
                'conta_debito' => $contaDebito,
                'conta_credito' => $contaCredito,
            ],
            'prioridade' => 100, // Alta prioridade para regras criadas manualmente
            'ativo' => true,
            'vezes_usada' => 0,
        ]);
    }
}
