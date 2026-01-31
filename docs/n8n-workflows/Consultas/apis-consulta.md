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

```json
{
    "cnpj": "12345678000190",
    "identificador_matriz_filial": 1,
    "descricao_matriz_filial": "MATRIZ",
    "razao_social": "EMPRESA EXEMPLO LTDA",
    "nome_fantasia": "EXEMPLO",
    "situacao_cadastral": "ATIVA",
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
    ]
}
```

### Mapeamento de Campos

| Campo API | Campo Sistema |
|-----------|---------------|
| `situacao_cadastral` | `situacao_cadastral` |
| `razao_social` | `razao_social` |
| `nome_fantasia` | `nome_fantasia` |
| `opcao_pelo_simples` | `simples_nacional` |
| `opcao_pelo_mei` | `mei` |
| `cnae_fiscal` + `cnae_fiscal_descricao` | `cnaes.principal` |
| `cnaes_secundarios` | `cnaes.secundarios` |
| `qsa` | `qsa` |
| Campos de endereço | `endereco.*` |

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
