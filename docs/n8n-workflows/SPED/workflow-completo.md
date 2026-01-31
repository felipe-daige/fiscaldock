# Workflow SPED Completo com Validação de Tipo

## Visão Geral

Workflow completo de importação SPED com validação de tipo de arquivo integrada.

---

## Diagrama do Workflow

```
┌─────────────────────────────────────────────────────────────────┐
│                         WEBHOOK START                           │
│                 (Recebe dados do Laravel)                       │
│                                                                 │
│ Payload:                                                        │
│ - user_id, tab_id, importacao_id                               │
│ - tipo_efd: "EFD Fiscal" ou "EFD Contribuições"                │
│ - arquivo_base64                                               │
│ - progress_url                                                 │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│              [Code] Validar Tipo de Arquivo SPED                │
│                                                                 │
│ Ações:                                                          │
│ 1. Decodifica base64 (primeiras 50 linhas)                     │
│ 2. Detecta tipo real via registro |0000|                       │
│ 3. Fallback: detecta por registros C100/M100/M500              │
│ 4. Compara tipo_detectado com tipo_efd                         │
│                                                                 │
│ Output:                                                         │
│ - tipo_valido: true/false                                      │
│ - tipo_detectado, tipo_esperado                                │
│ - erro, error_code, error_message (se inválido)                │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│                [IF] Tipo SPED Válido?                           │
│                                                                 │
│ Condition: tipo_valido === true AND erro !== true              │
└────┬─────────────────────────────────────────────────────┬──────┘
     │ TRUE                                                │ FALSE
     │                                                     │
     │                                                     ▼
     │                          ┌──────────────────────────────────┐
     │                          │ [HTTP] Enviar Erro ao Laravel    │
     │                          │                                  │
     │                          │ POST {{ $json.progress_url }}    │
     │                          │ Headers:                         │
     │                          │   X-API-Token: {{ $env.API... }}│
     │                          │                                  │
     │                          │ Body:                            │
     │                          │ {                                │
     │                          │   "user_id": {{ $json.user_id }},│
     │                          │   "tab_id": "{{ $json.tab_id }}", │
     │                          │   "progresso": 0,                │
     │                          │   "status": "erro",              │
     │                          │   "error_code": "INVALID_SPED",  │
     │                          │   "error_message": "...",        │
     │                          │   "dados": {...}                 │
     │                          │ }                                │
     │                          │                                  │
     │                          │ Retry: 3x, 1s interval           │
     │                          └──────────┬───────────────────────┘
     │                                     │
     │                                     ▼
     │                          ┌──────────────────────────────────┐
     │                          │ [Stop & Error]                   │
     │                          │ Workflow encerra com sucesso     │
     │                          │ (erro foi enviado ao Laravel)    │
     │                          └──────────────────────────────────┘
     │
     ▼
┌─────────────────────────────────────────────────────────────────┐
│           [Code] Enviar Progresso - Iniciando                   │
│                                                                 │
│ POST {{ $json.progress_url }}                                   │
│ {                                                               │
│   "user_id": {{ $json.user_id }},                               │
│   "tab_id": "{{ $json.tab_id }}",                               │
│   "progresso": 0,                                               │
│   "status": "iniciando",                                        │
│   "mensagem": "Iniciando processamento do arquivo SPED..."      │
│ }                                                               │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│              [Code] Decodificar Base64 Completo                 │
│                                                                 │
│ const fullContent = Buffer.from(                                │
│   $json.arquivo_base64, 'base64'                                │
│ ).toString('utf-8');                                            │
│                                                                 │
│ const lines = fullContent.split('\n');                          │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│           [Code] Extrair Participantes (CNPJs/CPFs)             │
│                                                                 │
│ Registros analisados:                                           │
│ - |0150| - Participantes (Fornecedores/Clientes)               │
│ - |0000| - Empresa declarante                                  │
│                                                                 │
│ Output:                                                         │
│ - Array de participantes {cnpj, razao_social, uf, ...}         │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│              [Loop Over Items] Processar Participantes          │
│                                                                 │
│ Para cada participante:                                         │
│   ├─ Sanitizar dados (trim, uppercase)                         │
│   ├─ UPSERT em PostgreSQL (participantes table)                │
│   └─ Enviar progresso a cada 10%                               │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│           [Set] Preparar Dados para SQL (Sanitização)           │
│                                                                 │
│ - cnpj: trim, apenas números                                   │
│ - razao_social: trim, uppercase                                │
│ - uf: uppercase                                                │
│ - origem_tipo: "SPED_EFD_FISCAL" ou "SPED_EFD_CONTRIB"         │
│ - origem_ref: JSON com metadata                                │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│              [Postgres] UPSERT Participante                     │
│                                                                 │
│ INSERT INTO participantes (                                     │
│   user_id, cnpj, razao_social, nome_fantasia, uf, cep,         │
│   municipio, telefone, crt, cliente_id, importacao_sped_id,    │
│   origem_tipo, origem_ref, created_at, updated_at              │
│ ) VALUES ($1, $2, ..., NOW(), NOW())                            │
│ ON CONFLICT (user_id, cnpj) DO UPDATE SET                      │
│   razao_social = COALESCE(EXCLUDED.razao_social, ...),         │
│   updated_at = NOW()                                            │
│ RETURNING id, (xmax = 0) AS is_new;                             │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│         [Code] Calcular Progresso e Enviar ao Laravel           │
│                                                                 │
│ progresso_pct = Math.floor(                                     │
│   (index_atual / total_participantes) * 100                     │
│ );                                                              │
│                                                                 │
│ Se progresso_pct % 10 === 0:                                    │
│   POST progress_url com status "processando"                    │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
                    [Fim do Loop]
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│           [Code] Agregar Resultados Finais                      │
│                                                                 │
│ - total_participantes: count total                             │
│ - novos: count where is_new = true                             │
│ - duplicados: count where is_new = false                       │
│ - participante_ids: array de IDs inseridos                     │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│        [Postgres] UPDATE importacoes_sped (Status Final)        │
│                                                                 │
│ UPDATE importacoes_sped SET                                     │
│   status = 'concluido',                                         │
│   total_participantes = $1,                                     │
│   novos = $2,                                                   │
│   duplicados = $3,                                              │
│   participante_ids = $4::jsonb,                                 │
│   processado_em = NOW()                                         │
│ WHERE id = {{ $json.importacao_id }};                           │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│           [HTTP] Enviar Progresso Final - Concluído             │
│                                                                 │
│ POST {{ $json.progress_url }}                                   │
│ {                                                               │
│   "user_id": {{ $json.user_id }},                               │
│   "tab_id": "{{ $json.tab_id }}",                               │
│   "progresso": 100,                                             │
│   "status": "concluido",                                        │
│   "mensagem": "Processamento concluído com sucesso!",           │
│   "dados": {                                                    │
│     "total_participantes": 150,                                 │
│     "novos": 30,                                                │
│     "duplicados": 120                                           │
│   }                                                             │
│ }                                                               │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
                    [Workflow END]
```

---

## Nodes do Workflow

### 1. Webhook Trigger

**Type:** Webhook
**Path:** `/webhook/sped/importacao`
**Method:** POST
**Authentication:** Basic Auth (via env vars)

**Output:** Payload do Laravel com tipo_efd, arquivo_base64, etc.

---

### 2. Code: Validar Tipo SPED

**Type:** Code (JavaScript)
**File:** `code-node-validacao.js`

**Purpose:**
- Detectar tipo real do arquivo
- Comparar com tipo selecionado
- Retornar erro se incompatível

**Output:** `tipo_valido`, `erro`, `error_code`, `error_message`

---

### 3. IF: Tipo Válido?

**Type:** IF
**Condition:** `{{ $json.tipo_valido }}` equals `true`
**Route FALSE:** Enviar erro ao Laravel
**Route TRUE:** Continuar processamento

---

### 4. HTTP: Enviar Erro (Tipo Inválido)

**Type:** HTTP Request
**Method:** POST
**URL:** `{{ $json.progress_url }}`
**Headers:**
- `X-API-Token: {{ $env.API_TOKEN }}`
- `Content-Type: application/json`

**Body:**
```json
{
  "user_id": {{ $json.user_id }},
  "tab_id": "{{ $json.tab_id }}",
  "progresso": 0,
  "status": "erro",
  "error_code": "{{ $json.error_code }}",
  "error_message": "{{ $json.error_message }}",
  "dados": {
    "tipo_esperado": "{{ $json.tipo_esperado }}",
    "tipo_detectado": "{{ $json.tipo_detectado }}"
  }
}
```

**Retry:** 3 attempts, 1s interval
**On Success:** Stop workflow
**On Error:** Log and stop

---

### 5. HTTP: Progresso - Iniciando

**Type:** HTTP Request
**Method:** POST
**URL:** `{{ $json.progress_url }}`

**Body:**
```json
{
  "user_id": {{ $json.user_id }},
  "tab_id": "{{ $json.tab_id }}",
  "progresso": 0,
  "status": "iniciando",
  "mensagem": "Iniciando processamento..."
}
```

---

### 6. Code: Decodificar Base64

**Type:** Code (JavaScript)

```javascript
const items = $input.all();
const data = items[0].json;

const fullContent = Buffer.from(
  data.arquivo_base64,
  'base64'
).toString('utf-8');

const lines = fullContent.split('\n');

return [{
  json: {
    ...data,
    sped_lines: lines,
    total_lines: lines.length
  }
}];
```

---

### 7. Code: Extrair Participantes

**Type:** Code (JavaScript)

**Purpose:** Parse registros |0150| para extrair CNPJs/CPFs de fornecedores e clientes

**Output:** Array de objetos participante

---

### 8. Loop Over Items

**Type:** Loop Over Items
**Input:** Array de participantes
**Batch Size:** 1 (processar um por vez)

---

### 9. Set: Sanitizar Dados

**Type:** Set Node

**Mappings:**
- `cnpj`: `{{ $json.cnpj.replace(/\D/g, '') }}`
- `razao_social`: `{{ $json.razao_social.trim().toUpperCase() }}`
- `origem_tipo`: `{{ $json.tipo_efd === "EFD Fiscal" ? "SPED_EFD_FISCAL" : "SPED_EFD_CONTRIB" }}`

---

### 10. Postgres: UPSERT Participante

**Type:** Postgres
**Operation:** Execute Query

**Query:**
```sql
INSERT INTO participantes (
  user_id, cnpj, razao_social, nome_fantasia, uf, cep,
  municipio, telefone, crt, cliente_id, importacao_sped_id,
  origem_tipo, origem_ref, created_at, updated_at
) VALUES (
  {{ $json.user_id }},
  '{{ $json.cnpj }}',
  '{{ $json.razao_social }}',
  '{{ $json.nome_fantasia }}',
  '{{ $json.uf }}',
  '{{ $json.cep }}',
  '{{ $json.municipio }}',
  '{{ $json.telefone }}',
  {{ $json.crt }},
  {{ $json.cliente_id || 'NULL' }},
  {{ $json.importacao_sped_id }},
  '{{ $json.origem_tipo }}',
  '{{ JSON.stringify($json.origem_ref) }}'::jsonb,
  NOW(),
  NOW()
)
ON CONFLICT (user_id, cnpj) DO UPDATE SET
  razao_social = COALESCE(EXCLUDED.razao_social, participantes.razao_social),
  nome_fantasia = COALESCE(EXCLUDED.nome_fantasia, participantes.nome_fantasia),
  uf = COALESCE(EXCLUDED.uf, participantes.uf),
  cep = COALESCE(EXCLUDED.cep, participantes.cep),
  municipio = COALESCE(EXCLUDED.municipio, participantes.municipio),
  telefone = COALESCE(EXCLUDED.telefone, participantes.telefone),
  crt = COALESCE(EXCLUDED.crt, participantes.crt),
  updated_at = NOW()
RETURNING id, (xmax = 0) AS is_new;
```

---

### 11. Code: Calcular e Enviar Progresso

**Type:** Code (JavaScript)

```javascript
const items = $input.all();
const currentIndex = {{ $node["Loop Over Items"].index }};
const totalItems = {{ $node["Loop Over Items"].total }};

const progresso = Math.floor((currentIndex / totalItems) * 100);

// Enviar progresso a cada 10%
if (progresso % 10 === 0) {
  // HTTP Request node will handle sending
  return [{
    json: {
      ...items[0].json,
      progresso,
      status: 'processando',
      mensagem: `Processando participante ${currentIndex} de ${totalItems}...`
    }
  }];
}

return items; // Pass through
```

---

### 12. Code: Agregar Resultados

**Type:** Code (JavaScript)

```javascript
const items = $input.all();

const total = items.length;
const novos = items.filter(i => i.json.is_new === true).length;
const duplicados = total - novos;
const ids = items.map(i => i.json.id);

return [{
  json: {
    total_participantes: total,
    novos,
    duplicados,
    participante_ids: ids
  }
}];
```

---

### 13. Postgres: UPDATE importacoes_sped

**Type:** Postgres
**Operation:** Execute Query

**Query:**
```sql
UPDATE importacoes_sped SET
  status = 'concluido',
  total_participantes = {{ $json.total_participantes }},
  novos = {{ $json.novos }},
  duplicados = {{ $json.duplicados }},
  participante_ids = '{{ JSON.stringify($json.participante_ids) }}'::jsonb,
  processado_em = NOW()
WHERE id = {{ $json.importacao_id }};
```

---

### 14. HTTP: Progresso Final

**Type:** HTTP Request
**Method:** POST
**URL:** `{{ $json.progress_url }}`

**Body:**
```json
{
  "user_id": {{ $json.user_id }},
  "tab_id": "{{ $json.tab_id }}",
  "progresso": 100,
  "status": "concluido",
  "mensagem": "Processamento concluído!",
  "dados": {
    "total_participantes": {{ $json.total_participantes }},
    "novos": {{ $json.novos }},
    "duplicados": {{ $json.duplicados }}
  }
}
```

---

## Error Handling Global

**Error Trigger:** Adicionar Error Trigger node conectado ao workflow

**On Error:**
1. Capturar erro
2. Enviar para progress_url com status="erro"
3. Incluir error_code e stack trace
4. Estornar créditos se necessário
5. Atualizar importacoes_sped com status="erro"

---

## Variáveis de Ambiente (n8n)

```
API_TOKEN=seu-token-api-laravel
DB_POSTGRESDB_HOST=postgres
DB_POSTGRESDB_PORT=5432
DB_POSTGRESDB_DATABASE=fiscaldock
DB_POSTGRESDB_USER=postgres
DB_POSTGRESDB_PASSWORD=senha-segura
```

---

## Testes de Integração

### Teste 1: Arquivo Correto
- Upload EFD Fiscal válido com tipo_efd="EFD Fiscal"
- Verificar que workflow processa até o fim
- Verificar que participantes são inseridos
- Verificar progresso 0%, 50%, 100%

### Teste 2: Tipo Incorreto
- Upload EFD Contribuições com tipo_efd="EFD Fiscal"
- Verificar que workflow para no IF
- Verificar que erro é enviado ao Laravel
- Verificar que frontend recebe erro via SSE
- Verificar que importacoes_sped fica com status="erro"

### Teste 3: Arquivo Corrompido
- Upload arquivo ZIP/PDF com tipo_efd="EFD Fiscal"
- Verificar que error_code="INVALID_SPED"
- Verificar mensagem amigável no frontend

---

## Performance

**Otimizações:**
- Validação analisa apenas primeiras 50 linhas (< 100ms)
- UPSERT batch processing (100 participantes/lote)
- Progresso enviado a cada 10% (reduz HTTP overhead)
- Cache de resultados intermediários

**Limites:**
- Arquivo máximo: 100MB
- Timeout: 1 hora
- Participantes máximos: 10.000/arquivo

---

## Monitoramento

**Logs importantes:**
- Tipo detectado vs esperado
- Tempo de processamento total
- Quantidade de participantes novos/duplicados
- Erros de validação

**Métricas:**
- Taxa de erro INVALID_SPED (deve ser < 1%)
- Tempo médio de processamento (< 5min para arquivos típicos)
- Taxa de sucesso do retry HTTP (> 99%)

---

**Última Atualização:** 2026-01-31
**Versão do Workflow:** 2.1 (com validação de tipo)
