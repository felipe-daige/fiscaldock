# n8n Workflows

Documentacao dos workflows de importacao no n8n: XML (NF-e/NFS-e/CT-e) e SPED (EFD Fiscal/Contribuicoes).

---

## Regra de Ouro: Templates JSON no n8n

Ao montar JSON no body do HTTP Request, use o formato correto:

| Tipo do valor | Formato no JSON | Exemplo |
|---------------|-----------------|---------|
| Numero | `{{ $json.campo }}` | `"progresso": {{ $json.progresso }}` |
| String | `"{{ $json.campo }}"` | `"tab_id": "{{ $json.tab_id }}"` |
| Boolean | `{{ $json.campo }}` | `"ativo": {{ $json.ativo }}` |
| Array | `{{ JSON.stringify($json.campo) }}` | `"ids": {{ JSON.stringify($json.ids) }}` |
| Object | `{{ JSON.stringify($json.campo) }}` | `"dados": {{ JSON.stringify($json.dados) }}` |
| Null | `null` | `"erro": null` |
| Nullable string | `{{ $json.campo ? '"' + $json.campo + '"' : 'null' }}` | Campo string ou null |

---

## Workflow XML (NF-e/NFS-e/CT-e)

### Fluxo Geral

```
WEBHOOK (ZIP) → EXTRAIR → LOOP XMLs → INSERT participantes/notas → UPDATE importacoes_xml → PROGRESSO 100%
```

### Webhook - Campos Recebidos

| Campo | Tipo | Descricao |
|-------|------|-----------|
| `user_id` | int | ID do usuario |
| `importacao_id` | int | ID em `importacoes_xml` |
| `tab_id` | string | UUID da aba (SSE) |
| `tipo_documento` | string | `NFE`, `NFSE` ou `CTE` |
| `cliente_id` | int/null | ID do cliente |
| `cliente_cnpj` | string/null | CNPJ para comparacao |
| `salvar_movimentacoes` | bool | Salvar em `notas_fiscais`? |
| `progress_url` | string | URL para progresso |
| `arquivo_url` | string | URL do ZIP |

### Tabelas Afetadas

#### 1. `participantes` (SEMPRE - emit E dest)

```sql
INSERT INTO participantes (
    user_id, cnpj, razao_social, nome_fantasia, uf, cep,
    municipio, telefone, crt, cliente_id, origem_tipo, origem_ref,
    created_at, updated_at
) VALUES (...)
ON CONFLICT (user_id, cnpj) DO UPDATE SET
    razao_social = COALESCE(EXCLUDED.razao_social, participantes.razao_social),
    nome_fantasia = COALESCE(EXCLUDED.nome_fantasia, participantes.nome_fantasia),
    uf = COALESCE(EXCLUDED.uf, participantes.uf),
    cep = COALESCE(EXCLUDED.cep, participantes.cep),
    municipio = COALESCE(EXCLUDED.municipio, participantes.municipio),
    telefone = COALESCE(EXCLUDED.telefone, participantes.telefone),
    crt = COALESCE(EXCLUDED.crt, participantes.crt),
    cliente_id = COALESCE(EXCLUDED.cliente_id, participantes.cliente_id),
    updated_at = NOW()
RETURNING id, (xmax = 0) AS is_new;
```

**Mapeamento JSON → DB:**

| Campo DB | emit | dest |
|----------|------|------|
| `cnpj` | `emit.CNPJ` | `dest.CNPJ` |
| `razao_social` | `emit.xNome` | `dest.xNome` |
| `nome_fantasia` | `emit.xFant` | `dest.xFant` |
| `uf` | `emit.enderEmit.UF` | `dest.enderDest.UF` |
| `cep` | `emit.enderEmit.CEP` | `dest.enderDest.CEP` |
| `municipio` | `emit.enderEmit.xMun` | `dest.enderDest.xMun` |
| `telefone` | `emit.enderEmit.fone` | `dest.enderDest.fone` |
| `crt` | `emit.CRT` | NULL |
| `cliente_id` | Se CNPJ = cliente_cnpj | Se CNPJ = cliente_cnpj |
| `origem_tipo` | `NFE`/`NFSE`/`CTE` | igual |

#### 2. `xml_chaves_processadas` (SEMPRE)

```sql
-- Verificar antes
SELECT id FROM xml_chaves_processadas
WHERE user_id = {{ user_id }} AND chave_acesso = '{{ chave }}'
LIMIT 1;

-- Inserir
INSERT INTO xml_chaves_processadas (user_id, importacao_xml_id, chave_acesso, created_at)
VALUES ({{ user_id }}, {{ importacao_id }}, '{{ chave }}', NOW())
ON CONFLICT DO NOTHING;
```

#### 3. `notas_fiscais` (OPCIONAL - se salvar_movimentacoes = true)

```sql
INSERT INTO notas_fiscais (
    user_id, cliente_id, chave_acesso, tipo_documento, numero_nota, serie,
    data_emissao, natureza_operacao, valor_total, tipo_nota, finalidade,
    chave_referenciada,
    emit_cnpj, emit_razao_social, emit_uf, emit_participante_id,
    dest_cnpj, dest_razao_social, dest_uf, dest_participante_id,
    icms_valor, icms_st_valor, pis_valor, cofins_valor, ipi_valor, tributos_total,
    payload, created_at, updated_at
) VALUES (...)
ON CONFLICT (user_id, chave_acesso) DO UPDATE SET updated_at = NOW();
```

**Mapeamento JSON → DB:**

| Campo DB | Origem JSON |
|----------|-------------|
| `chave_acesso` | `protNFe.infProt.chNFe` |
| `numero_nota` | `ide.nNF` |
| `serie` | `ide.serie` |
| `data_emissao` | `ide.dhEmi` |
| `valor_total` | `total.ICMSTot.vNF` |
| `tipo_nota` | `ide.tpNF` (0=entrada, 1=saida) |
| `finalidade` | `ide.finNFe` (1=normal, 4=devolucao) |
| `payload` | TODO O JSON |

#### 4. `importacoes_xml` (ATUALIZAR AO FINAL)

```sql
UPDATE importacoes_xml SET
    xmls_processados = {{ $json.xmls_processados }},
    xmls_novos = {{ $json.xmls_novos }},
    xmls_duplicados_processados = {{ $json.xmls_duplicados_processados }},
    xmls_com_erro = {{ $json.xmls_com_erro }},
    participantes_novos = {{ $json.participantes_novos }},
    participantes_atualizados = {{ $json.participantes_atualizados }},
    valor_total = {{ $json.valor_total }},
    participante_ids = {{ $json.participante_ids_sql }},
    erro_mensagem = {{ $json.erro_mensagem_sql }},
    erros_detalhados = {{ $json.erros_detalhados_sql }},
    status = '{{ $json.status }}',
    concluido_em = NOW(),
    updated_at = NOW()
WHERE id = {{ $json.importacao_id }};
```

**Campos `*_sql` (pre-formatados):** Use os campos do Code Node para evitar erros com NULL.

### Code Node: Gerar Resumo

Ver `docs/n8n-code-node-resumo.js` - Processa array de notas e gera resumo para UPDATE e HTTP Request final.

---

## Workflow SPED (EFD Fiscal/Contribuicoes)

### Fluxo Geral

```
WEBHOOK (TXT) → PARSE SPED → EXTRAIR CNPJs → INSERT participantes → PROGRESSO 100%
```

### Participantes - Campos origem

| Valor origem_tipo | Quando usar |
|-------------------|-------------|
| `SPED_EFD_FISCAL` | Arquivo SPED EFD Fiscal |
| `SPED_EFD_CONTRIB` | Arquivo SPED EFD Contribuicoes |

**Exemplo origem_ref:**

```json
{"arquivo": "SPED_EFD_2024.txt", "importado_em": "2026-01-18T10:30:00Z"}
```

### SQL para INSERT participantes

```sql
INSERT INTO participantes (
    user_id, cliente_id, cnpj, razao_social, uf,
    origem_tipo, origem_ref, created_at, updated_at
) VALUES (
    {{ $json.user_id }},
    {{ $json.cliente_id || 'NULL' }},
    '{{ $json.cnpj }}',
    '{{ $json.razao_social }}',
    '{{ $json.uf }}',
    'SPED_EFD_FISCAL',
    '{"arquivo": "{{ $json.filename }}", "importado_em": "{{ $now.toISO() }}"}'::jsonb,
    NOW(), NOW()
)
ON CONFLICT (user_id, cnpj) DO UPDATE SET
    razao_social = EXCLUDED.razao_social,
    uf = EXCLUDED.uf,
    updated_at = NOW();
```

---

## API de Progresso (Compartilhada)

### Endpoints

| Workflow | Endpoint |
|----------|----------|
| XML | `POST /api/monitoramento/xml/importacao/progress` |
| SPED | `POST /api/monitoramento/sped/importacao-txt/progress` |

**Header:** `X-API-Token: {API_TOKEN}`

### Payload - Campos

| Campo | Tipo | Obrigatorio | Descricao |
|-------|------|-------------|-----------|
| `user_id` | int | Sim | ID do usuario |
| `tab_id` | string(36) | Sim | UUID da aba |
| `progresso` | int(0-100) | Sim | Percentual |
| `mensagem` | string(255) | Nao | Texto UI |
| `status` | enum | Sim | `iniciando`, `processando`, `concluido`, `erro` |
| `importacao_id` | int | Nao | ID do registro |
| `error_code` | string(50) | Nao | Codigo erro |
| `error_message` | string(500) | Nao | Descricao erro |
| `dados` | object | Nao | Dados extras |

### Payload - Durante Processamento

```json
{
  "user_id": {{ $json.user_id }},
  "tab_id": "{{ $json.tab_id }}",
  "progresso": {{ $json.progresso }},
  "mensagem": "Processando XML {{ $json.atual }} de {{ $json.total }}...",
  "status": "processando",
  "importacao_id": {{ $json.importacao_id }}
}
```

### Payload - Conclusao (100%)

```json
{
  "user_id": {{ $json.user_id }},
  "tab_id": "{{ $json.tab_id }}",
  "progresso": 100,
  "mensagem": "{{ $json.mensagem }}",
  "status": "{{ $json.status }}",
  "importacao_id": {{ $json.importacao_id }},
  "dados": {
    "xmls_processados": {{ $json.xmls_processados }},
    "xmls_com_erro": {{ $json.xmls_com_erro }},
    "participantes_novos": {{ $json.participantes_novos }},
    "participantes_atualizados": {{ $json.participantes_atualizados }},
    "participante_ids": {{ JSON.stringify($json.participante_ids || []) }}
  }
}
```

### Payload - Erro

```json
{
  "user_id": {{ $json.user_id }},
  "tab_id": "{{ $json.tab_id }}",
  "progresso": {{ $json.progresso }},
  "mensagem": "Erro no processamento",
  "status": "erro",
  "error_code": "{{ $json.error_code }}",
  "error_message": "{{ $json.error_message }}"
}
```

### Codigos de Erro

| error_code | Descricao | Quando usar |
|------------|-----------|-------------|
| `PARSE_ERROR` | XML/SPED mal formado | Falha no parsing |
| `INVALID_XML` | Tipo desconhecido | Nao e NF-e/NFS-e/CT-e |
| `INVALID_SPED` | Arquivo invalido | Formato nao reconhecido |
| `DB_ERROR` | Erro ao salvar | Falha PostgreSQL |
| `INFOSIMPLES_TIMEOUT` | Timeout API | InfoSimples nao respondeu |
| `INFOSIMPLES_ERROR` | Erro API | HTTP 4xx/5xx |
| `NO_PARTICIPANTS` | Nenhum participante | SPED sem CNPJs |
| `UNKNOWN_ERROR` | Erro desconhecido | Fallback |

### Etapas de Progresso (SPED)

| Etapa | % | Status | Dados |
|-------|---|--------|-------|
| Identificacao | 5 | processando | `nome_empresa`, `cnpj_empresa` |
| Totais | 10 | processando | `total_participantes`, `total_cnpjs` |
| Processando | 20-98 | processando | - |
| Salvando | 99 | processando | - |
| Concluido | 100 | concluido | `total_a_analisar`, `participante_ids` |

---

## Cache e SSE

**Chave cache:** `progresso:{user_id}:{tab_id}` (TTL 10min)

**SSE endpoints:**
- SPED: `/app/monitoramento/progresso/stream?tab_id=xxx`
- XML: `/app/monitoramento/xml/progresso/stream?tab_id=xxx`

---

## Troubleshooting

### Erro 404 no endpoint de progresso

**Causa:** Imagem Docker desatualizada.

```bash
docker compose -f docker-compose.dev.yml pull
docker compose -f docker-compose.dev.yml up -d
docker compose exec app php artisan route:clear
```

### Erro "JSON parameter needs to be valid JSON"

**Causa:** Arrays nao serializados.

**Solucao:** Use `JSON.stringify()` em arrays:

```json
"participante_ids": {{ JSON.stringify($json.ids || []) }}
```

---

## Arquivos Relacionados

| Arquivo | Funcao |
|---------|--------|
| `docs/n8n-code-node-resumo.js` | Code Node com campos `*_sql` |
| `app/Http/Controllers/Api/DataReceiverController.php` | Recebe progresso |
| `app/Http/Controllers/Dashboard/MonitoramentoController.php` | SSE SPED |
| `app/Http/Controllers/Dashboard/XmlImportacaoController.php` | SSE XML |
| `routes/api.php` | Rotas API |
