# Comparação: Abordagem JOIN vs Abordagem Visual

## 🎯 Qual Usar?

### ✅ RECOMENDADO: Abordagem Visual (IFs e Switches)

**Arquivos:**
- `n8n-inicio-rapido.md` (guia prático)
- `n8n-workflow-visual.md` (referência completa)

**Vantagens:**
- ✅ Queries SQL simples e fáceis de entender
- ✅ Cada decisão é um node visual (IF/Switch)
- ✅ Fácil de debugar (vê dados em cada passo)
- ✅ Modular (pode desabilitar planos)
- ✅ Escalável (adicionar novos planos é fácil)

**Desvantagens:**
- ⚠️ Mais nodes (~40 vs ~24)
- ⚠️ Canvas maior

---

### ⚪ Alternativa: Abordagem JOIN

**Arquivos:**
- `n8n-workflow-passo-a-passo.md`
- `n8n-ordem-nodes.md`

**Vantagens:**
- ✅ Menos nodes (~24)
- ✅ Menos queries ao banco

**Desvantagens:**
- ⚠️ Query inicial complexa (4 JOINs)
- ⚠️ Mais difícil de debugar
- ⚠️ Lógica menos visual

---

## 📊 Comparação Lado a Lado

### QUERY INICIAL

#### Abordagem Visual (SIMPLES)
```sql
-- Node 2: Buscar assinaturas
SELECT id, user_id, participante_id, plano_id, frequencia_dias
FROM monitoramento_assinaturas
WHERE status = 'ativo' AND proxima_execucao_em <= NOW();

-- Node 6: Buscar user
SELECT id, name, email, credits
FROM users WHERE id = {{ $json.user_id }};

-- Node 8: Buscar participante
SELECT id, cnpj, uf, razao_social
FROM participantes WHERE id = {{ $json.participante_id }};

-- Node 10: Buscar plano
SELECT id, codigo, nome, custo_creditos, consultas_incluidas
FROM monitoramento_planos WHERE id = {{ $json.plano_id }};
```

**Total:** 4 queries simples (SELECT direto)

---

#### Abordagem JOIN (COMPLEXA)
```sql
-- Node 2: Buscar tudo de uma vez
SELECT
    a.id AS assinatura_id,
    a.user_id,
    a.participante_id,
    a.plano_id,
    a.frequencia_dias,
    p.cnpj,
    p.uf,
    p.razao_social,
    pl.codigo AS plano_codigo,
    pl.consultas_incluidas,
    pl.custo_creditos,
    u.credits AS user_credits,
    u.name AS user_name,
    u.email AS user_email
FROM monitoramento_assinaturas a
JOIN participantes p ON p.id = a.participante_id
JOIN monitoramento_planos pl ON pl.id = a.plano_id
JOIN users u ON u.id = a.user_id
WHERE a.status = 'ativo'
  AND a.proxima_execucao_em <= NOW()
ORDER BY a.proxima_execucao_em ASC
LIMIT 100;
```

**Total:** 1 query complexa (4 JOINs)

---

## 🔀 DECISÕES

### Abordagem Visual (CLARA)

```
Node 12: IF
├─ Condition: user_credits >= custo_creditos
├─ TRUE → Processar
└─ FALSE → Pausar

Node 19: Switch (por plano_codigo)
├─ Output 1: "basico" → Node 20
├─ Output 2: "cadastral" → Node 21-22
├─ Output 3: "fiscal_federal" → Node 23-25
├─ Output 4: "fiscal_completo" → Node 26-30
└─ Output 5: "due_diligence" → Node 31-37
```

**Visual:** Você VÊ a decisão no canvas ✅

---

### Abordagem JOIN (LÓGICA INTERNA)

```
Node 6: IF
├─ Condition: user_credits >= custo_creditos
└─ TRUE/FALSE

Node 13: Split In Batches (loop APIs)
  Node 14: Switch
    └─ Lógica dentro do node
```

**Visual:** Lógica escondida dentro dos nodes ⚠️

---

## 🧪 DEBUG

### Abordagem Visual

```
1. Clica no node "Buscar User"
2. Vê exatamente o que voltou:
   {
     "id": 1,
     "name": "Test",
     "credits": 100
   }
3. Próximo node adiciona mais dados
4. Vê a evolução passo a passo
```

**Debug:** Fácil - cada node mostra seus dados ✅

---

### Abordagem JOIN

```
1. Clica no node "Buscar Assinaturas"
2. Vê objeto grande com tudo misturado:
   {
     "assinatura_id": 2,
     "user_id": 1,
     "user_name": "Test",
     "user_credits": 100,
     "participante_id": 5,
     "cnpj": "...",
     ... (14 campos)
   }
3. Se algo está errado, precisa debugar o JOIN
```

**Debug:** Mais difícil - dados já mesclados ⚠️

---

## 📈 ADICIONAR NOVO PLANO

### Abordagem Visual

```
1. Adicione linha no Switch (node 19)
   Output 6: plano_codigo == "novo_plano"

2. Crie nodes para o novo plano
   Node 41: HTTP - API Nova
   Node 42: Function - Processar Novo

3. Conecte Output 6 → Node 41 → Node 42 → Node 38
```

**Esforço:** 3 passos simples ✅

---

### Abordagem JOIN

```
1. Modifique a query inicial (adicionar campos se necessário)
2. Modifique o Loop de APIs (node 13)
3. Adicione case no Switch (node 14)
4. Adicione novo HTTP node
5. Modifique Function final para incluir nova API
```

**Esforço:** 5 passos, mais acoplado ⚠️

---

## 🎨 APARÊNCIA NO CANVAS

### Abordagem Visual
```
[Cron] → [Query] → [IF] → [Loop]
                             ├→ [Query User]
                             │    └→ [Set]
                             ├→ [Query Part]
                             │    └→ [Set]
                             ├→ [Query Plano]
                             │    └→ [Set Consolidar]
                             │         └→ [IF Créditos]
                             │              ├─FALSE→ [Pausar]
                             │              └─TRUE→ [Descontar]
                             │                       └→ [Criar Consulta]
                             │                            └→ [Minha Receita]
                             │                                 └→ [Switch Plano]
                             │                                      ├→ [Básico]
                             │                                      ├→ [Cadastral+]
                             │                                      ├→ [Fiscal Fed]
                             │                                      ├→ [Fiscal Comp]
                             │                                      └→ [Due Dilig]
                             │                                           └→ [Salvar]
                             └→ [Loop próxima]
```

**Visual:** Fluxo MUITO claro, decisões visíveis ✅

---

### Abordagem JOIN
```
[Cron] → [Query] → [IF] → [Loop]
                             └→ [Set]
                                 └→ [IF Créditos]
                                      ├─FALSE→ [Pausar]
                                      └─TRUE→ [Descontar]
                                               └→ [Criar Consulta]
                                                    └→ [Set]
                                                         └→ [Minha Receita]
                                                              └→ [Parse]
                                                                   └→ [IF APIs Pagas]
                                                                        ├─FALSE→ [Analisar]
                                                                        └─TRUE→ [Loop APIs]
                                                                                 └→ [Switch]
                                                                                      └→ [Merge]
                                                                                           └→ [Analisar]
                                                                                                └→ [Salvar]
```

**Visual:** Mais compacto, mas decisões menos óbvias ⚠️

---

## 🏆 RECOMENDAÇÃO FINAL

### Use Abordagem Visual SE:
- ✅ Você quer entender TUDO que está acontecendo
- ✅ Você vai modificar/adicionar planos com frequência
- ✅ Você quer debugar facilmente
- ✅ Você prefere queries SQL simples
- ✅ Canvas grande não é problema

### Use Abordagem JOIN SE:
- ✅ Você quer menos nodes
- ✅ Você é expert em SQL JOINs
- ✅ Você não vai modificar muito o workflow
- ✅ Canvas pequeno é importante

---

## 🎯 Para Este Projeto: VISUAL

**Motivo:** Você disse que prefere queries simples + IFs/Switches para visualizar a lógica.

**Arquivos para usar:**
1. `docs/n8n-inicio-rapido.md` - Comece aqui
2. `docs/n8n-workflow-visual.md` - Referência completa

**Ignore:**
- `n8n-workflow-passo-a-passo.md` (abordagem JOIN)
- `n8n-ordem-nodes.md` (abordagem JOIN)

---

## 📚 Próximos Passos

1. ✅ Abra `docs/n8n-inicio-rapido.md`
2. ✅ Crie nodes 1-11 (ETAPA 1)
3. ✅ Teste cada checkpoint
4. ✅ Continue até o fim
5. ✅ Adicione outros planos conforme necessário

Boa implementação! 🚀

---

# Sistema de Progresso SPED (n8n → Laravel)

## 📖 Visão Geral

O sistema de progresso permite que o n8n envie atualizações em tempo real para o Laravel, que repassa para o frontend via SSE (Server-Sent Events).

**Princípio:** O n8n controla 100% do progresso (percentual, mensagem, status). Laravel apenas armazena em cache e repassa via SSE.

---

## 🔀 Fluxo Completo

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Frontend   │     │   Laravel   │     │    n8n      │
└─────────────┘     └─────────────┘     └─────────────┘
      │                    │                    │
      │ POST /sped/upload  │                    │
      │ {file, tab_id} --->│                    │
      │                    │ POST webhook ----->│
      │                    │ {user_id, tab_id,  │
      │                    │  progress_url}     │
      │                    │                    │
      │ SSE /stream?tab_id │                    │
      │ ------------------>│                    │
      │                    │                    │
      │                    │<-- POST /progress -│ (cada etapa)
      │                    │ {user_id, tab_id,  │
      │                    │  progresso, status}│
      │                    │                    │
      │<-- SSE data -------|                    │
      │                    │                    │
      │                    │<-- POST /progress -│ (concluído)
      │                    │ {status:"concluido"}
      │<-- SSE data -------|                    │
      │                    │                    │
      └─── FIM ───────────-┴────────────────────┘
```

---

## 📡 API de Progresso

### Endpoint

```
POST /api/monitoramento/sped/importacao-txt/progress
Header: X-API-Token: {API_TOKEN}
```

### Payload (n8n → Laravel)

```json
{
    "user_id": 1,
    "tab_id": "550e8400-e29b-41d4-a716-446655440000",
    "progresso": 45,
    "mensagem": "Processando CNPJ 45 de 150...",
    "status": "processando"
}
```

### Campos

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| `user_id` | int | ✅ | ID do usuário (recebido do Laravel no webhook inicial) |
| `tab_id` | string (max 36) | ✅ | UUID da aba do navegador (isola múltiplas abas) |
| `progresso` | int (0-100) | ✅ | Percentual de progresso |
| `mensagem` | string (max 255) | ❌ | Texto para exibir na UI |
| `status` | enum | ✅ | `iniciando`, `processando`, `concluido`, `erro` |
| `error_code` | string (max 50) | ❌ | Código do erro (quando status=erro) |
| `error_message` | string (max 500) | ❌ | Descrição do erro |

### Chave do Cache

O Laravel armazena o progresso em cache com a chave:

```
progresso:{user_id}:{tab_id}
```

**TTL:** 10 minutos

---

## ⚠️ Tratamento de Erros

Quando ocorre um erro durante o processamento (ex: API InfoSimples timeout), o n8n deve enviar:

```json
{
    "user_id": 1,
    "tab_id": "550e8400-e29b-41d4-a716-446655440000",
    "progresso": 65,
    "mensagem": "Erro ao consultar API InfoSimples",
    "status": "erro",
    "error_code": "INFOSIMPLES_TIMEOUT",
    "error_message": "API não respondeu após 30 segundos. Verifique a conexão e tente novamente."
}
```

### Códigos de Erro Padronizados

| Código | Descrição | Quando Usar |
|--------|-----------|-------------|
| `INFOSIMPLES_TIMEOUT` | Timeout na API InfoSimples | API não respondeu em 30s |
| `INFOSIMPLES_ERROR` | Erro retornado pela API | HTTP 4xx/5xx da InfoSimples |
| `INFOSIMPLES_INVALID_RESPONSE` | Resposta inválida | JSON malformado ou campos faltando |
| `MINHA_RECEITA_TIMEOUT` | Timeout na Minha Receita | API gratuita não respondeu |
| `MINHA_RECEITA_ERROR` | Erro na Minha Receita | HTTP 4xx/5xx |
| `INVALID_SPED` | Arquivo SPED inválido | Formato não reconhecido |
| `PARSE_ERROR` | Erro ao extrair dados | Falha no parsing do SPED |
| `NO_PARTICIPANTS` | Nenhum participante | SPED sem CNPJs válidos |
| `DB_ERROR` | Erro de banco | Falha ao salvar no PostgreSQL |
| `CREDIT_ERROR` | Erro de créditos | Falha ao verificar/descontar |
| `UNKNOWN_ERROR` | Erro desconhecido | Fallback para erros não mapeados |

### Exemplo no n8n (Function Node)

```javascript
// Ao detectar erro em qualquer etapa
if (apiResponse.error || apiResponse.status >= 400) {
    // Enviar erro para Laravel
    const progressUrl = $input.item.json.progress_url;

    await $http.request({
        method: 'POST',
        url: progressUrl,
        headers: {
            'Content-Type': 'application/json',
            'X-API-Token': $env.API_TOKEN
        },
        body: {
            user_id: $input.item.json.user_id,
            tab_id: $input.item.json.tab_id,
            progresso: currentProgress,
            mensagem: 'Erro ao consultar InfoSimples',
            status: 'erro',
            error_code: 'INFOSIMPLES_ERROR',
            error_message: apiResponse.message || 'Erro desconhecido'
        }
    });

    // Parar execução
    return [];
}
```

---

## 📊 Exemplos de Progresso por Fase

### Fluxo de Identificação de Participantes

```javascript
// 1. Iniciando (0%)
{
    progresso: 0,
    mensagem: "Iniciando processamento...",
    status: "iniciando"
}

// 2. Lendo arquivo (10%)
{
    progresso: 10,
    mensagem: "Lendo arquivo SPED...",
    status: "processando"
}

// 3. Validando formato (20%)
{
    progresso: 20,
    mensagem: "Validando formato do arquivo...",
    status: "processando"
}

// 4. Extraindo participantes (30%)
{
    progresso: 30,
    mensagem: "Extraindo participantes do bloco 0150...",
    status: "processando"
}

// 5. Consultando CNPJs (30-80% - proporcional)
// Para 150 CNPJs: cada CNPJ = ~0.33%
{
    progresso: 45,
    mensagem: "Consultando CNPJ 45 de 150...",
    status: "processando"
}

// 6. Salvando no banco (80-95%)
{
    progresso: 85,
    mensagem: "Salvando participantes no banco...",
    status: "processando"
}

// 7. Finalizando (95-100%)
{
    progresso: 95,
    mensagem: "Gerando relatório...",
    status: "processando"
}

// 8. Concluído (100%)
{
    progresso: 100,
    mensagem: "150 participantes identificados com sucesso!",
    status: "concluido"
}
```

### Fluxo com Erro

```javascript
// Processando normalmente...
{ progresso: 50, mensagem: "Consultando CNPJ 75 de 150...", status: "processando" }

// Erro detectado
{
    progresso: 50,
    mensagem: "Erro: API InfoSimples indisponível",
    status: "erro",
    error_code: "INFOSIMPLES_TIMEOUT",
    error_message: "A consulta ao CNPJ 12.345.678/0001-90 falhou. A API não respondeu após 30 segundos."
}
```

---

## 🔧 Frequência de Envio

| Tamanho do Arquivo | Frequência Recomendada |
|--------------------|------------------------|
| < 50 CNPJs | A cada 5 CNPJs processados |
| 50-500 CNPJs | A cada 10-20 CNPJs |
| > 500 CNPJs | A cada 50 CNPJs ou 2-3 segundos |

**Regra:** Máximo 1 update por segundo para evitar sobrecarga no Laravel/SSE.

---

## 🖥️ Frontend - Conectando ao SSE

```javascript
// 1. Gerar tab_id único (persiste na aba)
const tabId = sessionStorage.getItem('sped_tab_id') ||
              (sessionStorage.setItem('sped_tab_id', crypto.randomUUID()), sessionStorage.getItem('sped_tab_id'));

// 2. Enviar arquivo com tab_id
const formData = new FormData();
formData.append('file', fileInput.files[0]);
formData.append('tab_id', tabId);

fetch('/app/sped/upload', {
    method: 'POST',
    body: formData,
    headers: { 'X-CSRF-TOKEN': csrfToken }
});

// 3. Conectar ao SSE imediatamente
const eventSource = new EventSource(`/app/monitoramento/progresso/stream?tab_id=${tabId}`);

eventSource.onmessage = (event) => {
    const data = JSON.parse(event.data);

    // Atualizar UI
    progressBar.style.width = data.progresso + '%';
    progressText.textContent = data.mensagem || `${data.progresso}%`;

    // Status final
    if (data.status === 'concluido') {
        eventSource.close();
        showSuccessToast(data.mensagem);
        redirectToResults();
    } else if (data.status === 'erro') {
        eventSource.close();
        showErrorToast(data.error_message || data.mensagem);
        enableRetryButton();
    }
};

eventSource.onerror = () => {
    eventSource.close();
    showErrorToast('Conexão perdida. Recarregue a página.');
};
```

---

## 🧪 Testando o Sistema

### 1. Teste Manual via cURL

```bash
# Enviar progresso de teste
curl -X POST http://localhost:8080/api/monitoramento/sped/importacao-txt/progress \
  -H "Content-Type: application/json" \
  -H "X-API-Token: SEU_TOKEN" \
  -d '{
    "user_id": 1,
    "tab_id": "test-123",
    "progresso": 50,
    "mensagem": "Teste de progresso...",
    "status": "processando"
  }'

# Verificar cache (dentro do container)
docker compose exec app php artisan tinker --execute="dump(Cache::get('progresso:1:test-123'));"
```

### 2. Teste SSE via Browser

```
http://localhost:8080/app/monitoramento/progresso/stream?tab_id=test-123
```

### 3. Teste de Erro

```bash
curl -X POST http://localhost:8080/api/monitoramento/sped/importacao-txt/progress \
  -H "Content-Type: application/json" \
  -H "X-API-Token: SEU_TOKEN" \
  -d '{
    "user_id": 1,
    "tab_id": "test-123",
    "progresso": 65,
    "mensagem": "Erro na API",
    "status": "erro",
    "error_code": "INFOSIMPLES_TIMEOUT",
    "error_message": "API não respondeu"
  }'
```

---

## 📁 Arquivos Relacionados

| Arquivo | Função |
|---------|--------|
| `app/Http/Controllers/SpedUploadController.php` | Envia arquivo + progress_url + tab_id para n8n |
| `app/Http/Controllers/Api/DataReceiverController.php` | Método `receiveImportacaoTxtProgress()` - recebe progresso |
| `app/Http/Controllers/Dashboard/MonitoramentoController.php` | Método `streamProgresso()` - SSE para frontend |
| `routes/web.php` | Rota `GET /app/monitoramento/progresso/stream` |
| `routes/api.php` | Rota `POST /api/monitoramento/sped/importacao-txt/progress` |

---

## ✅ Checklist para Implementar no n8n

- [ ] Receber `user_id`, `tab_id`, `progress_url` do Laravel no webhook inicial
- [ ] Armazenar esses valores para usar em todo o workflow
- [ ] Enviar progresso a cada etapa significativa
- [ ] Incluir `error_code` e `error_message` quando status=erro
- [ ] Enviar status="concluido" com progresso=100 ao finalizar
- [ ] Respeitar limite de 1 update/segundo
- [ ] Tratar timeouts de APIs externas graciosamente

---

# Tabela Participantes - Rastreabilidade de Origem

## 📖 Visão Geral

Ao criar registros na tabela `participantes`, o n8n deve preencher os campos `origem_tipo` e `origem_ref` para rastrear de onde veio cada participante.

---

## 📋 Campo `origem_tipo` (obrigatório)

String que indica a fonte do participante.

| Valor | Quando usar |
|-------|-------------|
| `SPED_EFD_FISCAL` | Importado de arquivo SPED EFD Fiscal |
| `SPED_EFD_CONTRIB` | Importado de arquivo SPED EFD Contribuições |
| `NFE` | Extraído de NF-e |
| `NFSE` | Extraído de NFS-e |
| `MANUAL` | Cadastro manual pelo usuário |

---

## 📋 Campo `origem_ref` (opcional, JSON)

Objeto JSON com metadados para rastreabilidade. Estrutura varia conforme a origem.

### Importação via Monitoramento SPED

Quando o usuário importa direto em `/app/monitoramento/sped`:

```json
{
  "arquivo": "SPED_EFD_2024.txt",
  "importado_em": "2026-01-18T10:30:00Z"
}
```

O Laravel já envia o nome do arquivo no payload do webhook:

```json
{
  "user_id": 1,
  "tab_id": "uuid-xxx",
  "filename": "SPED_EFD_2024.txt",
  "tipo_efd": "fiscal",
  "cliente_id": 123,
  "progress_url": "https://.../api/monitoramento/sped/importacao-txt/progress"
}
```

### Importação via RAF (relatório processado)

Quando vem de um relatório RAF:

```json
{
  "raf_relatorio_id": 123
}
```

### Cadastro Manual

Pode ser `null` ou vazio:

```json
null
```

---

## 🔧 Exemplo SQL para INSERT (n8n)

### Via Monitoramento SPED

```sql
INSERT INTO participantes (
    user_id,
    cliente_id,
    cnpj,
    razao_social,
    uf,
    origem_tipo,
    origem_ref,
    created_at,
    updated_at
) VALUES (
    {{ $json.user_id }},
    {{ $json.cliente_id || 'NULL' }},
    '{{ $json.cnpj }}',
    '{{ $json.razao_social }}',
    '{{ $json.uf }}',
    'SPED_EFD_FISCAL',
    '{"arquivo": "{{ $json.filename }}", "importado_em": "{{ $now.toISO() }}"}'::jsonb,
    NOW(),
    NOW()
)
ON CONFLICT (user_id, cnpj) DO UPDATE SET
    razao_social = EXCLUDED.razao_social,
    uf = EXCLUDED.uf,
    updated_at = NOW();
```

### Via RAF

```sql
INSERT INTO participantes (
    user_id,
    cnpj,
    razao_social,
    origem_tipo,
    origem_ref,
    created_at,
    updated_at
) VALUES (
    {{ $json.user_id }},
    '{{ $json.cnpj }}',
    '{{ $json.razao_social }}',
    'SPED_EFD_FISCAL',
    '{"raf_relatorio_id": {{ $json.raf_relatorio_id }}}'::jsonb,
    NOW(),
    NOW()
)
ON CONFLICT (user_id, cnpj) DO NOTHING;
```

---

## 🔧 Exemplo Function Node (n8n)

```javascript
// Dados recebidos do webhook
const userId = $input.item.json.user_id;
const filename = $input.item.json.filename;
const tipoEfd = $input.item.json.tipo_efd; // 'fiscal' ou 'contribuicoes'

// Determinar origem_tipo
const origemTipo = tipoEfd === 'fiscal'
    ? 'SPED_EFD_FISCAL'
    : 'SPED_EFD_CONTRIB';

// Montar origem_ref
const origemRef = {
    arquivo: filename,
    importado_em: new Date().toISOString()
};

// Para cada participante extraído do SPED
return items.map(item => ({
    json: {
        ...item.json,
        user_id: userId,
        origem_tipo: origemTipo,
        origem_ref: JSON.stringify(origemRef)
    }
}));
```

---

## ✅ Checklist para Participantes

- [ ] Sempre preencher `origem_tipo` com valor válido da tabela
- [ ] Incluir `origem_ref` com nome do arquivo quando vem de importação SPED
- [ ] Usar `ON CONFLICT` para evitar duplicatas (CNPJ único por user)
- [ ] Manter `cliente_id` se o usuário selecionou um cliente na UI
