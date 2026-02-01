# Workflow: Consulta Manual

Especificação completa do workflow n8n para consultas manuais de participantes.

---

## Webhook

**URL:** `https://autowebhook.fiscaldock.com.br/webhook/consultas`

**Método:** POST

**Headers:**
```
Content-Type: application/json
X-API-Token: {API_TOKEN}
```

---

## Payload de Entrada

```json
{
  "user_id": 1,
  "consulta_lote_id": 123,
  "tab_id": "550e8400-e29b-41d4-a716-446655440000",
  "plano_codigo": "licitacao",
  "consultas_incluidas": [
    "situacao_cadastral",
    "dados_cadastrais",
    "endereco",
    "cnaes",
    "qsa",
    "simples_nacional",
    "mei",
    "sintegra",
    "tcu_consolidada",
    "cnd_federal",
    "crf_fgts",
    "cnd_estadual",
    "cndt"
  ],
  "participantes": [
    {
      "id": 1,
      "cnpj": "12345678000190",
      "razao_social": "EMPRESA EXEMPLO LTDA",
      "uf": "SP",
      "crt": 1
    },
    {
      "id": 2,
      "cnpj": "98765432000110",
      "razao_social": "OUTRA EMPRESA LTDA",
      "uf": "RJ",
      "crt": 3
    }
  ],
  "progress_url": "https://fiscaldock.com.br/api/consultas/lote/progress",
  "resultado_url": "https://fiscaldock.com.br/api/consultas/lote/resultado"
}
```

---

## Diagrama do Workflow

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              WEBHOOK                                         │
│                     POST /webhook/consultas                                  │
└────────────────────────────────┬────────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ Code Node: Validar Payload                                                   │
│                                                                              │
│ - Verificar campos obrigatórios                                              │
│ - Extrair variáveis para uso posterior                                       │
└────────────────────────────────┬────────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ HTTP Request: Enviar Progresso Inicial                                       │
│                                                                              │
│ POST progress_url                                                            │
│ { "status": "iniciando", "progresso": 0, "mensagem": "Iniciando..." }        │
└────────────────────────────────┬────────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ Split In Batches: Processar Participantes                                    │
│                                                                              │
│ Batch Size: 1 (processar um por vez)                                         │
└────────────────────────────────┬────────────────────────────────────────────┘
                                 │
              ┌──────────────────┴──────────────────┐
              │                                     │
              ▼                                     │
┌─────────────────────────────────────┐             │
│ Para cada participante:             │             │
│                                     │             │
│ 1. HTTP: Minha Receita              │             │
│ 2. IF: Tem consultas pagas?         │             │
│    ├─► Sim: HTTP(s) InfoSimples     │             │
│    └─► Não: Pular                   │             │
│ 3. Code: Consolidar Resultados      │             │
│ 4. HTTP: POST resultado_url         │             │
│ 5. HTTP: POST progress_url          │             │
└────────────────────────────────────┬┘             │
                                     │              │
                                     └──────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ HTTP Request: Enviar Progresso Final                                         │
│                                                                              │
│ POST progress_url                                                            │
│ { "status": "concluido", "progresso": 100, "mensagem": "Concluído!" }        │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Nodes Detalhados

### 1. Webhook

**Tipo:** Webhook

**Configuração:**
- HTTP Method: POST
- Path: `/consultas`
- Authentication: Header Auth
  - Header Name: `X-API-Token`
  - Header Value: `{{$env.FISCALDOCK_API_TOKEN}}`

---

### 2. Code Node: Validar Payload

```javascript
const input = $input.first().json;

// Validar campos obrigatórios
const required = ['user_id', 'consulta_lote_id', 'tab_id', 'plano_codigo',
                  'consultas_incluidas', 'participantes', 'progress_url', 'resultado_url'];

for (const field of required) {
    if (!input[field]) {
        throw new Error(`Campo obrigatório ausente: ${field}`);
    }
}

// Validar participantes
if (!Array.isArray(input.participantes) || input.participantes.length === 0) {
    throw new Error('Array de participantes vazio ou inválido');
}

// Extrair variáveis
return [{
    json: {
        user_id: input.user_id,
        consulta_lote_id: input.consulta_lote_id,
        tab_id: input.tab_id,
        plano_codigo: input.plano_codigo,
        consultas_incluidas: input.consultas_incluidas,
        participantes: input.participantes,
        progress_url: input.progress_url,
        resultado_url: input.resultado_url,
        total_participantes: input.participantes.length,
        processados: 0
    }
}];
```

---

### 3. HTTP Request: Progresso Inicial

**Tipo:** HTTP Request

**Configuração:**
- Method: POST
- URL: `{{ $json.progress_url }}`
- Authentication: Header Auth
  - Header Name: `X-API-Token`
  - Header Value: `{{$env.FISCALDOCK_API_TOKEN}}`
- Body (JSON):

```json
{
    "user_id": {{ $json.user_id }},
    "tab_id": "{{ $json.tab_id }}",
    "consulta_lote_id": {{ $json.consulta_lote_id }},
    "progresso": 0,
    "status": "processando",
    "mensagem": "Iniciando consultas..."
}
```

---

### 4. Split In Batches

**Tipo:** Split In Batches

**Configuração:**
- Batch Size: 1
- Reset: No

---

### 5. HTTP Request: Minha Receita

**Tipo:** HTTP Request

**Configuração:**
- Method: GET
- URL: `https://minhareceita.org/{{ $json.cnpj }}`
- Headers:
  - Accept: `application/json`

**Resposta esperada:**
```json
{
    "cnpj": "12345678000190",
    "razao_social": "EMPRESA EXEMPLO LTDA",
    "nome_fantasia": "EXEMPLO",
    "situacao_cadastral": "ATIVA",
    "data_situacao_cadastral": "2020-01-15",
    "motivo_situacao_cadastral": null,
    "natureza_juridica": "2062",
    "porte": "PEQUENO",
    "capital_social": 100000,
    "endereco": {
        "logradouro": "RUA EXEMPLO",
        "numero": "123",
        "complemento": "SALA 1",
        "bairro": "CENTRO",
        "municipio": "SAO PAULO",
        "uf": "SP",
        "cep": "01000000"
    },
    "email": "contato@exemplo.com.br",
    "telefone": "11999999999",
    "cnae_principal": {
        "codigo": "6201500",
        "descricao": "DESENVOLVIMENTO DE PROGRAMAS DE COMPUTADOR SOB ENCOMENDA"
    },
    "cnaes_secundarios": [...],
    "qsa": [...],
    "simples_nacional": {
        "optante": true,
        "data_opcao": "2018-01-01",
        "data_exclusao": null
    },
    "mei": false
}
```

---

### 6. IF Node: Tem Consultas Pagas?

**Tipo:** IF

**Condição:**
```javascript
// Verifica se plano inclui consultas pagas
const consultasPagas = ['sintegra', 'tcu_consolidada', 'cnd_federal', 'crf_fgts',
                        'cnd_estadual', 'cndt', 'protestos', 'lista_devedores_pgfn',
                        'trabalho_escravo', 'ibama_autuacoes', 'processos_cnj'];

const temPagas = $json.consultas_incluidas.some(c => consultasPagas.includes(c));
return temPagas;
```

---

### 7. HTTP Requests: InfoSimples (Consultas Pagas)

Para cada consulta paga incluída no plano, fazer HTTP Request correspondente.

**Exemplo: SINTEGRA**

```javascript
// URL
https://api.infosimples.com/api/v2/consultas/sintegra/unificada

// Body
{
    "token": "{{ $env.INFOSIMPLES_TOKEN }}",
    "cnpj": "{{ $json.cnpj }}",
    "timeout": 300
}
```

**Ver:** [apis-consulta.md](./apis-consulta.md) para referência completa das APIs.

---

### 8. Code Node: Consolidar Resultados

```javascript
const participante = $('Split In Batches').item.json;
const minhaReceita = $('HTTP Minha Receita').item.json;

// Buscar resultados das consultas pagas (se existirem)
let sintegra = null, tcuConsolidada = null, cndFederal = null;
// ... buscar de cada node

const resultado = {
    // Dados básicos
    situacao_cadastral: minhaReceita.situacao_cadastral,
    razao_social: minhaReceita.razao_social,
    nome_fantasia: minhaReceita.nome_fantasia,

    // Endereço
    endereco: minhaReceita.endereco,

    // CNAEs
    cnaes: {
        principal: minhaReceita.cnae_principal,
        secundarios: minhaReceita.cnaes_secundarios
    },

    // QSA
    qsa: minhaReceita.qsa,

    // Simples/MEI
    simples_nacional: minhaReceita.simples_nacional?.optante || false,
    mei: minhaReceita.mei || false,

    // Consultas pagas
    sintegra: sintegra,
    tcu_consolidada: tcuConsolidada,
    cnd_federal: cndFederal,
    // ... outras

    // Metadados
    consultas_realizadas: participante.consultas_incluidas,
    consultado_em: new Date().toISOString()
};

return [{
    json: {
        ...participante,
        resultado_dados: resultado,
        status: 'sucesso'
    }
}];
```

---

### 9. HTTP Request: Enviar Resultado Individual

**Tipo:** HTTP Request

**Configuração:**
- Method: POST
- URL: `{{ $('Validar Payload').item.json.resultado_url }}`
- Body (JSON):

```json
{
    "consulta_lote_id": {{ $('Validar Payload').item.json.consulta_lote_id }},
    "user_id": {{ $('Validar Payload').item.json.user_id }},
    "tab_id": "{{ $('Validar Payload').item.json.tab_id }}",
    "participante_id": {{ $json.id }},
    "status": "{{ $json.status }}",
    "resultado_dados": {{ JSON.stringify($json.resultado_dados) }}
}
```

---

### 10. HTTP Request: Atualizar Progresso

**Tipo:** HTTP Request

**Configuração:**
- Method: POST
- URL: `{{ $('Validar Payload').item.json.progress_url }}`
- Body (JSON):

```javascript
// Calcular progresso
const total = $('Validar Payload').item.json.total_participantes;
const atual = $('Split In Batches').context.currentItemIndex + 1;
const progresso = Math.round((atual / total) * 100);

return {
    "user_id": $('Validar Payload').item.json.user_id,
    "tab_id": $('Validar Payload').item.json.tab_id,
    "consulta_lote_id": $('Validar Payload').item.json.consulta_lote_id,
    "progresso": progresso,
    "status": "processando",
    "mensagem": `Processando ${atual}/${total} participantes...`
};
```

---

### 11. HTTP Request: Progresso Final

**Tipo:** HTTP Request

**Configuração:**
- Method: POST
- URL: `{{ $json.progress_url }}`
- Body (JSON):

```json
{
    "user_id": {{ $json.user_id }},
    "tab_id": "{{ $json.tab_id }}",
    "consulta_lote_id": {{ $json.consulta_lote_id }},
    "progresso": 100,
    "status": "concluido",
    "mensagem": "Processamento concluído!",
    "resultado_resumo": {
        "total": {{ $json.total_participantes }},
        "sucesso": {{ $json.total_participantes }},
        "erro": 0
    }
}
```

---

## Error Handling

### Erro na Consulta Individual

Se uma consulta falhar para um participante específico:

```javascript
// No Code Node de consolidação
try {
    // ... processar
} catch (error) {
    return [{
        json: {
            ...participante,
            status: 'erro',
            error_message: error.message,
            resultado_dados: null
        }
    }];
}
```

### Erro Global no Workflow

Adicionar node de Error Trigger para capturar erros globais e notificar Laravel:

```javascript
// Error Trigger → HTTP Request
{
    "user_id": {{ $json.user_id }},
    "tab_id": "{{ $json.tab_id }}",
    "consulta_lote_id": {{ $json.consulta_lote_id }},
    "progresso": 0,
    "status": "erro",
    "mensagem": "Erro no processamento",
    "error_code": "WORKFLOW_ERROR",
    "error_message": "{{ $json.error.message }}"
}
```

---

## Testes

### Teste 1: Plano Gratuito

```bash
curl -X POST https://autowebhook.fiscaldock.com.br/webhook/consultas \
  -H "Content-Type: application/json" \
  -H "X-API-Token: seu-token" \
  -d '{
    "user_id": 1,
    "consulta_lote_id": 999,
    "tab_id": "test-123",
    "plano_codigo": "gratuito",
    "consultas_incluidas": ["situacao_cadastral", "dados_cadastrais"],
    "participantes": [{"id": 1, "cnpj": "00000000000191", "razao_social": "TESTE", "uf": "SP", "crt": 1}],
    "progress_url": "https://fiscaldock.com.br/api/consultas/lote/progress",
    "resultado_url": "https://fiscaldock.com.br/api/consultas/lote/resultado"
  }'
```

### Teste 2: Plano Licitação

```bash
# Mesmo payload, mas com:
"plano_codigo": "licitacao",
"consultas_incluidas": ["situacao_cadastral", "sintegra", "cnd_federal", "crf_fgts", "cnd_estadual", "cndt"]
```

---

**Última atualização:** 2026-01-31
