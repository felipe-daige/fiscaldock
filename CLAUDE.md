# CLAUDE.md

## Project Overview

FiscalDock - Laravel 12 app for Brazilian tax compliance. Processes SPED files (EFD Contribuicoes/Fiscal), tax regime analysis, integrates with n8n for async processing.

**Stack:** Laravel 12, PHP 8.2+, PostgreSQL, Blade, Tailwind 4.0, Vite, Pest, Docker/Traefik

## Architecture: Laravel = Thin Layer, n8n = Heavy Work

**CRITICAL:** Laravel does SELECT only. n8n does all INSERT/UPDATE/DELETE via direct PostgreSQL.

| Laravel (Lightweight) | n8n (Heavy Processing) |
|-----------------------|------------------------|
| Auth, sessions | SPED parsing |
| Request coordination | Government API calls |
| Database reads (SELECT) | CND checks |
| Credit management | Report generation |
| Blade presentation | **ALL DB writes** |
| Trigger n8n webhooks | External integrations |

**Exceptions (Laravel writes):** `RafConsultaPendente`, `ImportacaoSped`, `NotaFiscal.validacao` (VCI), `NotaSped.validacao` (VCI), user sessions

## Navegacao (Sidebar)

Estrutura simplificada em 5 secoes principais:

```
PRINCIPAL
├── Dashboard
└── Alertas

IMPORTACAO
├── XMLs (NF-e/CT-e)
├── SPED
└── CNPJ Avulso

CADASTROS
├── Participantes
└── Clientes
    ├── Todos os Clientes
    └── Novo Cliente

CONSULTAS (unifica RAF + Monitoramento)
├── Nova Consulta     → /app/consultas/nova
├── Planos Disponiveis → /app/consultas/planos
├── Historico         → /app/consultas/historico
└── Relatorios        → /app/consultas/relatorios

COMPLIANCE
├── Score de Risco
└── Validacao Contabil
    ├── Dashboard
    └── Alertas

BI FISCAL
└── Dashboard BI

USUARIO (footer)
├── Perfil
├── Meu Plano
├── Configuracoes
└── Sair
```

**Rotas legadas mantidas para compatibilidade:**
- `/app/raf/*` continua funcionando (redireciona internamente)
- `/app/monitoramento/planos` → alias para `/app/consultas/planos`

## Commands

```bash
# Dev
docker compose -f docker-compose.dev.yml up -d  # http://localhost:8080
composer dev                                     # Native: serve + queue + vite

# Test & Quality
composer test                    # or: php artisan test
./vendor/bin/pint                # PSR-12 formatting

# Database
php artisan migrate
php artisan migrate:fresh --seed

# Deploy (Swarm)
docker service update --image felipedaige/fiscaldock:X.X.X fiscaldock_app
docker service update --image felipedaige/fiscaldock:X.X.X fiscaldock_scheduler
```

## Core Modules

### Consultas (antigo RAF)

**Arquitetura v2.1:** Resultados no banco + Relatorios on-demand no Laravel

1. Usuario acessa `/app/consultas/nova` e seleciona participantes ja importados
2. Escolhe plano de consulta (gratuito a enterprise)
3. Laravel debita creditos, cria `RafLote`, envia para n8n via webhook
4. n8n consulta APIs (Minha Receita + InfoSimples conforme plano)
5. n8n envia resultado de CADA participante para `POST /api/raf/lote/resultado`
6. n8n envia progresso/status final para `POST /api/raf/lote/progress`
7. Laravel gera CSV/PDF on-demand a partir de `raf_lote_resultados`

**Vantagens da nova arquitetura:**
- Relatorios customizados (logo, metadados, links)
- Suporte a PDF alem de CSV
- Dados persistidos para re-consulta
- Calculo de score on-demand via `RiskScoreService`

**Models:** `RafLote`, `RafLoteResultado` (novo), `RafConsultaPendente` (legado), `RafRelatorioProcessado` (legado)

**Services:** `RafReportService` (geracao CSV/PDF), `RiskScoreService` (calculo de scores)

**Routes (novas - preferir estas):**
| Route | Description |
|-------|-------------|
| `GET /app/consultas/nova` | Tela de selecao de participantes |
| `GET /app/consultas/nova/participantes` | AJAX lista participantes |
| `POST /app/consultas/nova/calcular-custo` | Calcular custo antes de executar |
| `POST /app/consultas/nova/executar` | Iniciar consulta |
| `GET /app/consultas/nova/progresso/stream` | SSE progresso |
| `GET /app/consultas/lote/{id}/baixar?formato=csv` | Download CSV (default) |
| `GET /app/consultas/lote/{id}/baixar?formato=pdf` | Download PDF |
| `GET /app/consultas/historico` | Historico unificado |
| `GET /app/consultas/planos` | Lista de planos disponiveis |
| `GET /app/consultas/relatorios` | Downloads disponiveis |
| `POST /api/raf/lote/progress` | n8n envia progresso |
| `POST /api/raf/lote/resultado` | n8n envia resultado por participante |

**Routes legadas (compatibilidade):**
| Route | Redireciona para |
|-------|------------------|
| `/app/raf/consulta` | Funciona (mesma view) |
| `/app/raf/historico` | Funciona (mesma view) |
| `/app/raf/lote/{id}/baixar` | Funciona (mesmo endpoint) |

**Webhook payload (enviado ao n8n):**
```json
{
  "user_id": 1,
  "raf_lote_id": 123,
  "tab_id": "uuid",
  "plano_codigo": "licitacao",
  "consultas_incluidas": ["situacao_cadastral", "sintegra", "cnd_federal"],
  "participantes": [
    {"id": 1, "cnpj": "12345678000190", "razao_social": "...", "uf": "SP"},
    ...
  ],
  "progress_url": "https://fiscaldock.com.br/api/raf/lote/progress",
  "resultado_url": "https://fiscaldock.com.br/api/raf/lote/resultado"
}
```

**Resultado individual (n8n -> Laravel):**
```json
POST /api/raf/lote/resultado
Header: X-API-Token: {token}

{
  "raf_lote_id": 123,
  "user_id": 1,
  "tab_id": "uuid",
  "participante_id": 456,
  "status": "sucesso",
  "resultado_dados": {
    "consultas_realizadas": ["situacao_cadastral", "sintegra", "cnd_federal"],
    "situacao_cadastral": "ATIVA",
    "razao_social": "EMPRESA LTDA",
    "simples_nacional": true,
    "mei": false,
    "cnaes": {"principal": "6201-5/00"},
    "sintegra": {"ie": "123456789", "situacao": "HABILITADO"},
    "cnd_federal": {"status": "NEGATIVA", "validade": "2026-07-30"}
  }
}
```

**Progresso simplificado (n8n -> Laravel):**
```json
POST /api/raf/lote/progress
{
  "user_id": 1,
  "tab_id": "uuid",
  "raf_lote_id": 123,
  "progresso": 100,
  "status": "concluido",
  "mensagem": "Processamento concluído",
  "resultado_resumo": {"total": 50, "sucesso": 48, "erro": 2}
}
```
Nota: `report_csv_base64` NAO e mais necessario no payload de progresso.

### Monitoramento (CNPJ Monitoring)
Continuous tracking of CNPJ tax status via subscriptions.

**Models:** `Participante`, `MonitoramentoPlano`, `MonitoramentoAssinatura`, `MonitoramentoConsulta`

**Routes:**
- `/app/monitoramento/sped` - Import SPED files
- `/app/monitoramento/xml` - Import XML (NF-e/NFS-e/CT-e)
- `/app/monitoramento/avulso` - Single CNPJ query
- `/app/monitoramento/participantes` - List all participantes
- `/app/monitoramento/clientes` - Client monitoring (placeholder)

### XML Import (NF-e, NFS-e, CT-e)

**Models:** `ImportacaoXml`, `XmlChaveProcessada`, `NotaFiscal`

**Flow:** User uploads XMLs -> Laravel sends ZIP to n8n -> n8n extracts participantes + optional notas_fiscais -> Progress via SSE

| Route | Description |
|-------|-------------|
| `POST /app/monitoramento/xml/importar` | Start import |
| `POST /api/monitoramento/xml/importacao/progress` | n8n sends progress |
| `GET /app/monitoramento/xml/progresso/stream` | SSE progress |

**Limits:** 50MB/file, 200MB total, 5000 XMLs max

### BI Fiscal (Analytics)
Dashboard gerencial para analise de faturamento, compras e tributos.

**Models:** `NotaFiscal` (dados existentes)

**Services:** `AnalyticsService`

**Routes:**
- `/app/analytics` - Dashboard principal com KPIs e graficos
- `/app/analytics/faturamento` - API de dados de faturamento
- `/app/analytics/compras` - API de dados de compras
- `/app/analytics/tributos` - API de dados tributarios

**Metricas disponiveis:**
- Faturamento por periodo (mensal)
- Top 10 clientes/fornecedores
- Faturamento por UF
- Entradas vs Saidas
- Carga tributaria efetiva
- Tributos por tipo (ICMS, PIS, COFINS, IPI)

### Risk Score (Score de Risco)
Avaliacao de risco fiscal e compliance de participantes.

**Models:** `ParticipanteScore`, `Participante`

**Services:** `RiskScoreService`

**Routes:**
- `/app/risk` - Dashboard de scores
- `/app/risk/participante/{id}` - Detalhes do participante
- `/app/risk/participante/{id}/consultar` - Atualizar score

**Categorias de score (peso):**
| Categoria | Peso | Fonte |
|-----------|------|-------|
| Cadastral | 15% | Situacao RF |
| CND Federal | 20% | PGFN |
| CND Estadual | 15% | SEFAZ |
| FGTS | 10% | CRF |
| Trabalhista | 10% | CNDT |
| Compliance | 15% | CEIS/CNEP/TCU |
| ESG | 10% | Trab. Escravo/IBAMA |
| Protestos | 5% | IEPTB |

**Classificacao:**
- 0-20: Baixo risco (verde)
- 21-50: Medio risco (amarelo)
- 51-80: Alto risco (laranja)
- 81-100: Critico (vermelho)

### Validacao Contabil Inteligente (VCI)
Sistema de analise e validacao de notas fiscais baseado em regras contabeis brasileiras.

**Models:** `NotaFiscal` (campo `validacao` JSONB)

**Services:** `ValidacaoContabilService`

**Routes:**
- `/app/validacao` - Dashboard principal de validacao
- `/app/validacao/alertas` - Lista de alertas por nivel/categoria
- `/app/validacao/nota/{id}` - Detalhes de validacao de nota
- `POST /app/validacao/validar-notas` - Validar notas especificas
- `POST /app/validacao/validar-importacao/{id}` - Validar toda importacao
- `POST /app/validacao/calcular-custo` - Calcular custo antes de validar

**Categorias de validacao (peso):**
| Categoria | Peso | Descricao |
|-----------|------|-----------|
| Cadastral | 20% | CRT declarado vs situacao RF |
| Tributacao | 25% | Aliquotas vs regime tributario |
| CFOP/CST | 20% | Combinacoes validas |
| Integridade | 15% | Soma tributos vs total |
| NCM | 10% | NCMs genericos/invalidos |
| Operacoes | 10% | Participante em listas restritivas |

**Classificacao:**
- 0-10: Conforme (verde)
- 11-30: Atencao (amarelo)
- 31-60: Irregular (laranja)
- 61-100: Critico (vermelho)

**Niveis de Alerta:**
| Nivel | Cor | Acao |
|-------|-----|------|
| BLOQUEANTE | Vermelho | Impede aprovacao, requer correcao |
| ATENCAO | Amarelo | Revisar manualmente |
| INFO | Azul | Informativo apenas |

**Precificacao:**
| Camada | Custo |
|--------|-------|
| Regras Locais | GRATIS |
| Validacao Completa | 1 cr/participante |
| Deep Analysis | 3 cr/participante |

**Estrutura do campo `validacao` (JSONB):**
```json
{
  "score_total": 25,
  "classificacao": "atencao",
  "scores": {
    "cadastral": 0,
    "tributacao": 40,
    "cfop_cst": 0,
    "integridade": 0,
    "ncm": 10,
    "operacoes": 0
  },
  "alertas": [
    {
      "categoria": "tributacao",
      "nivel": "atencao",
      "codigo": "SIMPLES_ICMS_ALTO",
      "mensagem": "Simples Nacional com ICMS destacado acima de 5%",
      "detalhe": "Aliquota efetiva: 7.5%"
    }
  ],
  "validado_em": "2026-01-28T10:30:00Z"
}
```

## Database - Tabelas Principais

### participantes
| Campo | Descricao |
|-------|-----------|
| `user_id` | Owner |
| `cnpj` | CNPJ (unique per user) |
| `razao_social`, `nome_fantasia` | Company names |
| `uf`, `cep`, `municipio`, `telefone` | Address data |
| `crt` | 1=Simples, 2=Excesso, 3=Normal |
| `cliente_id` | FK to clientes (optional) |
| `importacao_sped_id` | FK to importacoes_sped (optional) |
| `importacao_xml_id` | FK to importacoes_xml (optional) |
| `origem_tipo` | `SPED_EFD_FISCAL`, `SPED_EFD_CONTRIB`, `NFE`, `NFSE`, `CTE`, `MANUAL` |
| `origem_ref` | JSONB with source details |

### notas_fiscais
| Campo | Tipo | Descricao |
|-------|------|-----------|
| `cliente_id` | FK nullable | Link para clientes |
| `chave_acesso` | string(44) | Chave unica da nota |
| `tipo_nota` | smallint | 0=entrada, 1=saida |
| `finalidade` | smallint | 1=normal, 2=compl, 3=ajuste, 4=devolucao |
| `emit/dest_participante_id` | FK | Link para participantes |
| `payload` | JSONB | JSON completo do XML |
| `validacao` | JSONB nullable | Resultado da validacao contabil (VCI) |

### importacoes_sped (antes importacoes_participantes)
Tracks SPED import jobs with status, counts, and optional nota extraction.

| Campo | Tipo | Descricao |
|-------|------|-----------|
| `user_id` | FK | Owner |
| `tipo_efd` | varchar(30) | EFD_FISCAL ou EFD_CONTRIB |
| `filename` | varchar | Nome do arquivo |
| `arquivo_base64` | text nullable | Conteudo do arquivo SPED em base64 |
| `total_participantes` | int | Total no arquivo |
| `total_cnpjs_unicos` | int | CNPJs unicos |
| `total_cpfs_unicos` | int | CPFs unicos |
| `novos` | int | Novos inseridos |
| `duplicados` | int | Ja existiam |
| `status` | varchar(20) | pendente, processando, concluido, erro |
| `extrair_notas` | bool | Se deve extrair notas fiscais (feature futura) |
| `total_notas` | int | Total de notas no arquivo |
| `notas_extraidas` | int | Notas efetivamente extraidas |
| `creditos_cobrados` | int | Creditos debitados pela extracao |
| `participante_ids` | JSONB | Array de IDs criados |

### importacoes_xml
Tracks XML import jobs with status, counts, and `participante_ids` array.

### notas_sped
Notas fiscais extraidas de arquivos SPED (separada de notas_fiscais para dados de XML).

| Campo | Tipo | Descricao |
|-------|------|-----------|
| `user_id` | FK | Owner |
| `cliente_id` | FK nullable | Cliente associado |
| `importacao_sped_id` | FK | Importacao de origem |
| `emit_participante_id` | FK nullable | Emitente |
| `dest_participante_id` | FK nullable | Destinatario |
| `tipo_efd` | varchar(30) | EFD_FISCAL ou EFD_CONTRIB |
| `registro` | varchar(10) | Registro SPED origem (C100, C170, M100) |
| `tipo_nota` | smallint | 0=entrada, 1=saida |
| `modelo_doc` | varchar(2) | 55=NFe, 57=CTe, etc |
| `serie`, `numero_nota` | varchar | Dados da nota |
| `chave_acesso` | varchar(44) | Chave da NFe (quando disponivel) |
| `data_emissao`, `data_entrada_saida` | date | Datas |
| `valor_total` | decimal(15,2) | Valor total |
| `valor_icms`, `valor_icms_st`, `valor_ipi` | decimal(15,2) | Tributos |
| `valor_pis`, `valor_cofins` | decimal(15,2) | Contribuicoes |
| `valor_frete`, `valor_desconto` | decimal(15,2) | Outros valores |
| `cfop_principal` | varchar(4) | CFOP predominante |
| `payload` | JSONB nullable | Dados completos (opcional) |
| `validacao` | JSONB nullable | Resultado VCI |

### monitoramento_*
- `monitoramento_planos` - Available plans
- `monitoramento_assinaturas` - User subscriptions
- `monitoramento_consultas` - Query history

### raf_lotes (nova arquitetura RAF)
| Campo | Tipo | Descricao |
|-------|------|-----------|
| `user_id` | FK | Owner |
| `cliente_id` | FK nullable | Cliente associado |
| `plano_id` | FK | Plano de consulta |
| `status` | varchar(20) | pendente, processando, concluido, erro |
| `total_participantes` | int | Quantidade de CNPJs |
| `creditos_cobrados` | int | Creditos debitados |
| `tab_id` | varchar(36) | UUID para SSE |
| `resultado_resumo` | JSONB | Resumo do resultado |
| `report_csv_base64` | text | CSV em base64 |
| `error_code`, `error_message` | | Detalhes de erro |
| `processado_em` | timestamp | Data conclusao |

### raf_lote_participantes (pivot)
| Campo | Descricao |
|-------|-----------|
| `raf_lote_id` | FK para raf_lotes |
| `participante_id` | FK para participantes |

### participante_scores
| Campo | Tipo | Descricao |
|-------|------|-----------|
| `participante_id` | FK | Link para participante (unique) |
| `user_id` | FK | Owner |
| `score_cadastral` | smallint | Score situacao cadastral (0-100) |
| `score_cnd_federal` | smallint | Score CND Federal (0-100) |
| `score_cnd_estadual` | smallint | Score CND Estadual (0-100) |
| `score_fgts` | smallint | Score FGTS/CRF (0-100) |
| `score_trabalhista` | smallint | Score CNDT (0-100) |
| `score_compliance` | smallint | Score CEIS/CNEP/TCU (0-100) |
| `score_esg` | smallint | Score ESG (0-100) |
| `score_protestos` | smallint | Score protestos (0-100) |
| `score_total` | smallint | Score ponderado final (0-100) |
| `classificacao` | varchar(20) | baixo, medio, alto, critico |
| `ultima_consulta_em` | timestamp | Data ultima consulta |
| `dados_consultados` | JSONB | Dados brutos da consulta |

### raf_*
- `raf_consultas_pendentes` - Pending RAF analyses
- `raf_relatorios_processados` - Completed reports

## Webhooks

All URLs via `config('services.webhook.*')`, NO defaults in code.

| Operation | Env Variable | Endpoint n8n |
|-----------|--------------|--------------|
| **Consultas Lotes** | `WEBHOOK_CONSULTAS_LOTES_URL` | `/webhook/consultas/lotes` |
| Monitoramento SPED | `WEBHOOK_MONITORAMENTO_IMPORTACAO_*_URL` | |
| Monitoramento XML | `WEBHOOK_MONITORAMENTO_IMPORTACAO_XML_URL` | |
| RAF Fiscal (legado) | `WEBHOOK_SPED_FISCAL_URL` | |
| RAF Contribuicoes (legado) | `WEBHOOK_SPED_CONTRIBUICOES_URL` | |

## API Endpoints

**From n8n (X-API-Token header):**
- `POST /api/data/receive/raf/csvfile` - Receive CSV reports
- `POST /api/monitoramento/consulta/resultado` - Consultation results
- `POST /api/monitoramento/sped/importacao-txt/progress` - SPED progress
- `POST /api/monitoramento/xml/importacao/progress` - XML progress

**SSE:**
- `/app/monitoramento/progresso/stream?tab_id=xxx` - SPED progress
- `/app/monitoramento/xml/progresso/stream?tab_id=xxx` - XML progress

## Progress System

Cache key: `progresso:{user_id}:{tab_id}` (TTL 10min)

```json
{
  "user_id": 1, "tab_id": "uuid",
  "progresso": 45, "status": "processando",
  "mensagem": "Processando...",
  "dados": { "total_participantes": 35 }
}
```

**Status:** `iniciando` -> `processando` -> `concluido` | `erro`

---

## n8n Workflows

### Regra de Ouro: Templates JSON no n8n

| Tipo do valor | Formato no JSON | Exemplo |
|---------------|-----------------|---------|
| Numero | `{{ $json.campo }}` | `"progresso": {{ $json.progresso }}` |
| String | `"{{ $json.campo }}"` | `"tab_id": "{{ $json.tab_id }}"` |
| Boolean | `{{ $json.campo }}` | `"ativo": {{ $json.ativo }}` |
| Array | `{{ JSON.stringify($json.campo) }}` | `"ids": {{ JSON.stringify($json.ids) }}` |
| Object | `{{ JSON.stringify($json.campo) }}` | `"dados": {{ JSON.stringify($json.dados) }}` |
| Null | `null` | `"erro": null` |

### Workflow XML (NF-e/NFS-e/CT-e)

**Fluxo:**
```
WEBHOOK (ZIP) -> EXTRAIR -> LOOP XMLs -> INSERT participantes/notas -> UPDATE importacoes_xml -> PROGRESSO 100%
```

**Webhook campos recebidos:**
| Campo | Tipo | Descricao |
|-------|------|-----------|
| `user_id` | int | ID do usuario |
| `importacao_id` | int | ID em importacoes_xml |
| `tab_id` | string | UUID da aba (SSE) |
| `tipo_documento` | string | `NFE`, `NFSE` ou `CTE` |
| `cliente_id` | int/null | ID do cliente |
| `cliente_cnpj` | string/null | CNPJ para comparacao |
| `salvar_movimentacoes` | bool | Salvar em notas_fiscais? |
| `progress_url` | string | URL para progresso |
| `arquivo_url` | string | URL do ZIP |

**SQL participantes (UPSERT):**
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
    updated_at = NOW()
RETURNING id, (xmax = 0) AS is_new;
```

**Mapeamento XML -> DB (participantes):**
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

**Mapeamento XML -> DB (notas_fiscais):**
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

### Workflow SPED (EFD Fiscal/Contribuicoes)

**Fluxo:**
```
WEBHOOK (TXT) -> PARSE SPED -> EXTRAIR CNPJs -> INSERT participantes -> PROGRESSO 100%
```

**origem_tipo valores:**
- `SPED_EFD_FISCAL` - Arquivo SPED EFD Fiscal
- `SPED_EFD_CONTRIB` - Arquivo SPED EFD Contribuicoes

**Exemplo origem_ref:**
```json
{"arquivo": "SPED_EFD_2024.txt", "importado_em": "2026-01-18T10:30:00Z"}
```

### API de Progresso (Compartilhada)

**Endpoints:**
| Workflow | Endpoint |
|----------|----------|
| XML | `POST /api/monitoramento/xml/importacao/progress` |
| SPED | `POST /api/monitoramento/sped/importacao-txt/progress` |

**Header:** `X-API-Token: {API_TOKEN}`

**Payload campos:**
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

**Codigos de Erro:**
| error_code | Descricao |
|------------|-----------|
| `PARSE_ERROR` | XML/SPED mal formado |
| `INVALID_XML` | Tipo desconhecido |
| `INVALID_SPED` | Arquivo invalido |
| `DB_ERROR` | Erro ao salvar |
| `INFOSIMPLES_TIMEOUT` | Timeout API |
| `INFOSIMPLES_ERROR` | Erro API |
| `NO_PARTICIPANTS` | Nenhum participante |
| `UNKNOWN_ERROR` | Erro desconhecido |

---

## Planos e APIs

### Sistema de Creditos

- 1 credito = R$ 0,26 (custo InfoSimples)
- Consultas Minha Receita = GRATIS
- Creditos sao INTEGERS no sistema

```php
$this->creditService->hasEnough($user, $amount);
$this->creditService->deduct($user, $amount);
$this->creditService->refund($user, $amount);
```

### Consultas Gratuitas (Minha Receita)

| Consulta | Dados Retornados |
|----------|------------------|
| Situacao Cadastral | Ativa/baixada/inapta, data, motivo |
| Dados Cadastrais | Razao social, nome fantasia, capital social |
| Endereco | Logradouro, municipio, UF, CEP |
| CNAEs | Principal e secundarios |
| QSA | Socios com CPF/CNPJ, qualificacao |
| Simples Nacional | Optante sim/nao, data opcao |
| MEI | E MEI sim/nao |

### Consultas Pagas (InfoSimples)

| Consulta | Endpoint | Custo |
|----------|----------|-------|
| SINTEGRA | `/api/v2/consultas/sintegra/unificada` | 1 cr |
| TCU Consolidada | `/api/v2/consultas/tcu/consulta-consolidada-pj` | 1 cr |
| CND Federal | `/api/v2/consultas/receita-federal/pgfn-nova` | 1 cr |
| CRF (FGTS) | `/api/v2/consultas/caixa/regularidade` | 1 cr |
| CND Estadual | `/api/v2/consultas/sefaz/certidao-debitos` | 1 cr |
| CNDT | `/api/v2/consultas/tst/cndt` | 1 cr |
| Lista Devedores PGFN | `/api/v2/consultas/pgfn/lista-devedores` | 1 cr |
| Trabalho Escravo | `/api/v2/consultas/sit/trabalho-escravo` | 1 cr |
| IBAMA Autuacoes | `/api/v2/consultas/ibama/autuacoes` | 1 cr |
| Protestos | `/api/v2/consultas/ieptb/protestos` | 1 cr |
| Processos CNJ | `/api/v2/consultas/cnj/seeu-processos` | 1 cr |

**Base URL:** `https://api.infosimples.com`

### Planos de Monitoramento

| Codigo | Nome | Creditos | Consultas Incluidas |
|--------|------|----------|---------------------|
| `gratuito` | Gratuito | 0 | situacao_cadastral, dados_cadastrais, endereco, cnaes, qsa, simples_nacional, mei |
| `validacao` | Validacao | 4 | Gratuito + sintegra, tcu_consolidada |
| `licitacao` | Licitacao | 10 | Validacao + cnd_federal, crf_fgts, cnd_estadual, cndt |
| `compliance` | Compliance | 14 | Licitacao + protestos, lista_devedores_pgfn |
| `due_diligence` | Due Diligence | 18 | Compliance + trabalho_escravo, ibama_autuacoes |
| `enterprise` | Enterprise | 20 | Due Diligence + processos_cnj |

**Personas por plano:**
- **Gratuito**: Validar se CNPJ existe e esta ativo
- **Validacao**: Contador PME validando IE e listas restritivas
- **Licitacao**: Empresas em licitacoes publicas (CNDs obrigatorias)
- **Compliance**: Gestao de terceiros com protestos e divida ativa
- **Due Diligence**: M&A e investidores com analise ESG
- **Enterprise**: Corporativo com due diligence juridico completo

**Frequencias:** Diaria, Semanal, Quinzenal, Mensal

**Exemplo:** 30 CNPJs, perfil Licitacao, semanal = 300 cr/ciclo = 1.200 cr/mes

### Alertas do Monitoramento

| Evento | Criticidade |
|--------|-------------|
| Empresa baixada/inapta | Alta |
| IE baixada/suspensa | Alta |
| Entrou CEIS/CNEP | Alta |
| Lista trabalho escravo | Alta |
| CND venceu/positiva | Media |
| CRF irregular | Media |
| Desenquadrou Simples | Media |
| Nova autuacao IBAMA | Baixa |

---

## Services

| Service | Responsibility |
|---------|----------------|
| `SpedUploadService` | Sends SPED to n8n, manages credits |
| `CreditService` | Credit operations with transaction locking |
| `CsvParserService` | Parses CSV from n8n responses |
| `RegimeTributarioService` | Tax regime lookups |
| `RiskScoreService` | Calcula e persiste scores de risco de participantes |
| `AnalyticsService` | Agregacoes para BI Fiscal (faturamento, compras, tributos) |
| `ValidacaoContabilService` | Validacao contabil de notas fiscais (VCI) |

## Frontend

**No frameworks** - Vanilla JS only.

- SPA router: `resources/js/spa.js` (intercepts `[data-link]`)
- Page scripts: `public/js/{page}.js` with `window.init{Page}()`
- SSE: `EventSource` with cache-based updates

## n8n Automation

Subscription execution runs 100% in n8n cron (hourly). Laravel has NO scheduler for subscriptions.

**Code Node reference:** `docs/Processar XML/n8n-code-node-resumo.js`

## Environment

```env
DB_CONNECTION=pgsql
APP_KEY=base64:...
API_TOKEN=your-secure-token
WEBHOOK_SPED_*_URL=https://...
WEBHOOK_MONITORAMENTO_*_URL=https://...
```

## Troubleshooting

**404 em rotas API:** Imagem Docker desatualizada -> `docker service update --image ...`

**Upload SPED nao envia:** Verificar logs `grep -i "SpedUpload" storage/logs/laravel.log | tail -20`

**Erro 404 no endpoint de progresso:**
```bash
docker compose -f docker-compose.dev.yml pull
docker compose -f docker-compose.dev.yml up -d
docker compose exec app php artisan route:clear
```

**Erro "JSON parameter needs to be valid JSON":**
Use `JSON.stringify()` em arrays no n8n:
```json
"participante_ids": {{ JSON.stringify($json.ids || []) }}
```

## Arquivos Relacionados

| Arquivo | Funcao |
|---------|--------|
| `docs/Processar XML/n8n-code-node-resumo.js` | Code Node n8n |
| `app/Http/Controllers/Api/DataReceiverController.php` | Recebe progresso |
| `app/Http/Controllers/Dashboard/MonitoramentoController.php` | SSE SPED |
| `app/Http/Controllers/Dashboard/XmlImportacaoController.php` | SSE XML |
| `app/Http/Controllers/Dashboard/ValidacaoController.php` | Validacao Contabil |
| `app/Services/ValidacaoContabilService.php` | Regras de validacao |
| `routes/api.php` | Rotas API |
