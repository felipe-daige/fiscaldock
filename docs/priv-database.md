# Banco de Dados Privado (priv_*)

## Objetivo

Criar um banco de dados próprio para monitoramento independente de **pessoas físicas (CPFs)** e suas atividades fiscais, extraídas dos arquivos SPED processados pelo sistema.

A ideia é acumular dados ao longo do tempo para permitir análises e monitoramento sem depender de APIs externas.

---

## Estrutura das Tabelas

### 1. `priv_docs` - Documentos SPED

Armazena os arquivos SPED originais processados.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint | PK |
| `user_id` | bigint | Usuário que fez upload |
| `cliente_id` | bigint | Cliente associado |
| `document_type` | string | Tipo do documento (EFD Fiscal, EFD Contribuições, etc.) |
| `final_report_base64` | text | Relatório final em base64 |
| `document_text` | longtext | Texto extraído do SPED |

---

### 2. `priv_cpf_cadastro` - Cadastro de CPFs

Cadastro único de pessoas físicas identificadas nos SPEDs.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint | PK |
| `user_id` | bigint | Usuário que importou |
| `cliente_id` | bigint | Cliente associado |
| `cpf` | char(11) | CPF (único) |
| `nome` | varchar(255) | Nome completo |
| `cod_pais` | char(5) | Código do país (default: 1058 = Brasil) |
| `uf` | char(2) | Estado |
| `codigo_municipal` | char(7) | Código IBGE do município |
| `municipio_nome` | varchar(100) | Nome do município |
| `cep` | char(8) | CEP |
| `bairro` | varchar(60) | Bairro |
| `endereco` | varchar(255) | Logradouro |
| `numero` | varchar(10) | Número |
| `complemento` | varchar(60) | Complemento |
| `inscricao_estadual` | varchar(20) | IE (se produtor rural) |
| `suframa` | varchar(9) | Inscrição SUFRAMA |

**Índices:** `cpf` (unique), `uf`, `codigo_municipal`, `nome`

---

### 3. `priv_cpf_operacoes` - Operações Fiscais

Cada nota fiscal ou documento onde o CPF aparece como participante.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint | PK |
| `cpf_id` | bigint | FK → priv_cpf_cadastro |
| `cliente_id` | bigint | Cliente associado |
| `cnpj_empresa` | char(14) | CNPJ da empresa emitente |
| `tipo_participacao` | varchar(20) | `CLIENTE`, `FORNECEDOR`, `TRANSPORTADOR`, etc. |
| `chave_acesso` | char(44) | Chave de acesso NFe/NFCe (única quando não nula) |
| `modelo` | char(2) | Modelo do documento (55=NFe, 65=NFCe, etc.) |
| `serie` | char(3) | Série |
| `numero_doc` | varchar(20) | Número do documento |
| `data_emissao` | date | Data de emissão |
| `data_operacao` | date | Data da operação (entrada/saída) |
| `tipo_operacao` | char(1) | `0`=Entrada, `1`=Saída |
| `valor_total` | decimal(15,2) | Valor total da nota |
| `valor_mercadorias` | decimal(15,2) | Valor das mercadorias |
| `valor_frete` | decimal(15,2) | Valor do frete |
| `valor_desconto` | decimal(15,2) | Valor do desconto |
| `uf_origem` | char(2) | UF de origem |
| `uf_destino` | char(2) | UF de destino |
| `ncm_principal` | char(8) | NCM principal da operação |
| `descricao_resumo` | varchar(255) | Descrição resumida dos itens |
| `arquivo_origem` | varchar(255) | Nome do arquivo SPED de origem |

**Índices:** `cpf_id`, `cnpj_empresa`, `data_emissao`, `chave_acesso` (unique parcial)

---

### 4. `priv_cpf_relacionamentos` - Relacionamentos CPF ↔ CNPJ

Visão agregada dos relacionamentos entre CPFs e empresas.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint | PK |
| `cpf_id` | bigint | FK → priv_cpf_cadastro |
| `cliente_id` | bigint | Cliente associado |
| `cnpj` | char(14) | CNPJ da empresa |
| `razao_social` | varchar(255) | Razão social da empresa |
| `tipo_relacao` | varchar(20) | `CLIENTE`, `FORNECEDOR`, etc. |
| `total_operacoes` | int | Quantidade de operações |
| `valor_total` | decimal(18,2) | Soma dos valores |
| `primeira_operacao` | date | Data da primeira operação |
| `ultima_operacao` | date | Data da última operação |

**Constraint única:** `cpf_id` + `cnpj` + `tipo_relacao`

---

## Diagrama de Relacionamento

```
┌─────────────────┐
│   priv_docs     │ ← Arquivos SPED originais
│   (documentos)  │
└─────────────────┘
        │
        │ extração
        ▼
┌─────────────────────┐
│  priv_cpf_cadastro  │ ← CPF único com dados cadastrais
│      (1 CPF)        │
└─────────────────────┘
        │
        ├────────────────────────────┐
        │                            │
        ▼                            ▼
┌─────────────────────┐    ┌─────────────────────────┐
│  priv_cpf_operacoes │    │ priv_cpf_relacionamentos│
│   (N operações)     │    │    (N empresas)         │
│                     │    │                         │
│ - Cada nota fiscal  │    │ - Visão agregada        │
│ - Valores, datas    │    │ - Total operações       │
│ - Chave de acesso   │    │ - Valor acumulado       │
└─────────────────────┘    └─────────────────────────┘
```

---

## Casos de Uso para Monitoramento

### 1. Identificar padrões de compra/venda de um CPF
```sql
SELECT
    c.cpf, c.nome,
    o.cnpj_empresa, o.tipo_participacao,
    COUNT(*) as qtd_operacoes,
    SUM(o.valor_total) as valor_total
FROM priv_cpf_cadastro c
JOIN priv_cpf_operacoes o ON o.cpf_id = c.id
WHERE c.cpf = '12345678901'
GROUP BY c.cpf, c.nome, o.cnpj_empresa, o.tipo_participacao;
```

### 2. Listar todas as empresas que um CPF se relaciona
```sql
SELECT
    c.cpf, c.nome,
    r.cnpj, r.razao_social, r.tipo_relacao,
    r.total_operacoes, r.valor_total,
    r.primeira_operacao, r.ultima_operacao
FROM priv_cpf_cadastro c
JOIN priv_cpf_relacionamentos r ON r.cpf_id = c.id
WHERE c.cpf = '12345678901'
ORDER BY r.valor_total DESC;
```

### 3. Detectar CPFs com alto volume de operações
```sql
SELECT
    c.cpf, c.nome, c.uf,
    COUNT(DISTINCT o.cnpj_empresa) as empresas_diferentes,
    COUNT(*) as total_operacoes,
    SUM(o.valor_total) as valor_movimentado
FROM priv_cpf_cadastro c
JOIN priv_cpf_operacoes o ON o.cpf_id = c.id
WHERE o.data_emissao >= CURRENT_DATE - INTERVAL '12 months'
GROUP BY c.id, c.cpf, c.nome, c.uf
HAVING COUNT(*) > 100
ORDER BY valor_movimentado DESC;
```

### 4. Histórico de operações por período
```sql
SELECT
    DATE_TRUNC('month', o.data_emissao) as mes,
    COUNT(*) as operacoes,
    SUM(o.valor_total) as valor
FROM priv_cpf_operacoes o
JOIN priv_cpf_cadastro c ON c.id = o.cpf_id
WHERE c.cpf = '12345678901'
GROUP BY DATE_TRUNC('month', o.data_emissao)
ORDER BY mes;
```

---

## Fluxo de Alimentação

1. **Upload do SPED** → Armazenado em `priv_docs`
2. **Parsing do SPED** (n8n) → Extrai participantes CPF
3. **Cadastro do CPF** → Insere/atualiza `priv_cpf_cadastro`
4. **Registro de operações** → Insere em `priv_cpf_operacoes`
5. **Atualização de relacionamentos** → Upsert em `priv_cpf_relacionamentos`

---

## Considerações de Privacidade

Estas tabelas contêm **dados sensíveis** (CPFs, nomes, endereços). Garantir:

- Acesso restrito por `user_id` e `cliente_id`
- Políticas de retenção de dados
- Criptografia em repouso (se necessário)
- Logs de acesso para auditoria

---

## Próximos Passos

- [ ] Implementar extração de CPFs no workflow n8n
- [ ] Criar interface de consulta no Laravel
- [ ] Adicionar filtros por período, UF, valor
- [ ] Implementar alertas para padrões suspeitos
- [ ] Dashboard com visão consolidada
