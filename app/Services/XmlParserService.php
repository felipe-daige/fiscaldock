<?php

namespace App\Services;

use SimpleXMLElement;
use Exception;

class XmlParserService
{
    /**
     * Extrai dados essenciais de um arquivo XML (NF-e/NFS-e)
     *
     * @param string $xmlContent Conteúdo do arquivo XML
     * @return array Dados extraídos
     * @throws Exception Se o XML for inválido
     */
    public function extrairDados(string $xmlContent): array
    {
        // Remove namespaces para facilitar parsing
        $xmlContent = $this->removerNamespaces($xmlContent);
        
        try {
            $xml = new SimpleXMLElement($xmlContent);
        } catch (Exception $e) {
            throw new Exception('XML inválido: ' . $e->getMessage());
        }

        // Detecta tipo de documento
        $tipoDocumento = $this->detectarTipoDocumento($xml);

        if ($tipoDocumento === 'nfe') {
            return $this->extrairDadosNFe($xml);
        } elseif ($tipoDocumento === 'nfse') {
            return $this->extrairDadosNfse($xml);
        }

        throw new Exception('Tipo de documento não suportado');
    }

    /**
     * Extrai dados de uma NF-e
     */
    private function extrairDadosNFe(SimpleXMLElement $xml): array
    {
        $dados = [
            'tipo_documento' => 'nfe',
            'chave_acesso' => null,
            'cnpj_emitente' => null,
            'cnpj_destinatario' => null,
            'data_emissao' => null,
            'valor_total' => 0,
            'cfop' => null,
            'razao_social_emitente' => null,
            'razao_social_destinatario' => null,
        ];

        // Chave de acesso (chNFe)
        if (isset($xml->infNFe['Id'])) {
            $dados['chave_acesso'] = str_replace('NFe', '', (string)$xml->infNFe['Id']);
        } elseif (isset($xml->NFe->infNFe['Id'])) {
            $dados['chave_acesso'] = str_replace('NFe', '', (string)$xml->NFe->infNFe['Id']);
        }

        // Emitente
        if (isset($xml->emit)) {
            $dados['cnpj_emitente'] = $this->limparCnpj((string)$xml->emit->CNPJ ?? '');
            $dados['razao_social_emitente'] = (string)($xml->emit->xNome ?? '');
        } elseif (isset($xml->NFe->infNFe->emit)) {
            $dados['cnpj_emitente'] = $this->limparCnpj((string)$xml->NFe->infNFe->emit->CNPJ ?? '');
            $dados['razao_social_emitente'] = (string)($xml->NFe->infNFe->emit->xNome ?? '');
        }

        // Destinatário
        if (isset($xml->dest)) {
            $dados['cnpj_destinatario'] = $this->limparCnpj((string)$xml->dest->CNPJ ?? '');
            $dados['razao_social_destinatario'] = (string)($xml->dest->xNome ?? '');
        } elseif (isset($xml->NFe->infNFe->dest)) {
            $dados['cnpj_destinatario'] = $this->limparCnpj((string)$xml->NFe->infNFe->dest->CNPJ ?? '');
            $dados['razao_social_destinatario'] = (string)($xml->NFe->infNFe->dest->xNome ?? '');
        }

        // Data de emissão
        if (isset($xml->ide->dhEmi)) {
            $dados['data_emissao'] = $this->parsearData((string)$xml->ide->dhEmi);
        } elseif (isset($xml->NFe->infNFe->ide->dhEmi)) {
            $dados['data_emissao'] = $this->parsearData((string)$xml->NFe->infNFe->ide->dhEmi);
        }

        // Valor total
        if (isset($xml->total->ICMSTot->vNF)) {
            $dados['valor_total'] = (float)$xml->total->ICMSTot->vNF;
        } elseif (isset($xml->NFe->infNFe->total->ICMSTot->vNF)) {
            $dados['valor_total'] = (float)$xml->NFe->infNFe->total->ICMSTot->vNF;
        }

        // CFOP (pega o primeiro CFOP encontrado nos produtos)
        if (isset($xml->det)) {
            foreach ($xml->det as $det) {
                if (isset($det->prod->CFOP)) {
                    $dados['cfop'] = (string)$det->prod->CFOP;
                    break;
                }
            }
        } elseif (isset($xml->NFe->infNFe->det)) {
            foreach ($xml->NFe->infNFe->det as $det) {
                if (isset($det->prod->CFOP)) {
                    $dados['cfop'] = (string)$det->prod->CFOP;
                    break;
                }
            }
        }

        return $dados;
    }

    /**
     * Extrai dados de uma NFS-e (estrutura básica, pode variar por município)
     */
    private function extrairDadosNfse(SimpleXMLElement $xml): array
    {
        $dados = [
            'tipo_documento' => 'nfse',
            'chave_acesso' => null,
            'cnpj_emitente' => null,
            'cnpj_destinatario' => null,
            'data_emissao' => null,
            'valor_total' => 0,
            'cfop' => null,
            'razao_social_emitente' => null,
            'razao_social_destinatario' => null,
        ];

        // Tenta diferentes estruturas comuns de NFS-e
        if (isset($xml->InfNfse)) {
            $inf = $xml->InfNfse;
            $dados['chave_acesso'] = (string)($inf->Numero ?? '');
            $dados['data_emissao'] = $this->parsearData((string)($inf->DataEmissao ?? ''));
            $dados['valor_total'] = (float)($inf->Servico->Valores->ValorServicos ?? 0);
            
            if (isset($inf->PrestadorServico->IdentificacaoPrestador->Cnpj)) {
                $dados['cnpj_emitente'] = $this->limparCnpj((string)$inf->PrestadorServico->IdentificacaoPrestador->Cnpj);
            }
            
            if (isset($inf->TomadorServico->IdentificacaoTomador->Cnpj)) {
                $dados['cnpj_destinatario'] = $this->limparCnpj((string)$inf->TomadorServico->IdentificacaoTomador->Cnpj);
            }
        }

        return $dados;
    }

    /**
     * Detecta o tipo de documento (NF-e ou NFS-e)
     */
    private function detectarTipoDocumento(SimpleXMLElement $xml): string
    {
        // Verifica se é NF-e
        if (isset($xml->NFe) || isset($xml->infNFe) || isset($xml->NFe->infNFe)) {
            return 'nfe';
        }

        // Verifica se é NFS-e
        if (isset($xml->InfNfse) || isset($xml->Nfse)) {
            return 'nfse';
        }

        // Tenta pelo namespace
        $namespaces = $xml->getNamespaces(true);
        foreach ($namespaces as $ns) {
            if (stripos($ns, 'nfe') !== false) {
                return 'nfe';
            }
            if (stripos($ns, 'nfse') !== false) {
                return 'nfse';
            }
        }

        // Padrão: assume NF-e
        return 'nfe';
    }

    /**
     * Remove namespaces do XML para facilitar parsing
     */
    private function removerNamespaces(string $xmlContent): string
    {
        // Remove declarações de namespace, mas mantém a estrutura
        return preg_replace('/(<[^>]+)\s+xmlns[^=]*="[^"]*"/i', '$1', $xmlContent);
    }

    /**
     * Limpa e formata CNPJ
     */
    private function limparCnpj(string $cnpj): string
    {
        return preg_replace('/[^0-9]/', '', $cnpj);
    }

    /**
     * Parseia data de diferentes formatos
     */
    private function parsearData(string $data): ?string
    {
        if (empty($data)) {
            return null;
        }

        // Tenta formato ISO 8601 (com ou sem timezone)
        $timestamp = strtotime($data);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        // Tenta formato brasileiro
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $data, $matches)) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }

        return null;
    }

    /**
     * Valida se o XML é válido
     */
    public function validarXml(string $xmlContent): bool
    {
        try {
            new SimpleXMLElement($xmlContent);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
