# InfoSimples - Endpoints e Testes CURL

## 🔑 Credenciais

```bash
TOKEN="hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn"
```

---

## 🧪 Endpoints por Plano

### 📍 Plano BÁSICO
**Custo:** R$ 0,00 (só Minha Receita)

#### Minha Receita (GRÁTIS)
```bash
curl -X GET "https://minhareceita.org/33683111000280"
```

**Resposta:**
```json
{
  "cnpj": "33683111000280",
  "razao_social": "OPEN KNOWLEDGE BRASIL",
  "nome_fantasia": "REDE PELO CONHECIMENTO LIVRE",
  "situacao_cadastral": 2,
  "descricao_situacao_cadastral": "Ativa",
  "opcao_pelo_simples": false,
  "opcao_pelo_mei": false,
  "uf": "SP",
  "qsa": [...]
}
```

---

### 📍 Plano CADASTRAL+
**Custo:** R$ 0,26 (1 API paga)

#### 1. Minha Receita (GRÁTIS)
```bash
curl -X GET "https://minhareceita.org/33683111000280"
```

#### 2. SINTEGRA (R$ 0,26)
```bash
curl -X GET "https://api.infosimples.com/api/v2/consultas/sintegra/unificada" \
  -d "cnpj=33683111000280" \
  -d "uf=SP" \
  -d "token=hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn"
```

**Resposta:**
```json
{
  "code": 200,
  "code_message": "OK",
  "data": {
    "inscricao_estadual": "147469114115",
    "razao_social": "OPEN KNOWLEDGE BRASIL",
    "situacao": "HABILITADO",
    "uf": "SP"
  }
}
```

---

### 📍 Plano FISCAL FEDERAL
**Custo:** R$ 0,52 (2 APIs pagas)

#### 1. Minha Receita (GRÁTIS)
```bash
curl -X GET "https://minhareceita.org/33683111000280"
```

#### 2. CND Federal - PGFN (R$ 0,26)
```bash
curl -X GET "https://api.infosimples.com/api/v2/consultas/receita-federal/pgfn-nova" \
  -d "cnpj=33683111000280" \
  -d "token=hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn"
```

**Resposta:**
```json
{
  "code": 200,
  "data": {
    "situacao": "REGULAR",
    "validade": "2026-07-17",
    "numero_certidao": "ABC123456",
    "data_emissao": "2026-01-17"
  }
}
```

#### 3. FGTS - Regularidade (R$ 0,26)
```bash
curl -X GET "https://api.infosimples.com/api/v2/consultas/caixa/regularidade" \
  -d "cnpj=33683111000280" \
  -d "token=hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn"
```

**Resposta:**
```json
{
  "code": 200,
  "data": {
    "situacao": "REGULAR",
    "validade": "2026-06-20",
    "numero_crf": "XYZ789012"
  }
}
```

---

### 📍 Plano FISCAL COMPLETO
**Custo:** R$ 1,04 (4 APIs pagas)

#### 1. Minha Receita (GRÁTIS)
```bash
curl -X GET "https://minhareceita.org/33683111000280"
```

#### 2. CND Federal (R$ 0,26)
```bash
curl -X GET "https://api.infosimples.com/api/v2/consultas/receita-federal/pgfn-nova" \
  -d "cnpj=33683111000280" \
  -d "token=hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn"
```

#### 3. FGTS (R$ 0,26)
```bash
curl -X GET "https://api.infosimples.com/api/v2/consultas/caixa/regularidade" \
  -d "cnpj=33683111000280" \
  -d "token=hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn"
```

#### 4. CND Estadual - SEFAZ (R$ 0,26)
```bash
curl -X GET "https://api.infosimples.com/api/v2/consultas/sefaz/certidao-debitos" \
  -d "cnpj=33683111000280" \
  -d "uf=SP" \
  -d "token=hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn"
```

**Resposta:**
```json
{
  "code": 200,
  "data": {
    "situacao": "REGULAR",
    "validade": "2026-05-15",
    "numero_certidao": "SP123456",
    "uf": "SP"
  }
}
```

#### 5. CNDT - Trabalhista (R$ 0,26)
```bash
curl -X GET "https://api.infosimples.com/api/v2/consultas/tst/cndt" \
  -d "cnpj=33683111000280" \
  -d "token=hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn"
```

**Resposta:**
```json
{
  "code": 200,
  "data": {
    "situacao": "REGULAR",
    "validade": "2026-07-17",
    "numero_certidao": "CNDT987654"
  }
}
```

---

### 📍 Plano DUE DILIGENCE
**Custo:** R$ 1,56 (6 APIs pagas)

#### 1. Minha Receita (GRÁTIS)
```bash
curl -X GET "https://minhareceita.org/33683111000280"
```

#### 2. CND Federal (R$ 0,26)
```bash
curl -X GET "https://api.infosimples.com/api/v2/consultas/receita-federal/pgfn-nova" \
  -d "cnpj=33683111000280" \
  -d "token=hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn"
```

#### 3. FGTS (R$ 0,26)
```bash
curl -X GET "https://api.infosimples.com/api/v2/consultas/caixa/regularidade" \
  -d "cnpj=33683111000280" \
  -d "token=hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn"
```

#### 4. CND Estadual (R$ 0,26)
```bash
curl -X GET "https://api.infosimples.com/api/v2/consultas/sefaz/certidao-debitos" \
  -d "cnpj=33683111000280" \
  -d "uf=SP" \
  -d "token=hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn"
```

#### 5. CNDT (R$ 0,26)
```bash
curl -X GET "https://api.infosimples.com/api/v2/consultas/tst/cndt" \
  -d "cnpj=33683111000280" \
  -d "token=hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn"
```

#### 6. Protestos - IEPTB (R$ 0,26)
```bash
curl -X GET "https://api.infosimples.com/api/v2/consultas/ieptb/protestos" \
  -d "cnpj=33683111000280" \
  -d "token=hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn"
```

**Resposta:**
```json
{
  "code": 200,
  "data": {
    "total_protestos": 0,
    "protestos": []
  }
}
```

#### 7. Processos - CNJ (R$ 0,26)
```bash
curl -X GET "https://api.infosimples.com/api/v2/consultas/cnj/seeu-processos" \
  -d "cnpj=33683111000280" \
  -d "token=hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn"
```

**Resposta:**
```json
{
  "code": 200,
  "data": {
    "total_processos": 0,
    "processos": []
  }
}
```

---

## 🔧 Configuração no n8n

### HTTP Request Node - Template

```
Method: GET
URL: [endpoint da API]

Authentication: None

Body:
  Type: Form-Data

  Fields:
    - cnpj: {{ $node['Adicionar Consulta ID'].json.cnpj }}
    - uf: {{ $node['Adicionar Consulta ID'].json.uf }}  (se necessário)
    - token: hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn

Options:
  - Response Format: JSON
  - Timeout: 30000
  - Continue On Fail: true
```

---

## 📋 Tabela Resumo - URLs por Endpoint

| API | URL | Método | Params | Custo |
|-----|-----|--------|--------|-------|
| Minha Receita | `https://minhareceita.org/{cnpj}` | GET | cnpj | R$ 0,00 |
| SINTEGRA | `https://api.infosimples.com/api/v2/consultas/sintegra/unificada` | GET | cnpj, uf, token | R$ 0,26 |
| CND Federal | `https://api.infosimples.com/api/v2/consultas/receita-federal/pgfn-nova` | GET | cnpj, token | R$ 0,26 |
| FGTS | `https://api.infosimples.com/api/v2/consultas/caixa/regularidade` | GET | cnpj, token | R$ 0,26 |
| CND Estadual | `https://api.infosimples.com/api/v2/consultas/sefaz/certidao-debitos` | GET | cnpj, uf, token | R$ 0,26 |
| CNDT | `https://api.infosimples.com/api/v2/consultas/tst/cndt` | GET | cnpj, token | R$ 0,26 |
| Protestos | `https://api.infosimples.com/api/v2/consultas/ieptb/protestos` | GET | cnpj, token | R$ 0,26 |
| Processos | `https://api.infosimples.com/api/v2/consultas/cnj/seeu-processos` | GET | cnpj, token | R$ 0,26 |

---

## 🧪 Script de Teste Completo

Salve como `test-infosimples.sh`:

```bash
#!/bin/bash

TOKEN="hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn"
CNPJ="33683111000280"
UF="SP"

echo "========================================="
echo "Testando APIs InfoSimples"
echo "========================================="

echo ""
echo "1. Minha Receita (GRÁTIS)"
curl -s "https://minhareceita.org/$CNPJ" | jq -r '.razao_social'

echo ""
echo "2. SINTEGRA (R$ 0,26)"
curl -s "https://api.infosimples.com/api/v2/consultas/sintegra/unificada" \
  -d "cnpj=$CNPJ" \
  -d "uf=$UF" \
  -d "token=$TOKEN" | jq -r '.data.situacao'

echo ""
echo "3. CND Federal (R$ 0,26)"
curl -s "https://api.infosimples.com/api/v2/consultas/receita-federal/pgfn-nova" \
  -d "cnpj=$CNPJ" \
  -d "token=$TOKEN" | jq -r '.data.situacao'

echo ""
echo "4. FGTS (R$ 0,26)"
curl -s "https://api.infosimples.com/api/v2/consultas/caixa/regularidade" \
  -d "cnpj=$CNPJ" \
  -d "token=$TOKEN" | jq -r '.data.situacao'

echo ""
echo "5. CND Estadual (R$ 0,26)"
curl -s "https://api.infosimples.com/api/v2/consultas/sefaz/certidao-debitos" \
  -d "cnpj=$CNPJ" \
  -d "uf=$UF" \
  -d "token=$TOKEN" | jq -r '.data.situacao'

echo ""
echo "6. CNDT (R$ 0,26)"
curl -s "https://api.infosimples.com/api/v2/consultas/tst/cndt" \
  -d "cnpj=$CNPJ" \
  -d "token=$TOKEN" | jq -r '.data.situacao'

echo ""
echo "7. Protestos (R$ 0,26)"
curl -s "https://api.infosimples.com/api/v2/consultas/ieptb/protestos" \
  -d "cnpj=$CNPJ" \
  -d "token=$TOKEN" | jq -r '.data.total_protestos'

echo ""
echo "8. Processos (R$ 0,26)"
curl -s "https://api.infosimples.com/api/v2/consultas/cnj/seeu-processos" \
  -d "cnpj=$CNPJ" \
  -d "token=$TOKEN" | jq -r '.data.total_processos'

echo ""
echo "========================================="
echo "Custo total se rodar tudo: R$ 1,82"
echo "========================================="
```

**Executar:**
```bash
chmod +x test-infosimples.sh
./test-infosimples.sh
```

---

## ⚠️ IMPORTANTE - Custos

| Teste | Custo |
|-------|-------|
| Minha Receita | GRÁTIS |
| 1 API InfoSimples | R$ 0,26 |
| Plano Cadastral+ (1 API) | R$ 0,26 |
| Plano Fiscal Federal (2 APIs) | R$ 0,52 |
| Plano Fiscal Completo (4 APIs) | R$ 1,04 |
| Plano Due Diligence (6 APIs) | R$ 1,56 |
| **TESTAR TUDO (7 APIs)** | **R$ 1,82** |

**⚠️ Cuidado:** Cada curl acima COBRA R$ 0,26 (exceto Minha Receita)

**Recomendação para testes:**
1. Teste só Minha Receita primeiro (grátis)
2. Depois teste 1 API InfoSimples (R$ 0,26)
3. Só rode tudo quando tiver certeza

---

## 🔐 Armazenar Token no n8n

### Opção 1: Credential (RECOMENDADO)

1. n8n → Credentials → Add Credential
2. Type: "Header Auth"
3. Name: "InfoSimples"
4. Header Name: `token`
5. Header Value: `hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn`

**Usar no HTTP Node:**
```
Authentication: Header Auth
Credential: InfoSimples
```

### Opção 2: Direct (SIMPLES)

No HTTP Node, adicione nos Query Parameters:
```
token: hS0Vu9ksh_gVyUbaVu0iAHZP2U4Hfzty3y_1MZTn
```

---

## 📱 Testar no Navegador

### Minha Receita (GRÁTIS)
Abra no navegador:
```
https://minhareceita.org/33683111000280
```

### InfoSimples (NÃO DÁ)
❌ Não funciona no navegador (precisa POST)
✅ Use curl ou Postman

---

## 🎯 CNPJs para Teste

| Empresa | CNPJ | UF | Situação |
|---------|------|-----|----------|
| Open Knowledge Brasil | 33683111000280 | SP | Ativa |
| Petrobras | 33000167000101 | RJ | Ativa |
| Banco do Brasil | 00000000000191 | DF | Ativa |

---

## 📚 Documentação Oficial

- **Minha Receita:** https://docs.minhareceita.org
- **InfoSimples:** https://www.infosimples.com/api/docs (precisa login)

---

## ✅ Checklist de Teste

Antes de usar no n8n, teste manualmente:

- [ ] Minha Receita funciona? (grátis)
- [ ] InfoSimples retorna 200? (R$ 0,26)
- [ ] Token está correto?
- [ ] CNPJ está formatado certo? (sem pontos/traços)
- [ ] UF está certo quando necessário?

Depois de testar manualmente → Configure no n8n! 🚀
