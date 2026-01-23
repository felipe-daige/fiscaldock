# FiscalDock - Especificação de Planos de Consulta

## Sistema de Créditos

- 1 consulta InfoSimples = 1 crédito consumido
- Custo real por crédito: R$ 0,26 (API InfoSimples)
- Consultas gratuitas não consomem créditos

---

## Consultas Gratuitas (0 créditos)

| Consulta | Descrição |
|----------|-----------|
| Situação Cadastral | Situação ativa/baixada/inapta, data e motivo |
| Dados Cadastrais | Razão social, nome fantasia, data abertura, capital social, natureza jurídica, porte |
| Endereço | Logradouro, número, complemento, bairro, município, UF, CEP |
| CNAEs | CNAE principal e secundários |
| Quadro Societário (QSA) | Lista de sócios com nome, CPF/CNPJ, qualificação |
| Simples Nacional | Optante sim/não, data opção, períodos anteriores |
| MEI | É MEI sim/não, dados do certificado |

---

## Consultas Pagas (1 crédito cada)

| Consulta | API InfoSimples | O que retorna |
|----------|-----------------|---------------|
| SINTEGRA | SINTEGRA / Unificada | Situação IE, regime ICMS, todos os estados |
| TCU Consolidada | TCU / Consulta Consolidada PJ | CEIS + CNEP + CNJ + TCU em uma chamada |
| CND Federal | Receita Federal / PGFN | Certidão de débitos federais + PDF |
| CRF (FGTS) | Caixa / Regularidade do Empregador | Certidão de regularidade FGTS + PDF |
| CND Estadual | SEFAZ / Certidão Negativa Débitos Estaduais | Certidão de débitos estaduais + PDF |
| CNDT | TST / CNDT | Certidão de débitos trabalhistas + PDF |
| Lista Devedores PGFN | PGFN / Lista de Devedores | Valor detalhado da dívida federal |
| Trabalho Escravo | SIT / Trabalho Escravo | Consulta na "lista suja" |
| IBAMA Autuações | IBAMA / Autuações Ambientais | Infrações ambientais |

---

## Planos

### Básico
- **Créditos:** 0 (gratuito)
- **Descrição:** Verificação rápida de situação cadastral e regime tributário
- **Consultas:**
  - Situação Cadastral
  - Dados Cadastrais
  - Endereço
  - CNAEs
  - Quadro Societário (QSA)
  - Simples Nacional
  - MEI

---

### Cadastral+
- **Créditos:** 3
- **Descrição:** Dados completos do CNPJ com SINTEGRA e verificação de listas restritivas
- **Consultas:**
  - Tudo do Básico +
  - SINTEGRA
  - TCU Consolidada
- **APIs InfoSimples:** 2
- **Valor agregado:** Valida situação da IE em todos os estados e verifica se a empresa está impedida de licitar (CEIS, CNEP, CNJ, TCU)

---

### Fiscal Federal
- **Créditos:** 6
- **Descrição:** CND Federal (PGFN) e regularidade FGTS
- **Consultas:**
  - Tudo do Cadastral+ +
  - CND Federal
  - CRF (FGTS)
- **APIs InfoSimples:** 4
- **Valor agregado:** Certidões de regularidade fiscal federal e FGTS, essenciais para licitações

---

### Fiscal Completo
- **Créditos:** 12
- **Descrição:** CNDs Federal, Estadual e Trabalhista
- **Consultas:**
  - Tudo do Fiscal Federal +
  - CND Estadual
  - CNDT
- **APIs InfoSimples:** 6
- **Valor agregado:** Cobertura completa de certidões nas três esferas (federal, estadual, trabalhista)

---

### Due Diligence
- **Créditos:** 16
- **Descrição:** Análise completa com detalhamento de dívida federal
- **Consultas:**
  - Tudo do Fiscal Completo +
  - Lista Devedores PGFN
- **APIs InfoSimples:** 7
- **Valor agregado:** Mostra o valor detalhado da dívida federal, não apenas se tem ou não débitos

---

### ESG
- **Créditos:** 6
- **Descrição:** Verificação de compliance ambiental e trabalhista
- **Consultas:**
  - Trabalho Escravo
  - IBAMA Autuações
- **APIs InfoSimples:** 2
- **Valor agregado:** Identifica fornecedores na "lista suja" e com infrações ambientais
- **Observação:** Plano independente, pode ser combinado com outros

---

### Completo
- **Créditos:** 22
- **Descrição:** Todas as consultas disponíveis
- **Consultas:**
  - Tudo do Due Diligence +
  - Trabalho Escravo
  - IBAMA Autuações
- **APIs InfoSimples:** 9
- **Valor agregado:** Relatório completo com todos os dados disponíveis

---

## Resumo dos Planos

| Plano | Créditos | APIs Pagas | Inclui |
|-------|----------|------------|--------|
| Básico | 0 | 0 | Dados gratuitos |
| Cadastral+ | 3 | 2 | Básico + SINTEGRA + TCU |
| Fiscal Federal | 6 | 4 | Cadastral+ + CND Federal + FGTS |
| Fiscal Completo | 12 | 6 | Fiscal Federal + CND Estadual + CNDT |
| Due Diligence | 16 | 7 | Fiscal Completo + Lista Devedores |
| ESG | 6 | 2 | Trabalho Escravo + IBAMA |
| Completo | 22 | 9 | Tudo |

---

## Monitoramento de CNPJs

O monitoramento executa planos automaticamente em intervalos definidos pelo usuário.

### Perfis de Monitoramento

| Perfil | Plano Executado | Créditos por CNPJ por Ciclo |
|--------|-----------------|----------------------------|
| Básico | Cadastral+ | 3 |
| Fiscal | Fiscal Completo | 12 |
| Completo | Completo | 22 |

### Frequências Disponíveis

- Diária
- Semanal
- Quinzenal
- Mensal

### Exemplo de Consumo

Monitorar 30 CNPJs, perfil Fiscal, frequência semanal:
- Por ciclo: 30 × 12 = 360 créditos
- Por mês (4 ciclos): 360 × 4 = 1.440 créditos

---

## Alertas do Monitoramento

O sistema deve alertar o usuário quando detectar mudanças:

| Evento | Criticidade |
|--------|-------------|
| Empresa baixada/inapta na Receita | Alta |
| IE baixada/suspensa/inapta (SINTEGRA) | Alta |
| Entrou no CEIS/CNEP (impedida de licitar) | Alta |
| Entrou na lista de trabalho escravo | Alta |
| CND Federal venceu ou virou positiva | Média |
| CND Estadual venceu ou virou positiva | Média |
| CNDT com débitos | Média |
| CRF (FGTS) irregular | Média |
| Desenquadrou do Simples Nacional | Média |
| Nova autuação IBAMA | Baixa |
