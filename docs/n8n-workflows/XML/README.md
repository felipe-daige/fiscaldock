# Workflow n8n: Importacao XML (NF-e/NFS-e/CT-e)

## Fluxo do Workflow

```
WEBHOOK (ZIP/XMLs)
    |
EXTRAIR ZIP
    |
LOOP: Para cada XML
    |-- Parse XML
    |-- Verificar duplicata (SELECT xml_notas WHERE user_id + nfe_id)
    |-- Se duplicada: acumular em lista, continuar
    |-- Se nova:
    |   |-- Buscar titularidade (emit + dest)
    |   |   |-- SELECT clientes (is_empresa_propria?)
    |   |   |-- SELECT clientes (cliente regular?)
    |   |   +-- SELECT participantes (ja existe?)
    |   |-- Switch: setar metadata de titularidade
    |   |-- UPSERT participantes (emit + dest, se nao for 'novo')
    |   |-- INSERT xml_notas (se salvar_movimentacoes)
    |   +-- Acumular contadores
    |-- Enviar progresso (a cada 5 XMLs)
    +-- Continuar loop
    |
UPDATE xml_importacoes (status=concluido)
    |
ENVIAR PROGRESSO FINAL (100%)
```

---

## Webhook: Campos Recebidos

| Campo | Tipo | Obrigatorio | Descricao |
|-------|------|-------------|-----------|
| `user_id` | int | Sim | ID do usuario |
| `importacao_id` | int | Sim | ID em `xml_importacoes` |
| `tab_id` | string(36) | Sim | UUID da aba (para SSE) |
| `tipo_documento` | string | Sim | `NFE`, `NFSE` ou `CTE` |
| `cliente_id` | int/null | Nao | ID do cliente associado |
| `salvar_movimentacoes` | bool | Sim | Se deve salvar em `xml_notas` |
| `progress_url` | string | Sim | URL para enviar progresso |
| `arquivo_url` | string | Sim | URL do arquivo ZIP |

---

## Passo 1: Verificar Duplicata

Query no node PostgreSQL antes de qualquer processamento:

```sql
SELECT id, created_at
FROM xml_notas
WHERE user_id = $1 AND nfe_id = $2
LIMIT 1;
```

- Se retornar resultado: nota duplicada, acumular em `duplicadas_detectadas[]` e pular
- Se nao retornar: nota nova, seguir para titularidade

---

## Passo 2: Buscar Titularidade (emit + dest)

Para cada nota nova, verificar se os CNPJs do emitente e destinatario ja existem no sistema.

### Metadata de Titularidade

Tres campos adicionados ao objeto, todos `null` por padrao:

```javascript
{
  titularidade_user: null,          // "emit" | "dest" | null
  titularidade_cliente: null,       // "emit" | "dest" | null
  titularidade_participante: null   // "emit" | "dest" | null
}
```

| Campo | Quando preenchido | Valor |
|-------|-------------------|-------|
| `titularidade_user` | CNPJ encontrado em `clientes` com `is_empresa_propria = true` | `"emit"` ou `"dest"` |
| `titularidade_cliente` | CNPJ encontrado em `clientes` com `is_empresa_propria = false` | `"emit"` ou `"dest"` |
| `titularidade_participante` | CNPJ encontrado em `participantes` (nao esta em `clientes`) | `"emit"` ou `"dest"` |

Se o CNPJ nao for encontrado em nenhuma tabela, todos ficam `null` → CNPJ novo.

### Queries de Busca

**Buscar em clientes (emit):**
```sql
SELECT id, is_empresa_propria
FROM clientes
WHERE REGEXP_REPLACE(documento, '[^0-9]', '', 'g') = $1
  AND user_id = $2
LIMIT 1;
```

**Buscar em clientes (dest):**
```sql
SELECT id, is_empresa_propria
FROM clientes
WHERE REGEXP_REPLACE(documento, '[^0-9]', '', 'g') = $1
  AND user_id = $2
LIMIT 1;
```

**Buscar em participantes (emit):**
```sql
SELECT id, cliente_id
FROM participantes
WHERE cnpj = $1 AND user_id = $2
LIMIT 1;
```

**Buscar em participantes (dest):**
```sql
SELECT id, cliente_id
FROM participantes
WHERE cnpj = $1 AND user_id = $2
LIMIT 1;
```

### Logica do Switch

Com os resultados das queries, usar Switch node para setar os campos:

```
Se emit_cnpj encontrado em clientes:
  Se is_empresa_propria = true  -> titularidade_user = "emit"
  Se is_empresa_propria = false -> titularidade_cliente = "emit"
Senao se emit_cnpj encontrado em participantes:
  -> titularidade_participante = "emit"
Senao:
  -> CNPJ novo (nao setar nada, fica null)

Mesma logica para dest_cnpj, setando "dest" nos campos.
```

**Nota:** Ambos os campos podem estar preenchidos simultaneamente. Exemplo: `titularidade_user = "emit"` e `titularidade_cliente = "dest"` (empresa propria emitiu nota para um cliente).

### Resultado para Decisao

| titularidade_* | Acao |
|----------------|------|
| `"emit"` ou `"dest"` preenchido | UPSERT participante para esse CNPJ |
| Todos `null` para um CNPJ | NAO criar participante, acumular em `cnpjs_novos` |

---

## Passo 3: UPSERT Participantes

So executar para CNPJs identificados (titularidade != null).

```sql
INSERT INTO participantes (
    user_id, cnpj, razao_social, nome_fantasia, uf, cep,
    municipio, telefone, crt, cliente_id, importacao_xml_id,
    origem_tipo, origem_ref, created_at, updated_at
) VALUES (
    $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13::jsonb, NOW(), NOW()
)
ON CONFLICT (user_id, cnpj) DO UPDATE SET
    razao_social = COALESCE(NULLIF(EXCLUDED.razao_social, ''), participantes.razao_social),
    nome_fantasia = COALESCE(EXCLUDED.nome_fantasia, participantes.nome_fantasia),
    uf = COALESCE(EXCLUDED.uf, participantes.uf),
    cep = COALESCE(EXCLUDED.cep, participantes.cep),
    municipio = COALESCE(EXCLUDED.municipio, participantes.municipio),
    telefone = COALESCE(EXCLUDED.telefone, participantes.telefone),
    crt = COALESCE(EXCLUDED.crt, participantes.crt),
    updated_at = NOW()
RETURNING id, (xmax = 0) AS is_new;
```

**Nota sobre CRT:** Apenas o emitente (`emit.CRT`) possui CRT no XML. Para destinatario, passar `NULL`.

### Mapeamento XML -> Participante

| Campo DB | emit | dest |
|----------|------|------|
| `cnpj` | `emit.CNPJ` | `dest.CNPJ` |
| `razao_social` | `emit.xNome` | `dest.xNome` |
| `nome_fantasia` | `emit.xFant` | `dest.xFant` |
| `uf` | `emit.enderEmit.UF` | `dest.enderDest.UF` |
| `cep` | `emit.enderEmit.CEP` | `dest.enderDest.CEP` |
| `municipio` | `emit.enderEmit.xMun` | `dest.enderDest.xMun` |
| `telefone` | `emit.enderEmit.fone` | `dest.enderDest.fone` |
| `crt` | `emit.CRT` | `NULL` |

---

## Passo 4: INSERT xml_notas

Apenas quando `salvar_movimentacoes = true`.

Para CNPJs novos (todos titularidade null), `emit_participante_id` e/ou `dest_participante_id` serao `NULL`.

```sql
INSERT INTO xml_notas (
    user_id, cliente_id, importacao_xml_id,
    nfe_id, numero_nota, serie, data_emissao,
    valor_total, tipo_nota, finalidade, modelo_doc,
    emit_participante_id, emit_cliente_id,
    dest_participante_id, dest_cliente_id,
    icms_valor, icms_st_valor, pis_valor, cofins_valor, ipi_valor,
    payload, created_at, updated_at
) VALUES (
    $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11,
    $12, $13, $14, $15,
    $16, $17, $18, $19, $20,
    $21::jsonb, NOW(), NOW()
)
RETURNING id;
```

### Mapeamento XML -> xml_notas

| Campo DB | Caminho XML |
|----------|-------------|
| `nfe_id` | `protNFe.infProt.chNFe` |
| `numero_nota` | `ide.nNF` |
| `serie` | `ide.serie` |
| `data_emissao` | `ide.dhEmi` |
| `valor_total` | `total.ICMSTot.vNF` |
| `tipo_nota` | `ide.tpNF` (0=entrada, 1=saida) |
| `finalidade` | `ide.finNFe` (1=normal, 2=compl, 3=ajuste, 4=devolucao) |
| `modelo_doc` | `ide.mod` (55=NFe, 57=CTe) |
| `icms_valor` | `total.ICMSTot.vICMS` |
| `icms_st_valor` | `total.ICMSTot.vST` |
| `pis_valor` | `total.ICMSTot.vPIS` |
| `cofins_valor` | `total.ICMSTot.vCOFINS` |
| `ipi_valor` | `total.ICMSTot.vIPI` |
| `payload` | JSON completo do XML |

---

## Passo 5: Envio de Progresso

### Durante Processamento (a cada 5 XMLs)

```
POST {progress_url}
Header: X-API-Token: {API_TOKEN}
```

```json
{
  "user_id": 1,
  "tab_id": "uuid",
  "importacao_id": 123,
  "progresso": 45,
  "status": "processando",
  "mensagem": "Processando 45/100: NF 12345 - EMPRESA LTDA",
  "dados": {
    "xml_atual": 45,
    "total_xmls": 100,
    "novas": 42,
    "duplicadas": 2,
    "erros": 1,
    "participantes_novos": 8,
    "nota_atual": {
      "numero": "12345",
      "emit_razao": "EMPRESA LTDA",
      "emit_cnpj": "11222333000181",
      "valor": 1500.00
    }
  }
}
```

### Payload Final (status=concluido)

```json
{
  "user_id": 1,
  "tab_id": "uuid",
  "importacao_id": 123,
  "progresso": 100,
  "status": "concluido",
  "mensagem": "Importacao concluida! 95 notas, 3 duplicadas, 2 erros.",
  "dados": {
    "xmls_processados": 100,
    "xmls_novos": 95,
    "xmls_duplicados": 3,
    "xmls_com_erro": 2,
    "participantes_novos": 15,
    "participantes_atualizados": 8,
    "valor_total": 125000.50,
    "resumo_titularidade": {
      "propria_emit": 22,
      "propria_dest": 35,
      "cliente_emit": 18,
      "cliente_dest": 12,
      "terceiro_emit": 55,
      "terceiro_dest": 48
    },
    "duplicadas_detectadas": [
      {
        "nfe_id": "35260111222333000181550010000000011234567890",
        "numero_nota": "12345",
        "emit_cnpj": "11222333000181",
        "emit_razao": "EMPRESA LTDA",
        "data_emissao": "2026-01-15",
        "valor": 1500.00,
        "existente_id": 456,
        "existente_importado_em": "2026-01-10T14:30:00Z"
      }
    ],
    "erros_detectados": [
      {
        "arquivo": "nfe_001.xml",
        "erro": "XML mal formado",
        "detalhe": "Tag <emit> nao fechada"
      }
    ],
    "participante_ids": [1, 2, 3, 4, 5],
    "cnpjs_novos": [
      {
        "cnpj": "12345678000190",
        "razao_social": "EMPRESA NOVA LTDA",
        "nome_fantasia": "EMPRESA NOVA",
        "uf": "SP",
        "cep": "01310100",
        "municipio": "SAO PAULO",
        "telefone": "1133334444",
        "crt": null,
        "visto_como": ["emit", "dest"],
        "contagem_notas": 5
      }
    ],
    "cnpjs_novos_truncated": false,
    "cnpjs_novos_total": 15
  }
}
```

---

## Passo 6: UPDATE xml_importacoes (Final)

```sql
UPDATE xml_importacoes SET
    status = 'concluido',
    total_xmls = $2,
    xmls_processados = $3,
    novos = $4,
    duplicados = $5,
    erros_count = $6,
    participantes_novos = $7,
    participantes_atualizados = $8,
    participante_ids = $9::jsonb,
    processado_em = NOW(),
    updated_at = NOW()
WHERE id = $1;
```

**Em caso de erro:**

```sql
UPDATE xml_importacoes SET
    status = 'erro',
    error_code = $2,
    error_message = $3,
    processado_em = NOW(),
    updated_at = NOW()
WHERE id = $1;
```

---

## Codigos de Erro

| Codigo | Descricao |
|--------|-----------|
| `PARSE_ERROR` | XML mal formado |
| `INVALID_XML` | Tipo de documento nao reconhecido |
| `DB_ERROR` | Erro ao salvar no banco |
| `MISSING_FIELDS` | Campos obrigatorios ausentes |

---

## Indices Recomendados

```sql
-- Busca de duplicatas
CREATE INDEX IF NOT EXISTS idx_xml_notas_user_chave
ON xml_notas (user_id, nfe_id);

-- Busca de titularidade (clientes)
CREATE INDEX IF NOT EXISTS idx_clientes_documento_clean
ON clientes (user_id, REGEXP_REPLACE(documento, '[^0-9]', '', 'g'));

-- Busca de titularidade (participantes)
CREATE INDEX IF NOT EXISTS idx_participantes_user_cnpj
ON participantes (user_id, cnpj);
```
