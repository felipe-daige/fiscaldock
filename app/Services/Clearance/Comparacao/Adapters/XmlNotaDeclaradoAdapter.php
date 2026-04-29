<?php

namespace App\Services\Clearance\Comparacao\Adapters;

use App\Models\XmlNota;
use App\Services\Clearance\Comparacao\DeclaradoSource;
use App\Services\Clearance\Comparacao\ItemNormalizado;
use App\Services\Clearance\Comparacao\NotaNormalizada;

final class XmlNotaDeclaradoAdapter implements DeclaradoSource
{
    public function __construct(private readonly XmlNota $nota) {}

    public function carregar(): NotaNormalizada
    {
        $payload = is_array($this->nota->payload) ? $this->nota->payload : [];
        $tipoDocumento = strtoupper((string) ($this->nota->tipo_documento ?? 'NFE'));
        $modelo = strlen((string) $this->nota->nfe_id) === 44
            ? substr((string) $this->nota->nfe_id, 20, 2)
            : ($payload['ide']['mod'] ?? null);

        return new NotaNormalizada(
            chave: (string) $this->nota->nfe_id,
            tipoDocumento: $tipoDocumento,
            header: [
                'numero' => $this->nota->numero_nota !== null ? (string) $this->nota->numero_nota : null,
                'serie' => $this->nota->serie !== null ? (string) $this->nota->serie : null,
                'data_emissao' => $this->nota->data_emissao?->format('Y-m-d') ?? (string) $this->nota->data_emissao,
                'modelo' => $modelo,
                'natureza_operacao' => $payload['ide']['natOp'] ?? $this->nota->natureza_operacao,
            ],
            metaSefaz: [],
            partes: [
                'emit' => [
                    'cnpj' => $this->nota->emit_cnpj,
                    'razao_social' => $this->nota->emit_razao_social,
                    'ie' => $payload['emit']['IE'] ?? null,
                    'uf' => $payload['emit']['enderEmit']['UF'] ?? $this->nota->emit_uf,
                ],
                'dest' => [
                    'cnpj' => $this->nota->dest_cnpj,
                    'razao_social' => $this->nota->dest_razao_social,
                    'ie' => $payload['dest']['IE'] ?? null,
                    'uf' => $payload['dest']['enderDest']['UF'] ?? $this->nota->dest_uf,
                ],
            ],
            totais: [
                'valor_total' => (float) $this->nota->valor_total,
                'base_icms' => isset($payload['total']['ICMSTot']['vBC']) ? (float) $payload['total']['ICMSTot']['vBC'] : null,
                'valor_icms' => isset($payload['total']['ICMSTot']['vICMS']) ? (float) $payload['total']['ICMSTot']['vICMS'] : null,
                'valor_ipi' => isset($payload['total']['ICMSTot']['vIPI']) ? (float) $payload['total']['ICMSTot']['vIPI'] : null,
                'valor_pis' => isset($payload['total']['ICMSTot']['vPIS']) ? (float) $payload['total']['ICMSTot']['vPIS'] : null,
                'valor_cofins' => isset($payload['total']['ICMSTot']['vCOFINS']) ? (float) $payload['total']['ICMSTot']['vCOFINS'] : null,
                'valor_frete' => isset($payload['total']['ICMSTot']['vFrete']) ? (float) $payload['total']['ICMSTot']['vFrete'] : null,
                'valor_seguro' => isset($payload['total']['ICMSTot']['vSeg']) ? (float) $payload['total']['ICMSTot']['vSeg'] : null,
                'valor_desconto' => isset($payload['total']['ICMSTot']['vDesc']) ? (float) $payload['total']['ICMSTot']['vDesc'] : null,
            ],
            itens: $this->mapearItens($payload['det'] ?? []),
            origemLabel: $this->origemLabel(),
        );
    }

    public function origemLabel(): string
    {
        $data = $this->nota->created_at?->format('d/m/Y') ?? '—';

        return "XML uploaded em {$data}";
    }

    /**
     * @param  array<int, array<string, mixed>>  $det
     * @return array<int, ItemNormalizado>
     */
    private function mapearItens(array $det): array
    {
        return array_values(array_map(function (array $item, int $idx): ItemNormalizado {
            $prod = $item['prod'] ?? [];

            return new ItemNormalizado(
                cProd: isset($prod['cProd']) ? (string) $prod['cProd'] : null,
                nItem: (int) ($item['nItem'] ?? ($idx + 1)),
                xProd: isset($prod['xProd']) ? (string) $prod['xProd'] : null,
                ncm: isset($prod['NCM']) ? (string) $prod['NCM'] : null,
                cfop: isset($prod['CFOP']) ? (string) $prod['CFOP'] : null,
                qCom: isset($prod['qCom']) ? (float) $prod['qCom'] : null,
                uCom: isset($prod['uCom']) ? (string) $prod['uCom'] : null,
                vUnCom: isset($prod['vUnCom']) ? (float) $prod['vUnCom'] : null,
                vProd: isset($prod['vProd']) ? (float) $prod['vProd'] : null,
            );
        }, $det, array_keys($det)));
    }
}
