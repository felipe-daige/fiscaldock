# Planos de Consulta e APIs

## Sistema de Creditos

- 1 credito = R$ 0,26 (custo InfoSimples)
- Consultas Minha Receita = GRATIS
- Creditos sao INTEGERS no sistema

---

## Consultas Gratuitas (Minha Receita)

| Consulta | Dados Retornados |
|----------|------------------|
| Situacao Cadastral | Ativa/baixada/inapta, data, motivo |
| Dados Cadastrais | Razao social, nome fantasia, capital social |
| Endereco | Logradouro, municipio, UF, CEP |
| CNAEs | Principal e secundarios |
| QSA | Socios com CPF/CNPJ, qualificacao |
| Simples Nacional | Optante sim/nao, data opcao |
| MEI | E MEI sim/nao |

---

## Consultas Pagas (InfoSimples)

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

**Params comuns:** `cnpj`, `token`, `uf` (quando necessario)

---

## Planos

| Plano | Creditos | APIs Pagas | Inclui |
|-------|----------|------------|--------|
| Basico | 0 | 0 | Dados gratuitos (Minha Receita) |
| Cadastral+ | 3 | 2 | Basico + SINTEGRA + TCU |
| Fiscal Federal | 6 | 4 | Cadastral+ + CND Federal + FGTS |
| Fiscal Completo | 12 | 6 | Fiscal Federal + CND Estadual + CNDT |
| Due Diligence | 16 | 7 | Fiscal Completo + Lista Devedores |
| ESG | 6 | 2 | Trabalho Escravo + IBAMA |
| Completo | 22 | 9 | Tudo |

---

## Detalhes dos Planos

### Basico (0 cr)
Verificacao rapida de situacao cadastral e regime tributario.

### Cadastral+ (3 cr)
- SINTEGRA: Valida IE em todos os estados
- TCU Consolidada: CEIS + CNEP + CNJ + TCU (impedimentos licitacao)

### Fiscal Federal (6 cr)
- CND Federal (PGFN): Certidao debitos federais + PDF
- CRF (FGTS): Certidao regularidade FGTS + PDF

### Fiscal Completo (12 cr)
- CND Estadual (SEFAZ): Certidao debitos estaduais + PDF
- CNDT (TST): Certidao debitos trabalhistas + PDF

### Due Diligence (16 cr)
- Lista Devedores PGFN: Valor detalhado da divida federal

### ESG (6 cr) - Independente
- Trabalho Escravo: Lista suja
- IBAMA: Infracoes ambientais

### Completo (22 cr)
Todas as consultas disponiveis.

---

## Monitoramento de CNPJs

| Perfil | Plano Executado | Creditos/CNPJ/Ciclo |
|--------|-----------------|---------------------|
| Basico | Cadastral+ | 3 |
| Fiscal | Fiscal Completo | 12 |
| Completo | Completo | 22 |

**Frequencias:** Diaria, Semanal, Quinzenal, Mensal

**Exemplo:** 30 CNPJs, perfil Fiscal, semanal = 360 cr/ciclo = 1.440 cr/mes

---

## Alertas do Monitoramento

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

## Configuracao n8n

### HTTP Request Template

```
Method: GET
URL: https://api.infosimples.com/api/v2/consultas/...
Body Type: Form-Data
Fields:
  - cnpj: {{ $json.cnpj }}
  - uf: {{ $json.uf }}
  - token: {{ $env.INFOSIMPLES_TOKEN }}
Options:
  - Timeout: 30000
  - Continue On Fail: true
```

### Credential (Recomendado)

1. n8n > Credentials > Add > Header Auth
2. Name: `InfoSimples`
3. Header Name: `token`
4. Header Value: `{{TOKEN_AQUI}}`

---

## Teste Rapido

```bash
# Minha Receita (GRATIS)
curl -s "https://minhareceita.org/33683111000280" | jq '.razao_social'

# InfoSimples (R$ 0,26)
curl -s "https://api.infosimples.com/api/v2/consultas/sintegra/unificada" \
  -d "cnpj=33683111000280" \
  -d "uf=SP" \
  -d "token={{SEU_TOKEN}}" | jq '.data.situacao'
```

---

## CNPJs para Teste

| Empresa | CNPJ | UF |
|---------|------|-----|
| Open Knowledge Brasil | 33683111000280 | SP |
| Petrobras | 33000167000101 | RJ |
| Banco do Brasil | 00000000000191 | DF |
