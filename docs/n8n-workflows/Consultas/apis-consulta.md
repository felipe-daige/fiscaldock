# APIs de Consulta

Referência completa das APIs utilizadas para consulta de participantes.

---

## Visão Geral

| Provedor | Custo | Consultas |
|----------|-------|-----------|
| **Minha Receita** | Gratuito | Situação cadastral, dados básicos, Simples, MEI, QSA |
| **InfoSimples** | 1 crédito/consulta | SINTEGRA, CNDs, TCU, Protestos, etc. |

---

## Minha Receita (Gratuito)

### Endpoint

```
GET https://minhareceita.org/{cnpj}
```

### Headers

```
Accept: application/json
```

### Exemplo de Requisição

```bash
curl -X GET "https://minhareceita.org/12345678000190" \
  -H "Accept: application/json"
```

### Resposta

> **Nota:** A API retorna um **array JSON** mesmo para consulta de 1 CNPJ. No workflow n8n, acesse o primeiro elemento: `$json[0]` ou `$input.first().json`.

```json
[
  {
    "cnpj": "12345678000190",
    "identificador_matriz_filial": 1,
    "descricao_matriz_filial": "MATRIZ",
    "razao_social": "EMPRESA EXEMPLO LTDA",
    "nome_fantasia": "EXEMPLO",
    "situacao_cadastral": 2,
    "descricao_situacao_cadastral": "ATIVA",
    "data_situacao_cadastral": "2020-01-15",
    "motivo_situacao_cadastral": 0,
    "nome_cidade_no_exterior": null,
    "codigo_natureza_juridica": 2062,
    "natureza_juridica": "SOCIEDADE EMPRESARIA LIMITADA",
    "data_inicio_atividade": "2015-03-20",
    "cnae_fiscal": 6201500,
    "cnae_fiscal_descricao": "DESENVOLVIMENTO DE PROGRAMAS DE COMPUTADOR SOB ENCOMENDA",
    "descricao_tipo_de_logradouro": "RUA",
    "logradouro": "EXEMPLO",
    "numero": "123",
    "complemento": "SALA 1",
    "bairro": "CENTRO",
    "cep": "01000000",
    "uf": "SP",
    "codigo_municipio": 3550308,
    "municipio": "SAO PAULO",
    "ddd_telefone_1": "11999999999",
    "ddd_telefone_2": null,
    "ddd_fax": null,
    "qualificacao_do_responsavel": 49,
    "capital_social": 100000.00,
    "porte": "PEQUENO",
    "descricao_porte": "EMPRESA DE PEQUENO PORTE",
    "opcao_pelo_simples": true,
    "data_opcao_pelo_simples": "2018-01-01",
    "data_exclusao_do_simples": null,
    "opcao_pelo_mei": false,
    "situacao_especial": null,
    "data_situacao_especial": null,
    "cnaes_secundarios": [
        {
            "codigo": 6202300,
            "descricao": "DESENVOLVIMENTO E LICENCIAMENTO DE PROGRAMAS DE COMPUTADOR CUSTOMIZÁVEIS"
        },
        {
            "codigo": 6209100,
            "descricao": "SUPORTE TÉCNICO, MANUTENÇÃO E OUTROS SERVIÇOS EM TECNOLOGIA DA INFORMAÇÃO"
        }
    ],
    "qsa": [
        {
            "identificador_de_socio": 2,
            "nome_socio": "FULANO DE TAL",
            "cnpj_cpf_do_socio": "***123456**",
            "codigo_qualificacao_socio": 49,
            "qualificacao_socio": "SOCIO-ADMINISTRADOR",
            "data_entrada_sociedade": "2015-03-20",
            "codigo_pais": null,
            "pais": null,
            "cpf_representante_legal": null,
            "nome_representante_legal": null,
            "codigo_qualificacao_representante_legal": null,
            "qualificacao_representante_legal": null,
            "codigo_faixa_etaria": 4,
            "faixa_etaria": "Entre 31 a 40 anos"
        }
    ],
    "regime_tributario": [
        {
            "ano": 2025,
            "cnpj_da_scp": null,
            "forma_de_tributacao": "LUCRO PRESUMIDO",
            "quantidade_de_escrituracoes": 1
        },
        {
            "ano": 2024,
            "cnpj_da_scp": null,
            "forma_de_tributacao": "LUCRO PRESUMIDO",
            "quantidade_de_escrituracoes": 1
        },
        {
            "ano": 2023,
            "cnpj_da_scp": null,
            "forma_de_tributacao": "LUCRO REAL",
            "quantidade_de_escrituracoes": 1
        }
    ]
  }
]
```

### Campos Importantes

| Campo API | Tipo | Descrição |
|-----------|------|-----------|
| `situacao_cadastral` | `int` | Código numérico (2=ATIVA, 8=BAIXADA, etc.) |
| `descricao_situacao_cadastral` | `string` | Texto descritivo ("ATIVA", "BAIXADA", etc.) |
| `razao_social` | `string` | Razão social completa |
| `nome_fantasia` | `string\|null` | Nome fantasia (pode ser null) |
| `opcao_pelo_simples` | `bool\|null` | true/false/null - optante pelo Simples |
| `opcao_pelo_mei` | `bool\|null` | true/false/null - é MEI |
| `cnae_fiscal` | `int` | Código CNAE principal (numérico, ex: 6201500) |
| `cnae_fiscal_descricao` | `string` | Descrição do CNAE principal |
| `cnaes_secundarios` | `array` | Lista de {codigo, descricao} |
| `capital_social` | `float` | Capital social (decimal) |
| `regime_tributario` | `array` | **NOVO** - Histórico de regimes por ano |

#### Campos de Endereço (nível raiz)

| Campo API | Tipo | Descrição |
|-----------|------|-----------|
| `uf` | `string` | Sigla do estado (ex: "SP") |
| `cep` | `string` | CEP sem formatação (ex: "01000000") |
| `municipio` | `string` | Nome do município |
| `codigo_municipio` | `int` | Código IBGE do município |
| `bairro` | `string` | Bairro |
| `logradouro` | `string` | Logradouro (sem tipo) |
| `descricao_tipo_de_logradouro` | `string` | Tipo (RUA, AVENIDA, etc.) |
| `numero` | `string` | Número |
| `complemento` | `string\|null` | Complemento |

#### Campo `regime_tributario` (NOVO)

Array com histórico de regimes tributários por ano. Muito útil para validações contábeis.

> **Importante:** O array é retornado em **ordem cronológica crescente** (mais antigo primeiro). Para obter o regime atual, use o **último** elemento do array.

| Sub-campo | Tipo | Descrição |
|-----------|------|-----------|
| `ano` | `int` | Ano de referência |
| `forma_de_tributacao` | `string` | "LUCRO REAL", "LUCRO PRESUMIDO", "SIMPLES NACIONAL", etc. |
| `cnpj_da_scp` | `string\|null` | CNPJ da SCP (se aplicável) |
| `quantidade_de_escrituracoes` | `int` | Quantidade de ECFs no ano |

#### Campos de Telefone

| Campo API | Tipo | Descrição |
|-----------|------|-----------|
| `ddd_telefone_1` | `string\|null` | DDD + telefone principal |
| `ddd_telefone_2` | `string\|null` | DDD + telefone secundário |
| `ddd_fax` | `string\|null` | DDD + fax |

### Códigos de Situação Cadastral

| Código | Descrição |
|--------|-----------|
| 2 | ATIVA |
| 3 | SUSPENSA |
| 4 | INAPTA |
| 8 | BAIXADA |

### Mapeamento para Sistema FiscalDock

| Campo API | Campo Participante | Notas |
|-----------|-------------------|-------|
| `cnpj` | `cnpj` | Sem formatação |
| `razao_social` | `razao_social` | |
| `nome_fantasia` | `nome_fantasia` | |
| `uf` | `uf` | No nível raiz (não aninhado) |
| `cep` | `cep` | |
| `municipio` | `municipio` | |
| `ddd_telefone_1` | `telefone` | |
| `opcao_pelo_simples` | Para validações | Usado em VCI |
| `regime_tributario[ultimo].forma_de_tributacao` | Para validações | Regime atual (último = mais recente) |

### Code Node: Normalizar Payload Minha Receita

Normaliza a resposta da API Minha Receita para o formato FiscalDock.

```javascript
const items = [];

for (const item of $input.all()) {
  const empresa = Array.isArray(item.json) ? item.json[0] : item.json;

  if (!empresa || !empresa.cnpj) {
    items.push({
      json: {
        status: 'erro',
        error_code: 'INVALID_RESPONSE',
        error_message: 'CNPJ não encontrado na resposta da API'
      }
    });
    continue;
  }

  // Derivar CRT: 1=Simples Nacional, 3=Normal
  const crt = empresa.opcao_pelo_simples === true ? 1 : 3;

  // Regime tributário (inferido do mais recente na lista)
  const regimeMaisRecente = empresa.regime_tributario?.length > 0
    ? empresa.regime_tributario[empresa.regime_tributario.length - 1]
    : null;
  const regimeAtual = regimeMaisRecente?.forma_de_tributacao || null;
  const regimeAtualAno = regimeMaisRecente?.ano || null;

  // Formatar CNAE (de 6201500 para "6201-5/00")
  const formatarCnae = (codigo) => {
    if (!codigo) return null;
    const str = String(codigo).padStart(7, '0');
    return str.replace(/(\d{4})(\d)(\d{2})/, '$1-$2/$3');
  };

  // Dados para UPSERT em participantes
  const participante = {
    cnpj: empresa.cnpj,
    razao_social: empresa.razao_social || null,
    nome_fantasia: empresa.nome_fantasia || null,
    situacao_cadastral: empresa.descricao_situacao_cadastral || 'DESCONHECIDA',
    uf: empresa.uf || null,
    cep: empresa.cep || null,
    municipio: empresa.municipio || null,
    codigo_municipal: empresa.codigo_municipio ? String(empresa.codigo_municipio) : null,
    telefone: empresa.ddd_telefone_1 || null,
    endereco: empresa.logradouro || null,
    numero: empresa.numero || null,
    complemento: empresa.complemento || null,
    bairro: empresa.bairro || null,
    crt: crt,
    regime_tributario: regimeAtual
  };

  // Dados para raf_lote_resultados.resultado_dados (JSONB)
  const resultado_dados = {
    consultas_realizadas: ['situacao_cadastral', 'dados_cadastrais', 'endereco', 'cnaes', 'qsa', 'simples_nacional', 'mei'],

    situacao_cadastral: empresa.descricao_situacao_cadastral || 'DESCONHECIDA',
    situacao_cadastral_codigo: empresa.situacao_cadastral || null,
    data_criacao_sociedade: empresa.data_inicio_atividade || null,
    motivo_situacao_cadastral: empresa.motivo_situacao_cadastral || null,

    razao_social: empresa.razao_social || null,
    nome_fantasia: empresa.nome_fantasia || null,
    matriz_filial: empresa.descricao_matriz_filial || null,

    simples_nacional: empresa.opcao_pelo_simples === true,
    data_opcao_simples: empresa.data_opcao_pelo_simples || null,
    data_exclusao_simples: empresa.data_exclusao_do_simples || null,
    mei: empresa.opcao_pelo_mei === true,

    regime_tributario: regimeAtual,
    regime_tributario_ano: regimeAtualAno,

    cnaes: {
      principal: {
        codigo: formatarCnae(empresa.cnae_fiscal),
        descricao: empresa.cnae_fiscal_descricao || null
      },
      secundarios: (empresa.cnaes_secundarios || []).map(c => ({
        codigo: formatarCnae(c.codigo),
        descricao: c.descricao || null
      }))
    },

    qsa: (empresa.qsa || []).map(s => ({
      nome: s.nome_socio || null,
      cpf_cnpj: s.cnpj_cpf_do_socio || null,
      qualificacao: s.qualificacao_socio || null,
      data_entrada: s.data_entrada_sociedade || null
    })),

    endereco: {
      tipo_logradouro: empresa.descricao_tipo_de_logradouro || null,
      logradouro: empresa.logradouro || null,
      numero: empresa.numero || null,
      complemento: empresa.complemento || null,
      bairro: empresa.bairro || null,
      municipio: empresa.municipio || null,
      codigo_municipio: empresa.codigo_municipio || null,
      uf: empresa.uf || null,
      cep: empresa.cep || null
    },

    capital_social: empresa.capital_social || 0,
    natureza_juridica: empresa.natureza_juridica || null,
    porte: empresa.descricao_porte || empresa.porte || null,
    data_inicio_atividade: empresa.data_inicio_atividade || null,

    telefone_1: empresa.ddd_telefone_1 || null,
    telefone_2: empresa.ddd_telefone_2 || null
  };

  items.push({
    json: {
      status: 'sucesso',
      participante: participante,
      resultado_dados: resultado_dados
    }
  });
}

return items;
```

---

#### Estrutura de Saída do Code Node

**Output JSON:**
```json
{
  "status": "sucesso",
  "participante": {
    "cnpj": "12345678000190",
    "razao_social": "EMPRESA EXEMPLO LTDA",
    "nome_fantasia": "EXEMPLO",
    "situacao_cadastral": "ATIVA",
    "uf": "SP",
    "cep": "01000000",
    "municipio": "SAO PAULO",
    "codigo_municipal": "3550308",
    "telefone": "11999999999",
    "endereco": "EXEMPLO",
    "numero": "123",
    "complemento": "SALA 1",
    "bairro": "CENTRO",
    "crt": 1,
    "regime_tributario": "LUCRO PRESUMIDO"
  },
  "resultado_dados": {
    "consultas_realizadas": ["situacao_cadastral", "dados_cadastrais", "..."],
    "situacao_cadastral": "ATIVA",
    "situacao_cadastral_codigo": 2,
    "data_criacao_sociedade": "2015-03-20",
    "simples_nacional": true,
    "mei": false,
    "regime_tributario": "LUCRO PRESUMIDO",
    "regime_tributario_ano": 2023,
    "cnaes": {
      "principal": {"codigo": "6201-5/00", "descricao": "..."},
      "secundarios": [...]
    },
    "qsa": [...],
    "endereco": {...},
    "capital_social": 100000.00
  }
}
```

---

#### Uso no PostgreSQL Node

**UPSERT participantes:**
```sql
INSERT INTO participantes (
    user_id, cnpj, razao_social, nome_fantasia, situacao_cadastral,
    uf, cep, municipio, telefone, crt, created_at, updated_at
) VALUES (
    {{ $json.user_id }},
    '{{ $json.participante.cnpj }}',
    '{{ $json.participante.razao_social }}',
    {{ $json.participante.nome_fantasia ? "'" + $json.participante.nome_fantasia + "'" : 'NULL' }},
    '{{ $json.participante.situacao_cadastral }}',
    '{{ $json.participante.uf }}',
    '{{ $json.participante.cep }}',
    '{{ $json.participante.municipio }}',
    {{ $json.participante.telefone ? "'" + $json.participante.telefone + "'" : 'NULL' }},
    {{ $json.participante.crt }},
    NOW(), NOW()
)
ON CONFLICT (user_id, cnpj) DO UPDATE SET
    razao_social = COALESCE(EXCLUDED.razao_social, participantes.razao_social),
    nome_fantasia = COALESCE(EXCLUDED.nome_fantasia, participantes.nome_fantasia),
    situacao_cadastral = EXCLUDED.situacao_cadastral,
    uf = COALESCE(EXCLUDED.uf, participantes.uf),
    cep = COALESCE(EXCLUDED.cep, participantes.cep),
    crt = EXCLUDED.crt,
    updated_at = NOW()
RETURNING id;
```

**INSERT raf_lote_resultados:**
```sql
INSERT INTO raf_lote_resultados (
    consulta_lote_id, participante_id, status, resultado_dados, created_at
) VALUES (
    {{ $json.consulta_lote_id }},
    {{ $json.participante_id }},
    '{{ $json.status }}',
    '{{ JSON.stringify($json.resultado_dados) }}'::jsonb,
    NOW()
);
```

---

#### Campos Úteis para Validações

| Uso | Campo | Descrição |
|-----|-------|-----------|
| **VCI** | `situacao_cadastral` | Verificar se emitente/destinatário está ativo |
| **VCI** | `simples_nacional` | Validar alíquotas de ICMS/PIS/COFINS |
| **VCI** | `regime_tributario` | Lucro Real vs Presumido (afeta escrituração) |
| **VCI** | `cnaes.principal.codigo` | Validar compatibilidade CFOP/CST |
| **Risk Score** | `situacao_cadastral_codigo` | Score: 2=0pts, 3=50pts, 4/8=100pts |
| **Risk Score** | `capital_social` | Análise de porte |
| **Risk Score** | `qsa` | Verificar sócios em listas restritivas |

---

#### Tratamento de Erros

O Code Node já trata respostas inválidas:

```json
{
  "status": "erro",
  "error_code": "INVALID_RESPONSE",
  "error_message": "CNPJ não encontrado na resposta da API"
}
```

No workflow, adicione um **IF Node** após o Code Node para separar sucesso de erro:

```javascript
// Condição do IF Node
{{ $json.status === 'sucesso' }}
```

---

## InfoSimples (Pagas)

### Autenticação

Todas as requisições usam token no body:

```json
{
    "token": "seu-token-infosimples",
    ...
}
```

### Base URL

```
https://api.infosimples.com
```

---

### SINTEGRA Unificada

**Endpoint:**
```
POST /api/v2/consultas/sintegra/unificada
```

**Body:**
```json
{
    "token": "{{ $env.INFOSIMPLES_TOKEN }}",
    "cnpj": "12345678000190",
    "timeout": 300
}
```

**Resposta:**
```json
{
    "code": 200,
    "code_message": "A requisição foi processada com sucesso.",
    "data": [
        {
            "uf": "SP",
            "inscricao_estadual": "123456789012",
            "situacao": "HABILITADO",
            "data_situacao": "2020-01-15",
            "regime_apuracao": "NORMAL",
            "cnpj": "12345678000190",
            "razao_social": "EMPRESA EXEMPLO LTDA",
            "logradouro": "RUA EXEMPLO, 123",
            "municipio": "SAO PAULO",
            "cep": "01000000",
            "atividade_economica": "DESENVOLVIMENTO DE PROGRAMAS DE COMPUTADOR"
        }
    ]
}
```

**Custo:** 1 crédito

---

### TCU Consolidada

**Endpoint:**
```
POST /api/v2/consultas/tcu/consulta-consolidada-pj
```

**Body:**
```json
{
    "token": "{{ $env.INFOSIMPLES_TOKEN }}",
    "cnpj": "12345678000190",
    "timeout": 300
}
```

**Resposta:**
```json
{
    "code": 200,
    "data": [
        {
            "ceis": false,
            "cnep": false,
            "acordao_tcu": false,
            "licitacao_impedida": false,
            "detalhes": {
                "ceis_registros": [],
                "cnep_registros": [],
                "tcu_registros": []
            }
        }
    ]
}
```

**Custo:** 1 crédito

---

### CND Federal (PGFN)

**Endpoint:**
```
POST /api/v2/consultas/receita-federal/pgfn-nova
```

**Body:**
```json
{
    "token": "{{ $env.INFOSIMPLES_TOKEN }}",
    "cnpj": "12345678000190",
    "timeout": 300
}
```

**Resposta:**
```json
{
    "code": 200,
    "data": [
        {
            "status": "NEGATIVA",
            "tipo": "CND_CONJUNTA",
            "codigo_controle": "XXXX.XXXX.XXXX.XXXX",
            "data_emissao": "2026-01-15",
            "data_validade": "2026-07-15",
            "texto_certidao": "Certifica que não constam pendências..."
        }
    ]
}
```

**Status possíveis:**
- `NEGATIVA` - Nada consta
- `POSITIVA_COM_EFEITO_NEGATIVA` - Débitos parcelados/suspensos
- `POSITIVA` - Há pendências

**Custo:** 1 crédito

---

### CRF (FGTS)

**Endpoint:**
```
POST /api/v2/consultas/caixa/regularidade
```

**Body:**
```json
{
    "token": "{{ $env.INFOSIMPLES_TOKEN }}",
    "cnpj": "12345678000190",
    "timeout": 300
}
```

**Resposta:**
```json
{
    "code": 200,
    "data": [
        {
            "situacao": "REGULAR",
            "data_emissao": "2026-01-15",
            "data_validade": "2026-02-14",
            "numero_crf": "2026011500001234"
        }
    ]
}
```

**Situações possíveis:**
- `REGULAR` - Em dia
- `IRREGULAR` - Pendências

**Custo:** 1 crédito

---

### CND Estadual (SEFAZ)

**Endpoint:**
```
POST /api/v2/consultas/sefaz/certidao-debitos
```

**Body:**
```json
{
    "token": "{{ $env.INFOSIMPLES_TOKEN }}",
    "cnpj": "12345678000190",
    "uf": "SP",
    "timeout": 300
}
```

**Resposta:**
```json
{
    "code": 200,
    "data": [
        {
            "uf": "SP",
            "status": "NEGATIVA",
            "data_emissao": "2026-01-15",
            "data_validade": "2026-04-15",
            "codigo_verificacao": "ABCD1234"
        }
    ]
}
```

**Custo:** 1 crédito

---

### CNDT (Trabalhista)

**Endpoint:**
```
POST /api/v2/consultas/tst/cndt
```

**Body:**
```json
{
    "token": "{{ $env.INFOSIMPLES_TOKEN }}",
    "cnpj": "12345678000190",
    "timeout": 300
}
```

**Resposta:**
```json
{
    "code": 200,
    "data": [
        {
            "status": "NEGATIVA",
            "data_emissao": "2026-01-15",
            "data_validade": "2026-07-15",
            "numero_certidao": "12345678901234567890"
        }
    ]
}
```

**Custo:** 1 crédito

---

### Protestos (IEPTB)

**Endpoint:**
```
POST /api/v2/consultas/ieptb/protestos
```

**Body:**
```json
{
    "token": "{{ $env.INFOSIMPLES_TOKEN }}",
    "cnpj": "12345678000190",
    "timeout": 300
}
```

**Resposta:**
```json
{
    "code": 200,
    "data": [
        {
            "tem_protestos": false,
            "total_protestos": 0,
            "valor_total": 0.00,
            "protestos": []
        }
    ]
}
```

**Com protestos:**
```json
{
    "code": 200,
    "data": [
        {
            "tem_protestos": true,
            "total_protestos": 2,
            "valor_total": 5000.00,
            "protestos": [
                {
                    "cartorio": "1º TABELIONATO DE PROTESTOS DE SAO PAULO",
                    "data_protesto": "2025-06-15",
                    "valor": 3000.00,
                    "credor": "FORNECEDOR LTDA"
                },
                {
                    "cartorio": "2º TABELIONATO DE PROTESTOS DE SAO PAULO",
                    "data_protesto": "2025-08-20",
                    "valor": 2000.00,
                    "credor": "OUTRO FORNECEDOR LTDA"
                }
            ]
        }
    ]
}
```

**Custo:** 1 crédito

---

### Lista Devedores PGFN

**Endpoint:**
```
POST /api/v2/consultas/pgfn/lista-devedores
```

**Body:**
```json
{
    "token": "{{ $env.INFOSIMPLES_TOKEN }}",
    "cnpj": "12345678000190",
    "timeout": 300
}
```

**Resposta:**
```json
{
    "code": 200,
    "data": [
        {
            "inscrito": false,
            "valor_consolidado": 0.00,
            "inscricoes": []
        }
    ]
}
```

**Custo:** 1 crédito

---

### Trabalho Escravo

**Endpoint:**
```
POST /api/v2/consultas/sit/trabalho-escravo
```

**Body:**
```json
{
    "token": "{{ $env.INFOSIMPLES_TOKEN }}",
    "cnpj": "12345678000190",
    "timeout": 300
}
```

**Resposta:**
```json
{
    "code": 200,
    "data": [
        {
            "encontrado": false,
            "registros": []
        }
    ]
}
```

**Custo:** 1 crédito

---

### IBAMA Autuações

**Endpoint:**
```
POST /api/v2/consultas/ibama/autuacoes
```

**Body:**
```json
{
    "token": "{{ $env.INFOSIMPLES_TOKEN }}",
    "cnpj": "12345678000190",
    "timeout": 300
}
```

**Resposta:**
```json
{
    "code": 200,
    "data": [
        {
            "tem_autuacoes": false,
            "total_autuacoes": 0,
            "autuacoes": []
        }
    ]
}
```

**Custo:** 1 crédito

---

### Processos CNJ

**Endpoint:**
```
POST /api/v2/consultas/cnj/seeu-processos
```

**Body:**
```json
{
    "token": "{{ $env.INFOSIMPLES_TOKEN }}",
    "cnpj": "12345678000190",
    "timeout": 300
}
```

**Resposta:**
```json
{
    "code": 200,
    "data": [
        {
            "tem_processos": true,
            "total_processos": 3,
            "processos": [
                {
                    "numero": "0001234-56.2025.8.26.0100",
                    "tribunal": "TJSP",
                    "classe": "PROCEDIMENTO COMUM CIVEL",
                    "assunto": "COBRANCA",
                    "data_distribuicao": "2025-03-15",
                    "valor_causa": 50000.00,
                    "polo": "REU"
                }
            ]
        }
    ]
}
```

**Custo:** 1 crédito

---

## Tratamento de Erros

### Códigos de Resposta InfoSimples

| Código | Significado | Ação |
|--------|-------------|------|
| 200 | Sucesso | Processar dados |
| 400 | Parâmetros inválidos | Verificar payload |
| 401 | Token inválido | Verificar token |
| 402 | Créditos insuficientes | Recarregar conta |
| 404 | CNPJ não encontrado | Marcar como erro |
| 408 | Timeout | Retry |
| 500 | Erro interno | Retry com backoff |

### Retry Policy

```javascript
// No n8n, configurar no HTTP Request Node:
{
    "retryOnFail": true,
    "maxTries": 3,
    "waitBetweenTries": 5000  // 5 segundos
}
```

### Fallback para Erros

```javascript
// Code Node após HTTP Request
const response = $input.first().json;

if (response.code !== 200) {
    return [{
        json: {
            consulta: 'nome_da_consulta',
            status: 'erro',
            error_code: response.code,
            error_message: response.code_message || 'Erro desconhecido',
            data: null
        }
    }];
}

return [{
    json: {
        consulta: 'nome_da_consulta',
        status: 'sucesso',
        data: response.data[0]
    }
}];
```

---

## Planos e Consultas Incluídas

| Plano | Créditos | Consultas |
|-------|----------|-----------|
| `gratuito` | 0 | Minha Receita apenas |
| `validacao` | 4 | + sintegra, tcu_consolidada |
| `licitacao` | 10 | + cnd_federal, crf_fgts, cnd_estadual, cndt |
| `compliance` | 14 | + protestos, lista_devedores_pgfn |
| `due_diligence` | 18 | + trabalho_escravo, ibama_autuacoes |
| `enterprise` | 20 | + processos_cnj |

### Código para Filtrar Consultas por Plano

```javascript
const plano = $json.plano_codigo;
const todasConsultas = JSON.parse($json.consultas_incluidas);

// Separar gratuitas e pagas
const consultasGratuitas = ['situacao_cadastral', 'dados_cadastrais', 'endereco',
                            'cnaes', 'qsa', 'simples_nacional', 'mei'];

const consultasPagas = todasConsultas.filter(c => !consultasGratuitas.includes(c));

return [{
    json: {
        ...item,
        consultas_gratuitas: consultasGratuitas,
        consultas_pagas: consultasPagas,
        tem_consultas_pagas: consultasPagas.length > 0
    }
}];
```

---

## Rate Limits

### Minha Receita

- Sem limite documentado, mas recomenda-se 1 req/segundo
- Cache local recomendado para CNPJs já consultados

### InfoSimples

- Limite por token (verificar painel)
- Recomendado: máximo 10 requisições paralelas
- Timeout padrão: 300 segundos

---

**Última atualização:** 2026-01-31

---

## Code Node Completo (Copiar daqui)

```javascript
const items = [];

for (const item of $input.all()) {
  const empresa = Array.isArray(item.json) ? item.json[0] : item.json;

  if (!empresa || !empresa.cnpj) {
    items.push({
      json: {
        status: 'erro',
        error_code: 'INVALID_RESPONSE',
        error_message: 'CNPJ não encontrado na resposta da API'
      }
    });
    continue;
  }

  const crt = empresa.opcao_pelo_simples === true ? 1 : 3;

  const regimeMaisRecente = empresa.regime_tributario?.length > 0
    ? empresa.regime_tributario[empresa.regime_tributario.length - 1]
    : null;
  const regimeAtual = regimeMaisRecente?.forma_de_tributacao || null;
  const regimeAtualAno = regimeMaisRecente?.ano || null;

  const formatarCnae = (codigo) => {
    if (!codigo) return null;
    const str = String(codigo).padStart(7, '0');
    return str.replace(/(\d{4})(\d)(\d{2})/, '$1-$2/$3');
  };

  const participante = {
    cnpj: empresa.cnpj,
    razao_social: empresa.razao_social || null,
    nome_fantasia: empresa.nome_fantasia || null,
    situacao_cadastral: empresa.descricao_situacao_cadastral || 'DESCONHECIDA',
    uf: empresa.uf || null,
    cep: empresa.cep || null,
    municipio: empresa.municipio || null,
    codigo_municipal: empresa.codigo_municipio ? String(empresa.codigo_municipio) : null,
    telefone: empresa.ddd_telefone_1 || null,
    endereco: empresa.logradouro || null,
    numero: empresa.numero || null,
    complemento: empresa.complemento || null,
    bairro: empresa.bairro || null,
    crt: crt,
    regime_tributario: regimeAtual
  };

  const resultado_dados = {
    consultas_realizadas: ['situacao_cadastral', 'dados_cadastrais', 'endereco', 'cnaes', 'qsa', 'simples_nacional', 'mei'],
    situacao_cadastral: empresa.descricao_situacao_cadastral || 'DESCONHECIDA',
    situacao_cadastral_codigo: empresa.situacao_cadastral || null,
    data_criacao_sociedade: empresa.data_inicio_atividade || null,
    motivo_situacao_cadastral: empresa.motivo_situacao_cadastral || null,
    razao_social: empresa.razao_social || null,
    nome_fantasia: empresa.nome_fantasia || null,
    matriz_filial: empresa.descricao_matriz_filial || null,
    simples_nacional: empresa.opcao_pelo_simples === true,
    data_opcao_simples: empresa.data_opcao_pelo_simples || null,
    data_exclusao_simples: empresa.data_exclusao_do_simples || null,
    mei: empresa.opcao_pelo_mei === true,
    regime_tributario: regimeAtual,
    regime_tributario_ano: regimeAtualAno,
    cnaes: {
      principal: {
        codigo: formatarCnae(empresa.cnae_fiscal),
        descricao: empresa.cnae_fiscal_descricao || null
      },
      secundarios: (empresa.cnaes_secundarios || []).map(c => ({
        codigo: formatarCnae(c.codigo),
        descricao: c.descricao || null
      }))
    },
    qsa: (empresa.qsa || []).map(s => ({
      nome: s.nome_socio || null,
      cpf_cnpj: s.cnpj_cpf_do_socio || null,
      qualificacao: s.qualificacao_socio || null,
      data_entrada: s.data_entrada_sociedade || null
    })),
    endereco: {
      tipo_logradouro: empresa.descricao_tipo_de_logradouro || null,
      logradouro: empresa.logradouro || null,
      numero: empresa.numero || null,
      complemento: empresa.complemento || null,
      bairro: empresa.bairro || null,
      municipio: empresa.municipio || null,
      codigo_municipio: empresa.codigo_municipio || null,
      uf: empresa.uf || null,
      cep: empresa.cep || null
    },
    capital_social: empresa.capital_social || 0,
    natureza_juridica: empresa.natureza_juridica || null,
    porte: empresa.descricao_porte || empresa.porte || null,
    data_inicio_atividade: empresa.data_inicio_atividade || null,
    telefone_1: empresa.ddd_telefone_1 || null,
    telefone_2: empresa.ddd_telefone_2 || null
  };

  items.push({
    json: {
      status: 'sucesso',
      participante: participante,
      resultado_dados: resultado_dados
    }
  });
}

return items;
```
