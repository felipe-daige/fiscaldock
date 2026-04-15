# InfoSimples — CND Federal (`receita-federal/pgfn`)

Referência dos outputs reais da API InfoSimples para consulta da CND Federal (Receita Federal + PGFN). Usado para mapear campos no Laravel/n8n e tratar todos os códigos de resposta.

- **Endpoint:** `https://api.infosimples.com/api/v2/consultas/receita-federal/pgfn`
- **Parâmetros principais:** `token`, `cnpj` (ou `cpf`), `preferencia_emissao` (`1via` | `2via`), `birthdate` (quando CPF), `timeout`
- **Auth:** token como query param (`INFOSIMPLES_TOKEN`)

## Grupos de resposta

| Grupo | Códigos | Ação sugerida |
|---|---|---|
| Sucesso | `200`, `201` | Persistir `data[0]`, marcar consulta como concluída |
| Não encontrado | `612` | Marcar participante como "sem registro", não retentar |
| Indeterminado por origem | `611` | Marcar CND como `INDETERMINADO`, não classificar como irregular, logar `errors[]` |
| Erro do participante (parâmetro recusado/alterado) | `608`, `619`, `620` | Marcar erro definitivo, logar `errors[]`, estornar se aplicável |
| Retry temporário | `600`, `605`, `609`, `610`, `613`, `614`, `615`, `618` | Reenfileirar com backoff |
| Fatal (config/integração) | `601`, `602`, `603`, `604`, `606`, `607`, `617`, `621`, `622` | Alertar operação, não retentar sem correção |

## Estados normalizados de CND Federal

| Estado | Quando usar | Label sugerido |
|---|---|---|
| `NEGATIVA` | `code` 200/201 com `tipo` negativa pura | Regular |
| `REGULAR_COM_RESSALVA` | `code` 200/201 com `tipo` "Positiva com efeitos de negativa" | Regular com ressalva |
| `IRREGULAR` | `code` 200/201 com `tipo` positiva sem efeitos ou retorno que comprova ausência de regularidade | Irregular |
| `NAO_ENCONTRADA` | `code` 612 | Não encontrada |
| `INDETERMINADO` | `code` 611: a fonte não conseguiu emitir a certidão pela internet por dados insuficientes na Receita Federal/origem | Não foi possível emitir |
| `ERRO_TEMPORARIO` | códigos de retry temporário | Tente novamente |
| `ERRO_INTEGRACAO` | códigos fatais/configuração | Erro operacional |

`611` **não é irregularidade fiscal**. É um estado indeterminado: em algumas empresas, a própria fonte oficial não emite a certidão pela internet porque as informações disponíveis na Receita Federal/origem são insuficientes. O sistema deve mostrar esse caso como `INDETERMINADO` e preservar a mensagem original da fonte.

## Mapeamento crítico `data[0]` → Laravel

| Campo InfoSimples | Destino | Observação |
|---|---|---|
| `tipo` | `resultado_dados.cnd_federal.status` | Lido por `ConsultaController` via `strtoupper()` |
| `validade` / `validade_data` | `resultado_dados.cnd_federal.data_validade` | Formato `DD/MM/YYYY` |
| `emissao_data` | `resultado_dados.cnd_federal.emissao_data` | `DD/MM/YYYY` |
| `certidao_codigo` | `resultado_dados.cnd_federal.certidao_codigo` | Código identificador |
| `conseguiu_emitir_certidao_negativa` | `resultado_dados.cnd_federal.conseguiu_emitir` | boolean |
| `debitos_pgfn` / `debitos_rfb` | `resultado_dados.cnd_federal.debitos_pgfn` / `debitos_rfb` | boolean |
| `mensagem` | `resultado_dados.cnd_federal.mensagem` | Texto completo da certidão |
| `situacao` | `resultado_dados.cnd_federal.situacao` | Ex.: "Válida Prorrogada até ..." |
| `razao_social` / `nome` | Sobrescreve nome do participante? | **NÃO** — Laravel não atualiza participante |

Estrutura alvo (dentro de `consultas.resultado_dados`):

```json
{
  "cnd_federal": {
    "status": "Positiva com efeitos de negativa",
    "certidao_codigo": "11AA.111A.1AA1.1A11",
    "emissao_data": "DD/MM/YYYY",
    "data_validade": "DD/MM/YYYY",
    "conseguiu_emitir": true,
    "debitos_pgfn": false,
    "debitos_rfb": true,
    "mensagem": "...",
    "situacao": "Válida Prorrogada até DD/MM/YYYY"
  }
}
```

## Valores conhecidos de `tipo`

- `Negativa`
- `Positiva com efeitos de negativa`
- `Positiva`

## Outputs brutos

### `200` — sucesso

```json
{
  "code": 200,
  "code_message": "A requisição foi processada com sucesso.",
  "errors": [],
  "header": {
    "api_version": "v2",
    "service": "receita-federal/pgfn",
    "parameters": {
      "cnpj": "11111111111111",
      "preferencia_emissao": "2via"
    },
    "client_name": "Minha Empresa",
    "token_name": "Token de Produção",
    "billable": true,
    "price": "0.24",
    "requested_at": "2020-04-14T08:37:35.000-03:00",
    "elapsed_time_in_milliseconds": 705,
    "remote_ip": "111.111.111.111",
    "signature": "..."
  },
  "data_count": 1,
  "data": [
    {
      "certidao": "CERTIDÃO POSITIVA COM EFEITOS DE NEGATIVA DE DÉBITOS RELATIVOS AOS TRIBUTOS FEDERAIS E À DÍVIDA ATIVA DA UNIÃO",
      "certidao_codigo": "11AA.111A.1AA1.1A11",
      "cnpj": "11.111.111/1111-11",
      "cnpj_situacao": "Válida Prorrogada até 11/11/1111",
      "comprovante_tipo": "pdf",
      "conseguiu_emitir_certidao_negativa": true,
      "consulta_comprovante": "11AA.111A.1AA1.1A11",
      "consulta_datahora": "11/11/1111 11:11:11",
      "cpf": "",
      "debitos_pgfn": false,
      "debitos_rfb": true,
      "descricao": "Exemplo de texto",
      "emissao_data": "11/11/1111",
      "mensagem": "CERTIDÃO NEGATIVA DE DÉBITOS RELATIVOS AOS TRIBUTOS FEDERAIS E À DÍVIDA ATIVA DA UNIÃO",
      "nome": "Nome de Exemplo",
      "normalizado_cnpj": "11111111111111",
      "normalizado_consulta_datahora": "11/11/1111 11:11:11",
      "normalizado_cpf": "",
      "observacoes": "Exemplo de texto",
      "razao_social": "Empresa XYZ",
      "situacao": "Válida Prorrogada até 30/06/2020",
      "tipo": "Positiva com efeitos de negativa",
      "validade": "11/11/1111",
      "validade_data": "11/11/1111",
      "validade_prorrogada": "11/11/1111",
      "site_receipt": null
    }
  ],
  "site_receipts": []
}
```

### Códigos de erro

Todos os erros retornam `data_count: 0`, `data: []` e `site_receipts` com a URL de exemplo. Abaixo apenas `code` + `code_message` + `errors[]` relevantes (header omitido — é idêntico em estrutura ao do 200).

#### `600` — erro inesperado (retry)
```json
{
  "code": 600,
  "code_message": "Um erro inesperado ocorreu e será analisado.",
  "errors": []
}
```

#### `601` — autenticação (fatal)
```json
{
  "code": 601,
  "code_message": "Não foi possível se autenticar com o token informado.",
  "errors": []
}
```

#### `602` — serviço inválido (fatal)
```json
{
  "code": 602,
  "code_message": "O serviço informado na URL não é válido.",
  "errors": []
}
```

#### `603` — sem autorização/limite (fatal)
```json
{
  "code": 603,
  "code_message": "O token informado não tem autorização de acesso ao serviço. Verifique se ele continua ativo e se ele não possui algum limite de uso especificado.",
  "errors": []
}
```

#### `604` — validação interna (fatal)
```json
{
  "code": 604,
  "code_message": "A consulta não foi validada antes de pesquisar a fonte de origem.",
  "errors": ["timeout não é um número"]
}
```

#### `605` — timeout (retry)
```json
{
  "code": 605,
  "code_message": "A consulta não foi realizada dentro do tempo de limite de timeout especificado.",
  "errors": []
}
```

#### `606` — parâmetros obrigatórios ausentes (fatal)
```json
{
  "code": 606,
  "code_message": "Parâmetros obrigatórios não foram enviados. Por favor, verifique a documentação de uso do serviço.",
  "errors": []
}
```

#### `607` — parâmetros inválidos (fatal)
```json
{
  "code": 607,
  "code_message": "Parâmetro(s) inválido(s).",
  "errors": []
}
```

#### `608` — parâmetro recusado pela origem (erro do participante)
```json
{
  "code": 608,
  "code_message": "Os parâmetros foram recusados pelo site ou aplicativo de origem que processou esta consulta.",
  "errors": ["Cnpj não é válido"]
}
```

#### `609` — tentativas excedidas (retry)
```json
{
  "code": 609,
  "code_message": "Tentativas de consultar o site ou aplicativo de origem excedidas.",
  "errors": []
}
```

#### `610` — falha em CAPTCHA (retry)
```json
{
  "code": 610,
  "code_message": "Falha em resolver algum tipo de CAPTCHA.",
  "errors": []
}
```

#### `611` — dados incompletos na origem (erro do participante)
```json
{
  "code": 611,
  "code_message": "Os dados estão incompletos no site ou aplicativo de origem e não puderam ser retornados.",
  "errors": [
    "As informações disponíveis na Receita Federal sobre o contribuinte 08.070.566/0001-00 são insuficientes para emitir a certidão pela Internet."
  ],
  "header": {
    "service": "receita-federal/pgfn",
    "parameters": {
      "cnpj": "08070566000100",
      "preferencia_emissao": "nova"
    },
    "billable": true,
    "price": "0.26",
    "requested_at": "2026-04-15T14:45:52.000-03:00",
    "elapsed_time_in_milliseconds": 13965
  },
  "data_count": 0,
  "data": [],
  "site_receipts": ["https://...html"]
}
```

Mapeamento obrigatório para `611`:

```json
{
  "cnd_federal": {
    "status": "INDETERMINADO",
    "conseguiu_emitir": false,
    "motivo": "DADOS_INSUFICIENTES_ORIGEM",
    "mensagem": "As informações disponíveis na Receita Federal sobre o contribuinte 08.070.566/0001-00 são insuficientes para emitir a certidão pela Internet.",
    "errors": [
      "As informações disponíveis na Receita Federal sobre o contribuinte 08.070.566/0001-00 são insuficientes para emitir a certidão pela Internet."
    ]
  },
  "infosimples": {
    "service": "receita-federal/pgfn",
    "code": 611,
    "code_message": "Os dados estão incompletos no site ou aplicativo de origem e não puderam ser retornados.",
    "billable": true,
    "price": "0.26"
  }
}
```

#### `612` — não encontrado
```json
{
  "code": 612,
  "code_message": "A consulta não retornou dados no site ou aplicativo de origem no qual a automação foi executada.",
  "errors": ["Nenhum registro foi localizado com os parâmetros informados"]
}
```

#### `613` — bloqueio pelo servidor de origem (retry)
```json
{
  "code": 613,
  "code_message": "A consulta foi bloqueada pelo servidor do site ou aplicativo de origem. Por favor, tente novamente.",
  "errors": []
}
```

#### `614` — erro inesperado na origem (retry)
```json
{
  "code": 614,
  "code_message": "Um erro inesperado com o site ou aplicativo de origem ocorreu. Por favor, tente novamente.",
  "errors": []
}
```

#### `615` — origem indisponível (retry)
```json
{
  "code": 615,
  "code_message": "O site ou aplicativo de origem parece estar indisponível.",
  "errors": []
}
```

#### `617` — sobrecarga do prestador (fatal)
```json
{
  "code": 617,
  "code_message": "Contate o prestador de serviço. Há uma sobrecarga de uso do serviço.",
  "errors": []
}
```

#### `618` — origem sobrecarregada (retry)
```json
{
  "code": 618,
  "code_message": "O site ou aplicativo de origem está sobrecarregado. Tente novamente em alguns instantes.",
  "errors": []
}
```

#### `619` — parâmetro alterado na origem (erro do participante)
```json
{
  "code": 619,
  "code_message": "O parâmetro enviado sofreu alterações no site ou aplicativo de origem. Verifique a alteração diretamente no site ou aplicativo de origem.",
  "errors": [
    "O registro atualizou seus valores de identificação e não pode mais ser consultado com os parâmetros informados"
  ]
}
```

#### `620` — erro da origem não reversível (erro do participante)
```json
{
  "code": 620,
  "code_message": "O site ou aplicativo de origem emitiu um erro que provavelmente não mudará em breve para esta consulta. Leia-o para saber mais.",
  "errors": [
    "A consulta não pode ser realizada pelo site usando os parâmetros informados. Entre em contato com um posto de atendimento."
  ]
}
```

#### `621` — falha ao gerar comprovante (fatal)
```json
{
  "code": 621,
  "code_message": "Houve um erro ao tentar gerar o arquivo de visualização desta requisição.",
  "errors": []
}
```

#### `622` — consulta repetida (fatal)
```json
{
  "code": 622,
  "code_message": "Parece que você está tentando realizar a mesma consulta diversas vezes seguidas. Por favor, verifique se há algum problema em sua integração. Se acredita que está tudo certo, entre em contato com o suporte.",
  "errors": []
}
```

## Regra de cobrança (`header.billable`)

- `billable: true` → consulta é paga (sucesso, 608, 611, 619, 620, 606, 607 em alguns casos). Considerar estorno de créditos apenas nos erros do participante conforme política interna.
- `billable: false` → consulta não paga (600, 601–605, 609, 610, 613–618, 621, 622). Nunca cobra o cliente final.
