<?php

namespace App\Services\Clearance\Sefaz;

class NfeSnapshotNormalizer extends SnapshotNormalizer
{
    public function normalizar(array $raw, string $status, string $chaveAcesso, bool $billable): DocumentoSnapshot
    {
        $chave = substr($this->digits($chaveAcesso), 0, 44);
        $tipo = $this->tipoPorChave($chave);
        $header = $raw['header'] ?? [];
        $billable = (bool) ($header['billable'] ?? $billable);
        $code = (int) ($raw['code'] ?? 0);
        $codeMessage = $raw['code_message'] ?? null;
        $errors = is_array($raw['errors'] ?? null) ? $raw['errors'] : [];
        $mensagemErros = $errors ? implode(' | ', $errors) : $codeMessage;

        // Ramos não-sucesso: shape mínimo (espelha Code Nodes 2-6).
        if ($status !== 'sucesso') {
            $statusSnap = $this->statusNaoSucesso($status);

            return new DocumentoSnapshot(
                tipoDocumento: $tipo,
                chaveAcesso: $chave,
                status: $statusSnap,
                colunas: $this->colunasMinimas($chave, $statusSnap, $code, $codeMessage),
                payload: $this->payloadEnvelope($chave, $tipo, $statusSnap, [], $code, $codeMessage, $billable, $header),
                persistivel: $this->persistivelPara($statusSnap),
                estornavel: $this->estornavelPara($statusSnap, $billable),
                billable: $billable,
                errorCode: in_array($statusSnap, ['ERRO_PARAMETRO', 'TIMEOUT', 'ERRO_INTEGRACAO'], true) ? $statusSnap : null,
                errorMessage: in_array($statusSnap, ['INDETERMINADO', 'ERRO_PARAMETRO', 'TIMEOUT', 'ERRO_INTEGRACAO'], true) ? $mensagemErros : null,
                progressoMensagem: "NFe {$this->resumoChave($chave)}: {$statusSnap}",
            );
        }

        // Sucesso (Code Node 1).
        $data = $raw['data'][0] ?? [];
        $nfe = $data['nfe'] ?? [];
        $resumida = $data['resumida']['nfe'] ?? [];
        $eventos = is_array($nfe['eventos'] ?? null) ? $nfe['eventos'] : [];

        $situacaoRaw = $this->limpar($nfe['situacao'] ?? null);
        $situacaoAmbiente = $nfe['situacao_ambiente'] ?? null;
        $statusSnap = $this->deriveStatus($eventos, $nfe['situacao'] ?? null);
        if (str_contains($this->limpar($situacaoAmbiente), 'HOMOLOGA')) {
            $statusSnap = 'INDETERMINADO';
        }

        $valorTotal = (! empty($data['totais']['normalizado_valor_nfe']))
            ? (float) $data['totais']['normalizado_valor_nfe']
            : ($this->parseBR($nfe['valor_total'] ?? null)
                ?? $this->parseBR($resumida['valor_total'] ?? null)
                ?? $this->parseBR($data['resumida']['valor_total'] ?? null));

        $emit = $this->mergePerson($data['emitente'] ?? null, $nfe['emitente'] ?? null, true);
        $dest = $this->mergePerson($data['destinatario'] ?? null, $nfe['destinatario'] ?? null, false);

        $colunas = [
            'status' => $statusSnap,
            'modelo' => $nfe['modelo'] ?? $resumida['modelo'] ?? substr($chave, 20, 2),
            'numero' => $nfe['numero'] ?? $resumida['numero'] ?? null,
            'serie' => $nfe['serie'] ?? $resumida['serie'] ?? null,
            'data_emissao' => $this->parseDataEmissao($nfe['data_emissao'] ?? ($resumida['data_emissao'] ?? null)),
            'natureza_operacao' => $nfe['emissao']['natureza_operacao'] ?? $resumida['natureza_operacao'] ?? null,
            'tipo_operacao' => $nfe['emissao']['tipo_operacao'] ?? $resumida['tipo_operacao'] ?? null,
            'valor_total' => $valorTotal,
            'emit_cnpj' => $emit['cnpj'] ?? null,
            'emit_nome' => $emit['nome'] ?? null,
            'emit_ie' => $emit['ie'] ?? null,
            'emit_uf' => $emit['uf'] ?? null,
            'emit_municipio' => $emit['municipio'] ?? null,
            'dest_cnpj' => $dest['cnpj'] ?? null,
            'dest_cpf' => $dest['cpf'] ?? null,
            'dest_nome' => $dest['nome'] ?? null,
            'dest_uf' => $dest['uf'] ?? null,
            'dest_municipio' => $dest['municipio'] ?? null,
            'nfe_completa' => (bool) ($data['nfe_completa'] ?? false),
            'consulta_sem_certificado' => ! ($data['nfe_completa'] ?? false),
            'xml_completo' => (bool) ($data['xml_baixado_com_certificado'] ?? false),
            'versao_xml' => $data['versao_xml'] ?? null,
            'url_xml' => $data['url_xml'] ?? null,
            'url_html' => $data['url_html'] ?? null,
            'url_site_receipt' => $data['site_receipt'] ?? null,
            'infosimples_code' => $code,
            'infosimples_code_message' => $codeMessage,
            'eventos' => $eventos,
            'totais' => $data['totais'] ?? [],
            'produtos' => (is_array($data['produtos'] ?? null) && $data['produtos']) ? $data['produtos'] : ($data['resumida']['produtos'] ?? []),
        ];

        $clearanceExtra = array_merge($colunas, [
            'situacao' => $situacaoRaw ?: null,
            'situacao_ambiente' => $situacaoAmbiente,
            'emitente' => $emit,
            'destinatario' => $dest,
        ]);

        return new DocumentoSnapshot(
            tipoDocumento: $tipo,
            chaveAcesso: $chave,
            status: $statusSnap,
            colunas: $colunas,
            payload: $this->payloadEnvelope($chave, $tipo, $statusSnap, $clearanceExtra, $code, $codeMessage, $billable, $header),
            persistivel: $this->persistivelPara($statusSnap),
            estornavel: false,
            billable: $billable,
            progressoMensagem: "NFe {$this->resumoChave($chave)}: {$statusSnap}",
        );
    }

    private function deriveStatus(array $eventos, ?string $situacaoFallback): string
    {
        $situacao = $this->limpar($situacaoFallback);
        $ultimo = $eventos ? $this->limpar($eventos[count($eventos) - 1]['evento'] ?? '') : '';

        if (str_contains($ultimo, 'CANCELAMENTO') || str_contains($ultimo, 'CANCELADA') || $situacao === 'CANCELADA') {
            return 'CANCELADA';
        }
        if (str_contains($ultimo, 'DENEGA') || $situacao === 'DENEGADA') {
            return 'DENEGADA';
        }
        if ($situacao === 'INUTILIZADA') {
            return 'INUTILIZADA';
        }
        if (str_contains($ultimo, 'AUTORIZACAO') || $situacao === 'AUTORIZADA') {
            return 'AUTORIZADA';
        }

        return $situacao ?: 'AUTORIZADA';
    }

    private function mergePerson(?array $det, ?array $sim, bool $includeIe): ?array
    {
        $det = $det ?? [];
        $sim = $sim ?? [];
        $r = [
            'cnpj' => $det['normalizado_cnpj'] ?? $sim['normalizado_cnpj'] ?? null,
            'cpf' => $det['normalizado_cpf'] ?? $sim['normalizado_cpf'] ?? null,
            'nome' => $det['nome_razao_social'] ?? $det['nome'] ?? $sim['nome'] ?? null,
            'uf' => $det['uf'] ?? $sim['uf'] ?? null,
            'municipio' => $det['municipio'] ?? $sim['municipio'] ?? null,
        ];
        if ($includeIe) {
            $r['ie'] = $det['ie'] ?? $sim['ie'] ?? null;
        }
        if (! $r['cnpj'] && ! $r['cpf'] && ! $r['nome']) {
            return null;
        }

        return $r;
    }

    private function colunasMinimas(string $chave, string $status, int $code, ?string $codeMessage): array
    {
        return [
            'status' => $status,
            'modelo' => strlen($chave) === 44 ? substr($chave, 20, 2) : null,
            'infosimples_code' => $code,
            'infosimples_code_message' => $codeMessage,
            'eventos' => [],
            'totais' => [],
            'produtos' => [],
            'consulta_sem_certificado' => true,
            'nfe_completa' => false,
            'xml_completo' => false,
        ];
    }

    private function payloadEnvelope(string $chave, string $tipo, string $status, array $clearance, int $code, ?string $codeMessage, bool $billable, array $header): array
    {
        return [
            'nfe_clearance' => array_merge(['status' => $status, 'chave_acesso' => $chave, 'tipo_documento' => $tipo], $clearance),
            'consultas_realizadas' => ['nfe_clearance'],
            'infosimples_code' => $code,
            'infosimples_code_message' => $codeMessage,
            'infosimples_billable' => $billable,
            'infosimples_price' => $header['price'] ?? '0',
        ];
    }
}
