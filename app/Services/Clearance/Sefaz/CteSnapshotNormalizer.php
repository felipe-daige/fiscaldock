<?php

namespace App\Services\Clearance\Sefaz;

class CteSnapshotNormalizer extends SnapshotNormalizer
{
    public function normalizar(array $raw, string $status, string $chaveAcesso, bool $billable): DocumentoSnapshot
    {
        $chave = substr($this->digits($chaveAcesso), 0, 44);
        $header = $raw['header'] ?? [];
        $billable = (bool) ($header['billable'] ?? $billable);
        $code = (int) ($raw['code'] ?? 0);
        $codeMessage = $raw['code_message'] ?? null;
        $errors = is_array($raw['errors'] ?? null) ? $raw['errors'] : [];
        $mensagemErros = $errors ? implode(' | ', $errors) : $codeMessage;

        // Ramos não-sucesso: shape mínimo (Code Nodes 2-6).
        if ($status !== 'sucesso') {
            $statusSnap = $this->statusNaoSucesso($status);

            return new DocumentoSnapshot(
                tipoDocumento: 'CTE',
                chaveAcesso: $chave,
                status: $statusSnap,
                colunas: $this->colunasMinimas($chave, $statusSnap, $code, $codeMessage),
                payload: $this->payloadEnvelope($chave, $statusSnap, [], $code, $codeMessage, $billable, $header),
                persistivel: $this->persistivelPara($statusSnap),
                estornavel: $this->estornavelPara($statusSnap, $billable),
                billable: $billable,
                errorCode: in_array($statusSnap, ['ERRO_PARAMETRO', 'TIMEOUT', 'ERRO_INTEGRACAO'], true) ? $statusSnap : null,
                errorMessage: in_array($statusSnap, ['INDETERMINADO', 'ERRO_PARAMETRO', 'TIMEOUT', 'ERRO_INTEGRACAO'], true) ? $mensagemErros : null,
                progressoMensagem: "CTe {$this->resumoChave($chave)}: {$statusSnap}",
            );
        }

        // Sucesso (Code Node 1).
        $data = $raw['data'][0] ?? [];
        $cte = $data['cte'] ?? [];
        $resumida = $data['resumida']['cte'] ?? [];
        $eventos = is_array($cte['eventos'] ?? null) ? $cte['eventos'] : [];
        $statusSnap = $this->deriveStatus($eventos, $cte['situacao'] ?? null);

        $nfesReferenciadas = collect($data['carga']['nfes'] ?? [])
            ->map(fn ($n) => is_string($n) ? $this->digits($n) : $this->digits($n['normalizado_chave'] ?? ($n['chave'] ?? '')))
            ->filter(fn ($n) => strlen($n) === 44)
            ->values()
            ->all();

        $emit = $this->mergePerson($data['emitente'] ?? null, $cte['emitente'] ?? null, true);
        $tomador = $this->mergePerson($data['tomador'] ?? null, $cte['tomador'] ?? null, true);
        $remet = $this->mergePerson($data['remetente'] ?? null, $cte['remetente'] ?? null, false);
        $dest = $this->mergePerson($data['destinatario'] ?? null, $cte['destinatario'] ?? null, false);
        $expedidor = $this->mergePerson($data['expedidor'] ?? null, $cte['expedidor'] ?? null, false);
        $recebedor = $this->mergePerson($data['recebedor'] ?? null, $cte['recebedor'] ?? null, false);

        $valorPrestacao = $resumida['normalizado_valor']
            ?? $this->parseBR($data['totais']['valor_prestacao_servico'] ?? null)
            ?? $this->parseBR($cte['valores']['valor_total'] ?? null)
            ?? $this->parseBR($cte['valores']['normalizado_valor_total'] ?? null);

        $colunas = [
            'status' => $statusSnap,
            'modelo' => $resumida['modelo'] ?? (strlen($chave) === 44 ? substr($chave, 20, 2) : null),
            'numero' => $resumida['numero'] ?? $cte['numero'] ?? null,
            'serie' => $resumida['serie'] ?? $cte['serie'] ?? null,
            'data_emissao' => $this->parseDataEmissao($resumida['data_emissao'] ?? ($cte['data_emissao'] ?? null)),
            'natureza_operacao' => $resumida['natureza_operacao'] ?? $cte['caracteristicas']['natureza'] ?? null,
            'tipo_servico' => $resumida['tipo'] ?? $cte['caracteristicas']['tipo_servico'] ?? null,
            'cfop' => $resumida['cfop'] ?? $cte['caracteristicas']['cfop'] ?? null,
            'modal' => $resumida['modal'] ?? $cte['caracteristicas']['modal'] ?? null,
            'uf_inicio' => $resumida['uf_inicio'] ?? null,
            'uf_fim' => $resumida['uf_fim'] ?? null,
            'valor_prestacao' => $valorPrestacao,
            'valor_carga' => $data['carga']['normalizado_valor'] ?? null,
            'emit_cnpj' => $emit['cnpj'] ?? null,
            'emit_nome' => $emit['nome'] ?? null,
            'emit_ie' => $emit['ie'] ?? null,
            'emit_uf' => $emit['uf'] ?? null,
            'emit_municipio' => $emit['municipio'] ?? null,
            'tomador_cnpj' => $tomador['cnpj'] ?? null,
            'tomador_cpf' => $tomador['cpf'] ?? null,
            'tomador_nome' => $tomador['nome'] ?? null,
            'tomador_uf' => $tomador['uf'] ?? null,
            'tomador_municipio' => $tomador['municipio'] ?? null,
            'remet_cnpj' => $remet['cnpj'] ?? null,
            'remet_cpf' => $remet['cpf'] ?? null,
            'remet_nome' => $remet['nome'] ?? null,
            'remet_uf' => $remet['uf'] ?? null,
            'dest_cnpj' => $dest['cnpj'] ?? null,
            'dest_cpf' => $dest['cpf'] ?? null,
            'dest_nome' => $dest['nome'] ?? null,
            'dest_uf' => $dest['uf'] ?? null,
            'expedidor_cnpj' => $expedidor['cnpj'] ?? null,
            'recebedor_cnpj' => $recebedor['cnpj'] ?? null,
            'nfes_referenciadas_count' => count($nfesReferenciadas),
            'cte_completa' => (bool) ($data['cte_completo'] ?? false),
            'consulta_sem_certificado' => ! ($data['xml_baixado_com_certificado'] ?? false),
            'xml_completo' => (bool) ($data['xml_baixado_com_certificado'] ?? false),
            'versao_xml' => $cte['versao_xml'] ?? null,
            'url_xml' => $data['url_xml'] ?? null,
            'url_html' => $data['url_html'] ?? null,
            'url_site_receipt' => $data['site_receipt'] ?? null,
            'infosimples_code' => $code,
            'infosimples_code_message' => $codeMessage,
            'eventos' => $eventos,
            'componentes' => $data['totais']['componentes'] ?? [],
            'nfes_referenciadas' => $nfesReferenciadas,
            'totais' => $data['totais'] ?? [],
            'rodoviario' => $data['rodoviario'] ?? null,
            'aquaviario' => $data['aquaviario'] ?? null,
        ];

        $clearanceExtra = array_merge($colunas, [
            'situacao' => $cte['situacao'] ?? null,
            'situacao_ambiente' => $cte['situacao_ambiente'] ?? null,
            'emitente' => $emit,
            'tomador' => $tomador,
            'remetente' => $remet,
            'destinatario' => $dest,
            'expedidor' => $expedidor,
            'recebedor' => $recebedor,
        ]);

        return new DocumentoSnapshot(
            tipoDocumento: 'CTE',
            chaveAcesso: $chave,
            status: $statusSnap,
            colunas: $colunas,
            payload: $this->payloadEnvelope($chave, $statusSnap, $clearanceExtra, $code, $codeMessage, $billable, $header),
            persistivel: $this->persistivelPara($statusSnap),
            estornavel: false,
            billable: $billable,
            progressoMensagem: "CTe {$this->resumoChave($chave)}: {$statusSnap}",
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
            'nfes_referenciadas_count' => 0,
            'eventos' => [],
            'componentes' => [],
            'nfes_referenciadas' => [],
            'totais' => [],
            'consulta_sem_certificado' => true,
            'cte_completa' => false,
            'xml_completo' => false,
        ];
    }

    private function payloadEnvelope(string $chave, string $status, array $clearance, int $code, ?string $codeMessage, bool $billable, array $header): array
    {
        return [
            'cte_clearance' => array_merge(['status' => $status, 'chave_acesso' => $chave, 'tipo_documento' => 'CTE'], $clearance),
            'consultas_realizadas' => ['cte_clearance'],
            'infosimples_code' => $code,
            'infosimples_code_message' => $codeMessage,
            'infosimples_billable' => $billable,
            'infosimples_price' => $header['price'] ?? '0',
        ];
    }
}
