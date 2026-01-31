# Workflows n8n - SPED (EFD Fiscal / EFD Contribuições)

Documentação completa dos workflows n8n para processamento de arquivos SPED no FiscalDock.

---

## Arquivos Disponíveis

| Arquivo | Descrição |
|---------|-----------|
| **[validacao-tipo-arquivo.md](./validacao-tipo-arquivo.md)** | Guia completo de implementação da validação de tipo de arquivo SPED |
| **[code-node-validacao.js](./code-node-validacao.js)** | Code Node standalone para validação (copiar/colar no n8n) |
| **[workflow-completo.md](./workflow-completo.md)** | Diagrama e especificação completa do workflow SPED |
| **[json-template-regras.md](./json-template-regras.md)** | Referência rápida das regras de templates JSON no n8n |
| **[extracao-notas-fiscais.md](./extracao-notas-fiscais.md)** | Implementação da extração de notas fiscais (C100, A100) para BI |

---

## Quick Start

### 1. Implementar Validação de Tipo

**Objetivo:** Detectar e bloquear arquivos SPED do tipo errado antes de processar.

**Passos:**
1. Leia [validacao-tipo-arquivo.md](./validacao-tipo-arquivo.md)
2. Copie o código de [code-node-validacao.js](./code-node-validacao.js)
3. Cole no Code Node logo após o Webhook no n8n
4. Configure IF Node e HTTP Request conforme documentação
5. Teste com arquivos corretos e incorretos

**Tempo Estimado:** 30 minutos

---

### 2. Entender Workflow Completo

**Objetivo:** Compreender o fluxo completo de importação SPED.

**Passos:**
1. Leia [workflow-completo.md](./workflow-completo.md)
2. Visualize o diagrama ASCII do fluxo
3. Revise a configuração de cada node
4. Implemente error handling global

**Tempo Estimado:** 1 hora

---

### 3. Dominar Templates JSON

**Objetivo:** Evitar erros de sintaxe JSON nos HTTP Request nodes.

**Passos:**
1. Leia [json-template-regras.md](./json-template-regras.md)
2. Use a tabela de referência ao criar payloads
3. Valide cada HTTP Request node com o checklist
4. Teste payloads com ferramentas como Postman

**Tempo Estimado:** 15 minutos

---

## Arquitetura FiscalDock

### Princípio Fundamental

**Laravel faz SELECT only. n8n faz ALL INSERT/UPDATE/DELETE via PostgreSQL direto.**

### Fluxo de Dados

```
┌─────────────┐
│   USUÁRIO   │
│  (Frontend) │
└──────┬──────┘
       │ Upload SPED
       ▼
┌─────────────────────┐
│    LARAVEL          │
│ - Valida sessão     │
│ - Salva metadata    │
│ - Envia webhook     │
└──────┬──────────────┘
       │ POST webhook com arquivo_base64
       ▼
┌────────────────────────────────────┐
│          n8n WORKFLOW              │
│ 1. Validar tipo arquivo            │
│ 2. Decodificar base64              │
│ 3. Extrair participantes           │
│ 4. UPSERT PostgreSQL direto        │
│ 5. Enviar progresso via API        │
└──────┬─────────────────────────────┘
       │ POST /api/.../progress
       ▼
┌─────────────────────┐
│    LARAVEL          │
│ - Salva no cache    │
│ - SSE envia ao user │
└──────┬──────────────┘
       │ SSE
       ▼
┌─────────────┐
│   FRONTEND  │
│ (Atualiza   │
│  progresso) │
└─────────────┘
```

---

## Tipos de Arquivos SPED

### EFD ICMS/IPI (Fiscal)

**Usado por:** Empresas sujeitas a ICMS e/ou IPI
**Registros Típicos:** C100, C170, E100, E110, H005
**Periodicidade:** Mensal
**Campo Indicador:** `|0000|xxx|0|...` (IND_TIPO = 0)

### EFD Contribuições

**Usado por:** Empresas sujeitas a PIS/COFINS
**Registros Típicos:** M100, M500, A100, F100
**Periodicidade:** Mensal
**Campo Indicador:** `|0000|xxx|1|...` (IND_TIPO = 1)

---

## Códigos de Erro

| error_code | Descrição | Solução |
|------------|-----------|---------|
| `INVALID_SPED` | Tipo de arquivo incorreto ou inválido | Selecionar tipo correto ou validar arquivo |
| `INVALID_BASE64` | Arquivo corrompido ou base64 inválido | Re-upload do arquivo |
| `NO_INPUT` | Webhook não recebeu payload | Verificar configuração do webhook |
| `MISSING_FIELDS` | Campos obrigatórios ausentes | Verificar payload do Laravel |
| `DB_ERROR` | Erro ao inserir no PostgreSQL | Verificar logs do banco |
| `PARSE_ERROR` | Erro ao parsear registros SPED | Arquivo pode estar corrompido |

---

## API Endpoints

### Laravel → n8n

| Endpoint | Método | Descrição |
|----------|--------|-----------|
| `/webhook/sped/importacao` | POST | Envia arquivo SPED para processamento |

### n8n → Laravel

| Endpoint | Método | Descrição |
|----------|--------|-----------|
| `/api/monitoramento/sped/importacao-txt/progress` | POST | Envia progresso/status/erros |

---

## Payload Contracts

### Laravel → n8n (Webhook Input)

```json
{
  "user_id": 1,
  "importacao_id": 123,
  "tab_id": "550e8400-e29b-41d4-a716-446655440000",
  "tipo_efd": "EFD Fiscal",
  "cliente_id": 10,
  "progress_url": "https://fiscaldock.com.br/api/monitoramento/sped/importacao-txt/progress",
  "arquivo_base64": "MDAwMHxDMDAwfC4uLg=="
}
```

### n8n → Laravel (Progress/Error)

```json
{
  "user_id": 1,
  "tab_id": "550e8400-e29b-41d4-a716-446655440000",
  "progresso": 0,
  "status": "erro",
  "mensagem": "Tipo de arquivo incorreto",
  "error_code": "INVALID_SPED",
  "error_message": "O arquivo enviado é do tipo EFD Contribuições...",
  "dados": {
    "tipo_esperado": "EFD Fiscal",
    "tipo_detectado": "EFD Contribuições"
  }
}
```

---

## Variáveis de Ambiente (n8n)

```env
# API Laravel
API_TOKEN=seu-token-seguro-aqui

# PostgreSQL
DB_POSTGRESDB_HOST=postgres
DB_POSTGRESDB_PORT=5432
DB_POSTGRESDB_DATABASE=fiscaldock
DB_POSTGRESDB_USER=postgres
DB_POSTGRESDB_PASSWORD=senha-segura

# Webhook Authentication
WEBHOOK_USERNAME=n8n
WEBHOOK_PASSWORD=senha-webhook
```

---

## Performance e Limites

| Métrica | Valor Típico | Limite Máximo |
|---------|--------------|---------------|
| Tamanho do arquivo | 5-20MB | 100MB |
| Participantes/arquivo | 100-500 | 10.000 |
| Tempo de processamento | 30s-2min | 1 hora |
| Validação de tipo | 50-100ms | 500ms |
| Taxa de erro INVALID_SPED | < 0.5% | - |

---

## Testes Recomendados

### Teste 1: Happy Path - EFD Fiscal Correto
- [ ] Upload arquivo EFD Fiscal válido
- [ ] Tipo selecionado: "EFD Fiscal"
- [ ] Verificar progresso 0%, 50%, 100%
- [ ] Verificar participantes inseridos
- [ ] Verificar status final "concluido"

### Teste 2: Tipo Incorreto
- [ ] Upload arquivo EFD Contribuições
- [ ] Tipo selecionado: "EFD Fiscal"
- [ ] Verificar workflow para no IF
- [ ] Verificar erro enviado ao Laravel
- [ ] Verificar mensagem no frontend
- [ ] Verificar status "erro" no banco

### Teste 3: Arquivo Corrompido
- [ ] Upload arquivo não-SPED (PDF/Excel)
- [ ] Tipo selecionado: "EFD Fiscal"
- [ ] Verificar error_code "INVALID_SPED"
- [ ] Verificar mensagem amigável

### Teste 4: Arquivo Grande
- [ ] Upload arquivo > 50MB
- [ ] Verificar que não dá timeout
- [ ] Verificar progresso incremental
- [ ] Verificar batch processing

---

## Troubleshooting

### Workflow não inicia

**Sintomas:** Arquivo enviado mas workflow não executa

**Causas:**
- Webhook URL incorreta no Laravel
- Basic Auth inválido
- Workflow desativado no n8n

**Solução:**
```bash
# Verificar logs Laravel
tail -f storage/logs/laravel.log | grep SPED

# Verificar webhook URL
php artisan tinker
config('services.webhook.sped_fiscal_url')

# Verificar n8n workflow status
curl -u username:password https://n8n.fiscaldock.com.br/webhook/sped/importacao
```

---

### Erro "JSON parameter needs to be valid JSON"

**Sintomas:** HTTP Request node falha com erro de JSON inválido

**Causa:** Array ou Object sem `JSON.stringify()`

**Solução:** Leia [json-template-regras.md](./json-template-regras.md)

---

### Frontend não recebe progresso

**Sintomas:** Upload completa mas barra de progresso não atualiza

**Causas:**
- Cache Redis não acessível
- tab_id incorreto
- SSE connection fechada

**Solução:**
```bash
# Verificar cache Redis
redis-cli
> GET "progresso:1:550e8400-e29b-41d4-a716-446655440000"

# Verificar logs SSE
tail -f storage/logs/laravel.log | grep SSE
```

---

### Participantes duplicados

**Sintomas:** UPSERT cria duplicatas em vez de atualizar

**Causa:** Constraint `(user_id, cnpj)` não existe

**Solução:**
```sql
-- Verificar constraint
SELECT conname FROM pg_constraint
WHERE conrelid = 'participantes'::regclass;

-- Criar se não existir
ALTER TABLE participantes
ADD CONSTRAINT participantes_user_id_cnpj_unique
UNIQUE (user_id, cnpj);
```

---

## Recursos Adicionais

### Documentação Oficial SPED

- [EFD ICMS/IPI - Guia Prático](http://sped.rfb.gov.br/pasta/show/1644)
- [EFD Contribuições - Guia Prático](http://sped.rfb.gov.br/pasta/show/1645)
- [Leiaute dos Registros](http://sped.rfb.gov.br/spedtabelas/AppConsulta/publico/aspx/)

### n8n Documentation

- [Code Node Expressions](https://docs.n8n.io/code-examples/expressions/)
- [HTTP Request Node](https://docs.n8n.io/integrations/builtin/core-nodes/n8n-nodes-base.httprequest/)
- [Postgres Node](https://docs.n8n.io/integrations/builtin/app-nodes/n8n-nodes-base.postgres/)

### FiscalDock

- [CLAUDE.md](/opt/hub_contabil/CLAUDE.md) - Documentação geral do projeto
- [API Routes](/opt/hub_contabil/routes/api.php) - Endpoints disponíveis

---

## Changelog

### 2026-01-31 - v2.1
- ✅ Adicionada validação de tipo de arquivo SPED
- ✅ Implementado error handling robusto
- ✅ Documentação completa criada
- ✅ Referência de templates JSON

### 2025-12-15 - v2.0
- Migração para nova arquitetura (Laravel read-only)
- n8n escreve direto no PostgreSQL
- SSE para progresso em tempo real

### 2025-10-01 - v1.0
- Implementação inicial
- Processamento básico de SPED

---

## Contribuindo

Ao modificar workflows SPED:

1. **Sempre** teste com arquivos reais
2. **Sempre** atualize esta documentação
3. **Sempre** use as regras de templates JSON
4. **Sempre** adicione logging adequado
5. **Sempre** implemente error handling

---

## Contato

**Equipe:** FiscalDock Development Team
**Última Revisão:** 2026-01-31
**Versão da Documentação:** 2.1.0

---

**Próximos Passos:**
1. ~~Implementar extração de notas fiscais (registro C100)~~ ✅ Implementado - ver [extracao-notas-fiscais.md](./extracao-notas-fiscais.md)
2. Adicionar validação de período/datas
3. Implementar cache de parsing para re-upload
4. Adicionar suporte a SPED Contábil (ECD)
