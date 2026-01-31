# Workflows n8n - Consultas de Participantes

Documentação completa dos workflows n8n para consulta de participantes no FiscalDock.

---

## Arquivos Disponíveis

| Arquivo | Descrição |
|---------|-----------|
| **[workflow-manual.md](./workflow-manual.md)** | Workflow de consulta manual (via webhook) |
| **[workflow-automatico.md](./workflow-automatico.md)** | Workflow de consulta automática (cron horário) |
| **[apis-consulta.md](./apis-consulta.md)** | Referência das APIs de consulta (Minha Receita + InfoSimples) |

---

## Visão Geral

### Dois Fluxos de Consulta

| Fluxo | Trigger | Processamento | Resultado |
|-------|---------|---------------|-----------|
| **Manual** | Usuário executa via UI | Laravel → Webhook → n8n | n8n retorna via API |
| **Automático** | n8n Cron (cada hora) | n8n consulta PostgreSQL direto | n8n grava direto no banco |

---

## Arquitetura

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           CONSULTA MANUAL                                    │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌──────────┐    POST     ┌──────────┐    Webhook    ┌──────────┐           │
│  │ Frontend │ ──────────► │  Laravel │ ────────────► │   n8n    │           │
│  │          │             │          │               │          │           │
│  └────▲─────┘             └────▲─────┘               └────┬─────┘           │
│       │ SSE                    │ API                      │                 │
│       │                        │ POST                     │                 │
│       │     ┌──────────────────┴──────────────────────────┘                 │
│       │     │ /api/consultas/lote/progress                                  │
│       │     │ /api/consultas/lote/resultado                                 │
│       └─────┴───────────────────────────────────────────────────────────────│
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                         CONSULTA AUTOMÁTICA                                  │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌──────────┐   SELECT    ┌──────────┐   INSERT/UPDATE   ┌──────────┐       │
│  │ n8n Cron │ ──────────► │PostgreSQL│ ◄───────────────► │   APIs   │       │
│  │ (1h)     │             │          │                   │(InfoSimp)│       │
│  └──────────┘             └──────────┘                   └──────────┘       │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Tabelas Envolvidas

### Consulta Manual

| Tabela | Uso |
|--------|-----|
| `raf_lotes` | Lote de consulta (1 por execução) |
| `raf_lote_participantes` | Pivot: participantes no lote |
| `raf_lote_resultados` | Resultado individual por participante |
| `users` | Créditos (debitar antes de enviar) |

### Consulta Automática

| Tabela | Uso |
|--------|-----|
| `monitoramento_assinaturas` | Assinaturas ativas com próxima execução |
| `monitoramento_consultas` | Resultado de cada consulta automática |
| `monitoramento_planos` | Planos com consultas incluídas |
| `participantes` | Dados do CNPJ a consultar |
| `users` | Créditos (debitar atomicamente) |

---

## Planos de Consulta

| Código | Créditos | Consultas Incluídas |
|--------|----------|---------------------|
| `gratuito` | 0 | situacao_cadastral, dados_cadastrais, endereco, cnaes, qsa, simples_nacional, mei |
| `validacao` | 4 | Gratuito + sintegra, tcu_consolidada |
| `licitacao` | 10 | Validação + cnd_federal, crf_fgts, cnd_estadual, cndt |
| `compliance` | 14 | Licitação + protestos, lista_devedores_pgfn |
| `due_diligence` | 18 | Compliance + trabalho_escravo, ibama_autuacoes |
| `enterprise` | 20 | Due Diligence + processos_cnj |

---

## Webhook URL

```
https://autowebhook.fiscaldock.com.br/webhook/consultas
```

**Headers:**
```
Content-Type: application/json
X-API-Token: {API_TOKEN}
```

---

## Endpoints de Retorno (n8n → Laravel)

| Endpoint | Método | Descrição |
|----------|--------|-----------|
| `/api/consultas/lote/progress` | POST | Progresso da consulta manual |
| `/api/consultas/lote/resultado` | POST | Resultado individual por participante |
| `/api/consultas/alertas` | POST | Alertas críticos (opcional) |

---

## Regras de Negócio

### Créditos

1. **Consulta Manual:** Laravel debita ANTES de enviar para n8n
2. **Consulta Automática:** n8n debita atomicamente durante execução
3. **Refund:** Se erro no processamento, Laravel faz refund via API de progresso

### Créditos Insuficientes (Automática)

Quando assinatura não tem créditos suficientes:
1. Status muda para `pausado`
2. Registra erro em `monitoramento_consultas`
3. Usuário precisa reativar manualmente após adicionar créditos

### Frequências de Monitoramento

| Código | Dias | Descrição |
|--------|------|-----------|
| `diario` | 1 | Todo dia |
| `semanal` | 7 | Uma vez por semana |
| `quinzenal` | 15 | A cada 15 dias |
| `mensal` | 30 | Uma vez por mês |

---

## Status de Consulta

### RafLote (Manual)

| Status | Descrição |
|--------|-----------|
| `pendente` | Criado, aguardando n8n |
| `processando` | n8n está processando |
| `concluido` | Todos participantes processados |
| `erro` | Falha no processamento |

### MonitoramentoAssinatura (Automática)

| Status | Descrição |
|--------|-----------|
| `ativo` | Pronta para executar |
| `pausado` | Pausada (créditos insuficientes ou manual) |
| `cancelado` | Cancelada permanentemente |

### MonitoramentoConsulta (Resultado)

| Status | Descrição |
|--------|-----------|
| `pendente` | Aguardando processamento |
| `processando` | Em execução |
| `sucesso` | Concluída com sucesso |
| `erro` | Falha na consulta |

---

## Situação Geral do Participante

| Situação | Cor | Significado |
|----------|-----|-------------|
| `regular` | Verde | Tudo OK |
| `atencao` | Amarelo | Pendências menores |
| `irregular` | Vermelho | Problemas graves |

### Critérios de Classificação

| Evento | Situação |
|--------|----------|
| Empresa ATIVA, sem pendências | `regular` |
| CND próxima do vencimento | `atencao` |
| CRF irregular | `atencao` |
| Empresa BAIXADA/INAPTA | `irregular` |
| CND POSITIVA | `irregular` |
| Em lista restritiva (CEIS/CNEP) | `irregular` |

---

## Alertas Críticos

| Evento | Criticidade | Ação |
|--------|-------------|------|
| Empresa baixada/inapta | Alta | Notificar imediatamente |
| IE baixada/suspensa | Alta | Notificar |
| Entrou CEIS/CNEP | Alta | Notificar |
| Lista trabalho escravo | Alta | Notificar |
| CND venceu/positiva | Média | Marcar atenção |
| CRF irregular | Média | Marcar atenção |
| Desenquadrou Simples | Média | Marcar atenção |
| Nova autuação IBAMA | Baixa | Registrar |

---

## Variáveis de Ambiente

### Laravel (.env)

```env
# Webhook de consultas
WEBHOOK_CONSULTAS_LOTES_URL=https://autowebhook.fiscaldock.com.br/webhook/consultas

# Token para autenticação n8n → Laravel
API_TOKEN=seu-token-seguro
```

### n8n

```env
# API Laravel
FISCALDOCK_API_URL=https://fiscaldock.com.br
FISCALDOCK_API_TOKEN=seu-token-seguro

# PostgreSQL
DB_POSTGRESDB_HOST=postgres
DB_POSTGRESDB_PORT=5432
DB_POSTGRESDB_DATABASE=fiscaldock
DB_POSTGRESDB_USER=postgres
DB_POSTGRESDB_PASSWORD=senha-segura

# InfoSimples
INFOSIMPLES_TOKEN=seu-token-infosimples
```

---

## Troubleshooting

### Consulta manual não inicia

**Sintomas:** Usuário clica "Executar" mas nada acontece

**Verificar:**
```bash
# Logs Laravel
tail -f storage/logs/laravel.log | grep -i consulta

# Webhook URL
php artisan tinker
config('services.webhook.consultas_lotes_url')
```

### Consulta automática não executa

**Sintomas:** Assinaturas ativas não são processadas

**Verificar:**
```sql
-- Assinaturas pendentes
SELECT * FROM monitoramento_assinaturas
WHERE status = 'ativo'
AND proxima_execucao_em <= NOW();

-- Verificar se cron está rodando (logs n8n)
```

### Créditos não são debitados

**Sintomas:** Consulta executa mas créditos não diminuem

**Verificar:**
```sql
-- Verificar créditos do usuário
SELECT id, name, credits FROM users WHERE id = ?;

-- Verificar se consulta registrou débito
SELECT creditos_cobrados FROM raf_lotes WHERE id = ?;
SELECT creditos_cobrados FROM monitoramento_consultas WHERE id = ?;
```

---

## Changelog

### 2026-01-31 - v1.0
- Documentação inicial criada
- Especificação dos dois workflows (manual e automático)
- Referência de APIs de consulta

---

## Recursos Relacionados

- [CLAUDE.md](/opt/hub_contabil/CLAUDE.md) - Documentação geral do projeto
- [docs/n8n-workflows/SPED/](../SPED/) - Workflows de importação SPED
- [Minha Receita API](https://minhareceita.org/) - API gratuita de CNPJ
- [InfoSimples API](https://api.infosimples.com/docs) - APIs pagas de consulta

---

**Última atualização:** 2026-01-31
