# Workflow: Consulta Automática

Especificação completa do workflow n8n para consultas automáticas de participantes via cron.

---

## Visão Geral

- **Trigger:** Schedule (cron) - A cada hora
- **Fonte de dados:** PostgreSQL direto (sem webhook)
- **Processamento:** n8n consulta APIs e grava resultados
- **Créditos:** Debitados atomicamente durante execução

---

## Diagrama do Workflow

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         SCHEDULE TRIGGER                                     │
│                      Cron: 0 * * * * (cada hora)                             │
└────────────────────────────────┬────────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ PostgreSQL: SELECT assinaturas pendentes                                     │
│                                                                              │
│ WHERE status = 'ativo' AND proxima_execucao_em <= NOW()                      │
│ LIMIT 100                                                                    │
└────────────────────────────────┬────────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────┐
│ IF: Tem assinaturas?            │────No───► Exit (sem trabalho)
└────────────────────────────────┬┘
                                 │ Yes
                                 ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ Split In Batches: Processar 1 por vez                                        │
└────────────────────────────────┬────────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ Code Node: Verificar créditos                                                │
│                                                                              │
│ if (user_credits < custo_creditos) → skip = true                             │
└────────────────────────────────┬────────────────────────────────────────────┘
                                 │
                                 ▼
┌─────────────────────────────────┐
│ IF: Créditos insuficientes?     │
└────────────────────────────────┬┘
         │                       │
         │ Yes                   │ No
         ▼                       ▼
┌──────────────────┐   ┌──────────────────────────────────────────────────────┐
│ PostgreSQL:      │   │ PostgreSQL: Debitar créditos (atômico)               │
│ Pausar assinatura│   │                                                      │
│ + INSERT erro    │   │ UPDATE users SET credits = credits - X               │
└────────┬─────────┘   │ WHERE id = Y AND credits >= X                        │
         │             └────────────────────────────┬─────────────────────────┘
         │                                          │
         └──────────────────────────────────────────┼──────► (próximo item)
                                                    │
                                                    ▼
                          ┌─────────────────────────────────────────────────────┐
                          │ HTTP Request: Minha Receita                          │
                          │ GET https://minhareceita.org/{cnpj}                  │
                          └────────────────────────────┬────────────────────────┘
                                                       │
                                                       ▼
                          ┌─────────────────────────────────────────────────────┐
                          │ IF: Plano tem consultas pagas?                       │
                          └────────────────────────────┬────────────────────────┘
                                  │                    │
                                  │ Yes                │ No
                                  ▼                    │
                          ┌───────────────────┐        │
                          │ HTTP Requests:    │        │
                          │ InfoSimples APIs  │        │
                          └─────────┬─────────┘        │
                                    │                  │
                                    └──────────────────┤
                                                       │
                                                       ▼
                          ┌─────────────────────────────────────────────────────┐
                          │ Code Node: Analisar resultados e gerar alertas       │
                          │                                                      │
                          │ - Classificar situacao_geral                         │
                          │ - Identificar pendências                             │
                          │ - Gerar array de alertas                             │
                          └────────────────────────────┬────────────────────────┘
                                                       │
                                                       ▼
                          ┌─────────────────────────────────────────────────────┐
                          │ PostgreSQL: INSERT monitoramento_consultas           │
                          └────────────────────────────┬────────────────────────┘
                                                       │
                                                       ▼
                          ┌─────────────────────────────────────────────────────┐
                          │ PostgreSQL: UPDATE proxima_execucao_em               │
                          └────────────────────────────┬────────────────────────┘
                                                       │
                                                       ▼
                          ┌─────────────────────────────────────────────────────┐
                          │ IF: Tem alertas críticos?                            │
                          └────────────────────────────┬────────────────────────┘
                                  │                    │
                                  │ Yes                │ No
                                  ▼                    │
                          ┌───────────────────┐        │
                          │ HTTP Request:     │        │
                          │ POST /api/alertas │        │
                          │ (opcional)        │        │
                          └─────────┬─────────┘        │
                                    │                  │
                                    └──────────────────┴──────► (próximo item)
```

---

## Nodes Detalhados

### 1. Schedule Trigger

**Tipo:** Schedule Trigger

**Configuração:**
- Mode: Cron
- Cron Expression: `0 * * * *` (minuto 0 de cada hora)

**Alternativas:**
- `*/15 * * * *` - A cada 15 minutos
- `0 */2 * * *` - A cada 2 horas
- `0 8 * * *` - Todo dia às 8h

---

### 2. PostgreSQL: Buscar Assinaturas Pendentes

**Tipo:** PostgreSQL

**Operation:** Execute Query

**Query:**
```sql
SELECT
    ma.id as assinatura_id,
    ma.user_id,
    ma.participante_id,
    ma.plano_id,
    ma.frequencia_dias,
    p.cnpj,
    p.razao_social,
    p.uf,
    p.crt,
    mp.codigo as plano_codigo,
    mp.consultas_incluidas,
    mp.custo_creditos,
    u.credits as user_credits,
    u.name as user_name,
    u.email as user_email
FROM monitoramento_assinaturas ma
JOIN participantes p ON p.id = ma.participante_id
JOIN monitoramento_planos mp ON mp.id = ma.plano_id
JOIN users u ON u.id = ma.user_id
WHERE ma.status = 'ativo'
  AND ma.proxima_execucao_em <= NOW()
ORDER BY ma.proxima_execucao_em ASC
LIMIT 100;
```

**Notas:**
- LIMIT 100 para evitar sobrecarga
- ORDER BY para processar os mais atrasados primeiro
- JOIN completo para ter todos os dados necessários

---

### 3. IF Node: Tem Assinaturas?

**Tipo:** IF

**Condição:**
```javascript
{{ $json.length > 0 }}
```

---

### 4. Split In Batches

**Tipo:** Split In Batches

**Configuração:**
- Batch Size: 1
- Reset: No

---

### 5. Code Node: Verificar Créditos

```javascript
const item = $input.first().json;

const custoCreditos = item.custo_creditos || 0;
const creditosUsuario = item.user_credits || 0;

if (custoCreditos > creditosUsuario) {
    return [{
        json: {
            ...item,
            skip: true,
            skip_reason: 'INSUFFICIENT_CREDITS',
            skip_message: `Créditos insuficientes: necessário ${custoCreditos}, disponível ${creditosUsuario}`
        }
    }];
}

return [{
    json: {
        ...item,
        skip: false
    }
}];
```

---

### 6. IF Node: Créditos Insuficientes?

**Tipo:** IF

**Condição:**
```javascript
{{ $json.skip === true }}
```

---

### 7. PostgreSQL: Pausar Assinatura (branch: sem créditos)

**Tipo:** PostgreSQL

**Operation:** Execute Query

**Query 1: Pausar assinatura**
```sql
UPDATE monitoramento_assinaturas
SET
    status = 'pausado',
    updated_at = NOW()
WHERE id = {{ $json.assinatura_id }};
```

**Query 2: Registrar erro**
```sql
INSERT INTO monitoramento_consultas (
    user_id,
    participante_id,
    plano_id,
    assinatura_id,
    tipo,
    status,
    resultado,
    creditos_cobrados,
    created_at
) VALUES (
    {{ $json.user_id }},
    {{ $json.participante_id }},
    {{ $json.plano_id }},
    {{ $json.assinatura_id }},
    'assinatura',
    'erro',
    '{"error_code": "INSUFFICIENT_CREDITS", "error_message": "{{ $json.skip_message }}"}'::jsonb,
    0,
    NOW()
);
```

---

### 8. PostgreSQL: Debitar Créditos (atômico)

**Tipo:** PostgreSQL

**Operation:** Execute Query

**Query:**
```sql
UPDATE users
SET
    credits = credits - {{ $json.custo_creditos }},
    updated_at = NOW()
WHERE id = {{ $json.user_id }}
  AND credits >= {{ $json.custo_creditos }}
RETURNING id, credits as credits_after;
```

**Validação no próximo node:**
```javascript
// Se não retornou nada, significa que não tinha créditos suficientes (race condition)
if ($input.first().json.length === 0) {
    throw new Error('Falha ao debitar créditos - possível race condition');
}
```

---

### 9. HTTP Request: Minha Receita

**Tipo:** HTTP Request

**Configuração:**
- Method: GET
- URL: `https://minhareceita.org/{{ $json.cnpj }}`
- Headers:
  - Accept: `application/json`
- On Error: Continue (para não parar o workflow)

---

### 10. IF Node: Plano Tem Consultas Pagas?

**Tipo:** IF

**Condição:**
```javascript
// Verifica se plano inclui consultas pagas
const consultasPagas = ['sintegra', 'tcu_consolidada', 'cnd_federal', 'crf_fgts',
                        'cnd_estadual', 'cndt', 'protestos', 'lista_devedores_pgfn',
                        'trabalho_escravo', 'ibama_autuacoes', 'processos_cnj'];

const incluidas = JSON.parse($json.consultas_incluidas || '[]');
return incluidas.some(c => consultasPagas.includes(c));
```

---

### 11. Code Node: Analisar Resultados e Gerar Alertas

```javascript
const item = $input.first().json;
const minhaReceita = $('HTTP Minha Receita').first().json;

// Buscar resultados das consultas pagas (se existirem)
// ... (similar ao workflow manual)

const alertas = [];
let situacao_geral = 'regular';
let tem_pendencias = false;
let proxima_validade = null;

// === ANÁLISE DE SITUAÇÃO CADASTRAL ===
const situacao = minhaReceita.situacao_cadastral?.toUpperCase() || '';

if (situacao !== 'ATIVA') {
    alertas.push({
        tipo: 'situacao_cadastral',
        criticidade: 'alta',
        titulo: 'Situação Cadastral Irregular',
        mensagem: `Empresa com situação ${situacao} na Receita Federal`,
        data: minhaReceita.data_situacao_cadastral
    });
    situacao_geral = 'irregular';
    tem_pendencias = true;
}

// === ANÁLISE DE CND FEDERAL (se consultado) ===
const cndFederal = item.cnd_federal;
if (cndFederal) {
    if (cndFederal.status === 'POSITIVA' || cndFederal.status === 'POSITIVA_COM_EFEITO_NEGATIVA') {
        alertas.push({
            tipo: 'cnd_federal',
            criticidade: cndFederal.status === 'POSITIVA' ? 'alta' : 'media',
            titulo: 'CND Federal com Pendências',
            mensagem: `Certidão ${cndFederal.status}`,
            data: cndFederal.data_emissao
        });
        if (situacao_geral !== 'irregular') situacao_geral = 'atencao';
        tem_pendencias = true;
    }

    // Verificar validade
    if (cndFederal.data_validade) {
        const validade = new Date(cndFederal.data_validade);
        if (!proxima_validade || validade < proxima_validade) {
            proxima_validade = validade;
        }

        // Alerta se vence em menos de 7 dias
        const diasParaVencer = Math.ceil((validade - new Date()) / (1000 * 60 * 60 * 24));
        if (diasParaVencer <= 7 && diasParaVencer > 0) {
            alertas.push({
                tipo: 'cnd_federal_vencimento',
                criticidade: 'media',
                titulo: 'CND Federal Próxima do Vencimento',
                mensagem: `Vence em ${diasParaVencer} dia(s)`,
                data: cndFederal.data_validade
            });
            if (situacao_geral === 'regular') situacao_geral = 'atencao';
        }
    }
}

// === ANÁLISE DE CRF/FGTS (se consultado) ===
const crf = item.crf_fgts;
if (crf && crf.situacao !== 'REGULAR') {
    alertas.push({
        tipo: 'crf_fgts',
        criticidade: 'media',
        titulo: 'CRF/FGTS Irregular',
        mensagem: `Situação: ${crf.situacao}`,
        data: crf.data_emissao
    });
    if (situacao_geral !== 'irregular') situacao_geral = 'atencao';
    tem_pendencias = true;
}

// === ANÁLISE TCU/LISTAS RESTRITIVAS (se consultado) ===
const tcu = item.tcu_consolidada;
if (tcu) {
    if (tcu.ceis) {
        alertas.push({
            tipo: 'ceis',
            criticidade: 'alta',
            titulo: 'Empresa no CEIS',
            mensagem: 'Cadastro de Empresas Inidôneas e Suspensas',
            data: null
        });
        situacao_geral = 'irregular';
        tem_pendencias = true;
    }
    if (tcu.cnep) {
        alertas.push({
            tipo: 'cnep',
            criticidade: 'alta',
            titulo: 'Empresa no CNEP',
            mensagem: 'Cadastro Nacional de Empresas Punidas',
            data: null
        });
        situacao_geral = 'irregular';
        tem_pendencias = true;
    }
}

// === MONTAR RESULTADO ===
const resultado = {
    // Dados da Minha Receita
    situacao_cadastral: minhaReceita.situacao_cadastral,
    razao_social: minhaReceita.razao_social,
    nome_fantasia: minhaReceita.nome_fantasia,
    simples_nacional: minhaReceita.simples_nacional?.optante || false,
    mei: minhaReceita.mei || false,

    // Consultas pagas (se existirem)
    sintegra: item.sintegra || null,
    tcu_consolidada: item.tcu_consolidada || null,
    cnd_federal: item.cnd_federal || null,
    crf_fgts: item.crf_fgts || null,
    cnd_estadual: item.cnd_estadual || null,
    cndt: item.cndt || null,
    protestos: item.protestos || null,

    // Análise
    alertas: alertas,
    total_alertas: alertas.length,
    alertas_criticos: alertas.filter(a => a.criticidade === 'alta').length,

    // Metadados
    consultado_em: new Date().toISOString()
};

return [{
    json: {
        ...item,
        resultado: resultado,
        situacao_geral: situacao_geral,
        tem_pendencias: tem_pendencias,
        proxima_validade: proxima_validade ? proxima_validade.toISOString().split('T')[0] : null,
        alertas: alertas,
        tem_alertas_criticos: alertas.some(a => a.criticidade === 'alta')
    }
}];
```

---

### 12. PostgreSQL: INSERT monitoramento_consultas

**Tipo:** PostgreSQL

**Operation:** Execute Query

**Query:**
```sql
INSERT INTO monitoramento_consultas (
    user_id,
    participante_id,
    plano_id,
    assinatura_id,
    tipo,
    status,
    resultado,
    situacao_geral,
    tem_pendencias,
    proxima_validade,
    creditos_cobrados,
    created_at
) VALUES (
    {{ $json.user_id }},
    {{ $json.participante_id }},
    {{ $json.plano_id }},
    {{ $json.assinatura_id }},
    'assinatura',
    'sucesso',
    {{ JSON.stringify($json.resultado) }}::jsonb,
    '{{ $json.situacao_geral }}',
    {{ $json.tem_pendencias }},
    {{ $json.proxima_validade ? "'" + $json.proxima_validade + "'" : 'NULL' }},
    {{ $json.custo_creditos }},
    NOW()
)
RETURNING id;
```

---

### 13. PostgreSQL: UPDATE próxima execução

**Tipo:** PostgreSQL

**Operation:** Execute Query

**Query:**
```sql
UPDATE monitoramento_assinaturas
SET
    ultima_execucao_em = NOW(),
    proxima_execucao_em = NOW() + INTERVAL '{{ $json.frequencia_dias }} days',
    updated_at = NOW()
WHERE id = {{ $json.assinatura_id }};
```

---

### 14. IF Node: Tem Alertas Críticos?

**Tipo:** IF

**Condição:**
```javascript
{{ $json.tem_alertas_criticos === true }}
```

---

### 15. HTTP Request: Notificar Alertas (opcional)

**Tipo:** HTTP Request

**Configuração:**
- Method: POST
- URL: `{{ $env.FISCALDOCK_API_URL }}/api/consultas/alertas`
- Authentication: Header Auth
- Body (JSON):

```json
{
    "user_id": {{ $json.user_id }},
    "participante_id": {{ $json.participante_id }},
    "assinatura_id": {{ $json.assinatura_id }},
    "cnpj": "{{ $json.cnpj }}",
    "razao_social": "{{ $json.razao_social }}",
    "alertas": {{ JSON.stringify($json.alertas) }},
    "situacao_geral": "{{ $json.situacao_geral }}"
}
```

---

## Queries SQL Completas

### Buscar Assinaturas Pendentes

```sql
SELECT
    ma.id as assinatura_id,
    ma.user_id,
    ma.participante_id,
    ma.plano_id,
    ma.frequencia_dias,
    p.cnpj,
    p.razao_social,
    p.uf,
    p.crt,
    mp.codigo as plano_codigo,
    mp.consultas_incluidas,
    mp.custo_creditos,
    u.credits as user_credits,
    u.name as user_name,
    u.email as user_email
FROM monitoramento_assinaturas ma
JOIN participantes p ON p.id = ma.participante_id
JOIN monitoramento_planos mp ON mp.id = ma.plano_id
JOIN users u ON u.id = ma.user_id
WHERE ma.status = 'ativo'
  AND ma.proxima_execucao_em <= NOW()
ORDER BY ma.proxima_execucao_em ASC
LIMIT 100;
```

### Debitar Créditos (atômico)

```sql
UPDATE users
SET
    credits = credits - $1,
    updated_at = NOW()
WHERE id = $2
  AND credits >= $1
RETURNING id, credits as credits_after;
```

### Pausar Assinatura

```sql
UPDATE monitoramento_assinaturas
SET
    status = 'pausado',
    updated_at = NOW()
WHERE id = $1;
```

### Registrar Consulta com Erro

```sql
INSERT INTO monitoramento_consultas (
    user_id, participante_id, plano_id, assinatura_id,
    tipo, status, resultado, creditos_cobrados, created_at
) VALUES (
    $1, $2, $3, $4,
    'assinatura', 'erro',
    $5::jsonb,
    0, NOW()
);
```

### Inserir Resultado de Consulta

```sql
INSERT INTO monitoramento_consultas (
    user_id, participante_id, plano_id, assinatura_id,
    tipo, status, resultado, situacao_geral, tem_pendencias,
    proxima_validade, creditos_cobrados, created_at
) VALUES (
    $1, $2, $3, $4,
    'assinatura', 'sucesso',
    $5::jsonb, $6, $7, $8, $9, NOW()
)
RETURNING id;
```

### Atualizar Próxima Execução

```sql
UPDATE monitoramento_assinaturas
SET
    ultima_execucao_em = NOW(),
    proxima_execucao_em = NOW() + ($1 || ' days')::interval,
    updated_at = NOW()
WHERE id = $2;
```

---

## Testes

### Criar Assinatura de Teste

```sql
-- 1. Criar assinatura que vai executar na próxima hora
INSERT INTO monitoramento_assinaturas (
    user_id,
    participante_id,
    plano_id,
    status,
    frequencia_dias,
    proxima_execucao_em,
    created_at,
    updated_at
) VALUES (
    1,  -- user_id
    1,  -- participante_id (deve existir)
    1,  -- plano_id (gratuito)
    'ativo',
    7,  -- semanal
    NOW() - INTERVAL '1 minute',  -- já está pendente
    NOW(),
    NOW()
)
RETURNING id;
```

### Executar Workflow Manualmente

No n8n, clicar em "Execute Workflow" para testar sem esperar o cron.

### Verificar Resultados

```sql
-- Verificar consulta criada
SELECT * FROM monitoramento_consultas ORDER BY id DESC LIMIT 1;

-- Verificar próxima execução atualizada
SELECT id, proxima_execucao_em, ultima_execucao_em
FROM monitoramento_assinaturas
WHERE id = ?;

-- Verificar créditos debitados
SELECT id, credits FROM users WHERE id = ?;
```

---

## Troubleshooting

### Cron não executa

**Verificar:**
1. Workflow está ativo no n8n?
2. Cron expression está correta?
3. Timezone do n8n está correto?

### Assinaturas não são processadas

**Verificar:**
```sql
-- Existem assinaturas pendentes?
SELECT COUNT(*) FROM monitoramento_assinaturas
WHERE status = 'ativo'
AND proxima_execucao_em <= NOW();

-- Verificar dados de uma assinatura específica
SELECT ma.*, p.cnpj, mp.consultas_incluidas, u.credits
FROM monitoramento_assinaturas ma
JOIN participantes p ON p.id = ma.participante_id
JOIN monitoramento_planos mp ON mp.id = ma.plano_id
JOIN users u ON u.id = ma.user_id
WHERE ma.id = ?;
```

### Créditos não são debitados

**Verificar:**
- Query de UPDATE retorna algum registro?
- Usuário tem créditos suficientes?
- Condição `credits >= custo` está sendo satisfeita?

---

**Última atualização:** 2026-01-31
