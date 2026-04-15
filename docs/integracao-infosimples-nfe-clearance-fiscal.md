# Integração InfoSimples — Consulta NF-e (receita-federal/nfe)

## Decisão de Produto — MVP do Clearance NF-e

Em `2026-04-15`, a decisão para o MVP do produto de clearance foi:

- **Fornecedor inicial do MVP:** `InfoSimples`
- **Motivação principal:** menor barreira para começar a validar a feature com baixo compromisso de implementação
- **Papel no MVP:** consulta pontual de NF-e por chave de acesso, enriquecimento de dados e validação operacional inicial do fluxo de clearance
- **Fonte inicial da consulta:** `receita-federal/nfe` via InfoSimples, usando a visão nacional/Receita Federal/Portal Nacional como fonte única do MVP
- **Persistência:** a consulta avulsa deve salvar os dados retornados no PostgreSQL via n8n, não apenas exibir o resultado em tela
- **Associação com cliente:** o usuário pode associar opcionalmente a nota consultada a um cliente do FiscalDock
- **Limite da decisão:** o InfoSimples **não** deve ser tratado como backbone definitivo do produto enquanto não houver validação de aderência da feature, volume real e comportamento de custo

## Estado atual no FiscalDock

Frontend disponível:

- `GET /app/validacao/buscar-nfe`
- View: `resources/views/autenticado/validacao/buscar-nfe.blade.php`
- JS: `public/js/clearance-buscar-nfe.js`

A tela já permite:

- escolher o tipo de documento previsto (`NF-e`, `CT-e`, `NFS-e`)
- informar chave de acesso
- associar opcionalmente um cliente
- visualizar custo estimado
- visualizar estados previstos
- consultar um resultado simulado no frontend
- ver histórico local das últimas consultas de documentos fiscais

Backend/n8n ainda pendente:

- endpoint Laravel para disparar consulta real
- validação server-side de `cliente_id`
- chamada n8n
- chamada InfoSimples
- upsert em `xml_notas`
- retorno assíncrono ou polling do resultado real

### Consulta avulsa, n8n e cliente opcional

No MVP, a busca avulsa de NF-e deve seguir o padrão operacional já usado nos fluxos de importação: Laravel valida a requisição e o n8n executa a integração externa e a persistência no banco.

Payload mínimo previsto do Laravel para o n8n:

```json
{
  "user_id": 1,
  "tipo_documento": "nfe",
  "chave_acesso": "11111111111111111111111111111111111111111111",
  "cliente_id": 5
}
```

`cliente_id` é opcional. Quando informado, o Laravel deve validar que o cliente pertence ao usuário autenticado antes de chamar o n8n. Quando não informado, a nota ainda deve ser salva com `user_id`, mas sem vínculo direto com cliente.

Responsabilidades do n8n:

- consultar `receita-federal/nfe` via InfoSimples
- normalizar campos principais da nota
- gravar ou atualizar a nota em `xml_notas`
- salvar o payload completo em `xml_notas.payload`
- persistir ou reaproveitar participantes de emitente/destinatário quando aplicável
- associar `xml_notas.cliente_id` ao cliente escolhido pelo usuário quando `cliente_id` vier no payload
- atualizar `emit_cliente_id` ou `dest_cliente_id` quando houver correspondência clara com cliente/participante
- não duplicar nota em reconsulta da mesma chave

A chave de idempotência operacional deve ser `user_id + nfe_id`. Reconsultas da mesma chave devem fazer upsert: atualizar dados, situação/payload e associação de cliente quando informada, preservando o histórico útil da nota.

### Fonte nacional vs SEFAZ estadual

Para o MVP, o FiscalDock **não** deve consultar a SEFAZ de cada estado diretamente nem tentar comparar múltiplas fontes. A consulta avulsa começa pela fonte nacional `receita-federal/nfe` via InfoSimples porque:

- reduz complexidade de implementação e suporte
- evita manter regras ou fallbacks por UF no primeiro ciclo
- é suficiente para validar o uso de busca avulsa por chave
- mantém o produto focado em uma resposta simples: encontrada, autorizada, cancelada, denegada, não encontrada ou erro de consulta

Discrepâncias entre a fonte nacional e uma SEFAZ estadual podem existir por sincronização, indisponibilidade, atraso em eventos recentes ou diferenças de retorno do portal consultado. Isso **não entra no MVP** como alerta automático.

Evolução futura: se houver demanda e volume, adicionar segunda fonte/fallback estadual e registrar divergências como alerta operacional, por exemplo `Divergência entre fonte nacional e SEFAZ estadual`. Esse alerta deve ser tratado como indício de inconsistência/sincronização, não como fraude automática.

### Diretriz futura

Se o clearance mostrar aderência real de uso, volume e conversão, a direção planejada é:

- **Migrar ou complementar com `Focus NFe`**
- **Objetivo da segunda fase:** reduzir custo por volume, ganhar melhor cobertura operacional para recebidas/MDe e sair de uma dependência excessiva de consulta avulsa por chave

## Comparativo de custo usado na decisão

Comparativo simplificado considerado em `2026-04-15`.

### Faixa de 300 notas

| Fornecedor | Premissa usada | Total estimado |
|---|---|---|
| `InfoSimples` | franquia mínima mensal de `R$ 100,00` | `R$ 100,00` |
| `Focus NFe` | plano `Solo` `R$ 89,90` + `200` notas adicionais a `R$ 0,10` | `R$ 109,90` |

Leitura: em `300` notas, o InfoSimples ficou ligeiramente mais barato para começar.

### Faixa de 1.000 notas

| Fornecedor | Premissa usada | Total estimado |
|---|---|---|
| `InfoSimples` | `500 x R$ 0,20` + `500 x R$ 0,16` + adicional `NFE` `1.000 x R$ 0,06` | `R$ 240,00` |
| `Focus NFe` | plano `Solo` `R$ 89,90` + `900` notas adicionais a `R$ 0,10` | `R$ 179,90` |

Leitura: em volume maior, o Focus passa a ser economicamente melhor.

## Critério de evolução do MVP

O MVP com InfoSimples deve responder estas perguntas antes de virar arquitetura definitiva:

- Usuários realmente consultam NF-e por chave no fluxo de clearance?
- O uso é mais pontual ou recorrente?
- O retorno do InfoSimples é suficiente para o tipo de alerta que queremos exibir?
- O custo por nota continua aceitável em volume real?
- Existe demanda suficiente para justificar uma camada mais robusta de recebidas/MDe?

Se a resposta for positiva, a próxima etapa é abrir frente de evolução para:

- `Focus NFe` como fornecedor principal de escala
- InfoSimples mantido apenas como fallback de conveniência ou consulta avulsa, se ainda fizer sentido

## Visão Geral

API InfoSimples `receita-federal/nfe` permite consultar dados completos de uma NF-e na SEFAZ pela chave de acesso (44 dígitos). Retorna emitente, destinatário, produtos, tributos, situação, eventos e payload completo.

- **Endpoint:** `POST https://api.infosimples.com/api/v2/consultas/receita-federal/nfe`
- **Custo de referência na documentação existente:** R$ 0,24 por consulta
- **Parâmetro principal:** `nfe` (chave de acesso 44 dígitos)
- **Autenticação:** token como parâmetro (`token=INFOSIMPLES_TOKEN`)
- **Requer certificado digital:** `pkcs12_cert` + `pkcs12_pass` (AES-256-GCM encrypted base64)

## Estrutura do Payload de Resposta

```
code: 200
data[0]:
├── nfe                    # Dados da nota (modelo, série, número, situação, eventos)
├── emitente               # CNPJ, razão social, UF, IE, regime, endereço
├── destinatario           # CNPJ/CPF, nome, UF, IE, indicador IE
├── produtos[]             # Itens com NCM, CEST, CFOP, ICMS, PIS, COFINS, IPI
├── totais                 # Valores totais de tributos (ICMS, ST, PIS, COFINS, IPI)
├── cobranca               # Fatura, duplicatas, formas de pagamento
├── transporte             # Modalidade frete, transportador, veículo, volumes
├── documentos_referenciados[]  # Chaves de NF-e referenciadas
├── info_adicionais        # Informações complementares, dados de compra
├── local / local_entrega / local_retirada
├── avulsa                 # Dados de NF-e avulsa (raro)
├── resumida               # Versão resumida dos dados principais
├── url_html / url_xml     # Links para visualização
└── normalizado_chave_acesso  # Chave sem formatação (44 dígitos)
```

### Campos `normalizado_*`

A InfoSimples retorna campos formatados BR (ex: `"808,72"`) e campos `normalizado_*` já convertidos para float/int. **Sempre usar os campos `normalizado_*` no mapeamento.**

## Mapeamento para o Banco de Dados

### `xml_notas`

| Campo `xml_notas` | Campo InfoSimples | Notas |
|---|---|---|
| `user_id` | Payload Laravel | Usuário dono da consulta |
| `cliente_id` | Payload Laravel `cliente_id` | Opcional; preenchido quando usuário associar a nota a um cliente |
| `nfe_id` | `normalizado_chave_acesso` | 44 dígitos |
| `tipo_documento` | Fixo `"NFE"` | Modelo 55 |
| `numero_nota` | `nfe.numero` | |
| `serie` | `nfe.serie` | |
| `data_emissao` | `nfe.data_emissao` | Parse `dd/mm/yyyy HH:mm:ss-TZ` |
| `natureza_operacao` | `nfe.emissao.natureza_operacao` | |
| `valor_total` | `nfe.normalizado_valor_total` | Float |
| `tipo_nota` | `nfe.emissao.normalizado_tipo_operacao` | 0=entrada, 1=saída |
| `finalidade` | `nfe.emissao.normalizado_finalidade` | 1=normal, 2=complementar, 3=ajuste, 4=devolução |
| `chave_referenciada` | `documentos_referenciados[0].normalizado_chave_acesso` | |
| `emit_cnpj` | `emitente.normalizado_cnpj` | 14 dígitos |
| `emit_razao_social` | `emitente.nome` | |
| `emit_uf` | `emitente.uf` | |
| `dest_cnpj` | `destinatario.normalizado_cnpj` ou `normalizado_cpf` | CPF = 11 chars, varchar(14) aceita |
| `dest_razao_social` | `destinatario.nome` | |
| `dest_uf` | `destinatario.uf` | |
| `icms_valor` | `totais.normalizado_valor_icms` | |
| `icms_st_valor` | `totais.normalizado_valor_icms_substituicao` | |
| `pis_valor` | `totais.normalizado_valor_pis` | |
| `cofins_valor` | `totais.normalizado_valor_cofins` | |
| `ipi_valor` | `totais.normalizado_valor_ipi` | |
| `tributos_total` | `totais.normalizado_valor_tributos` | |
| `payload` | Todo o `data[0]` | JSONB completo |

### `efd_notas_itens` (via `produtos[]`)

| Campo `efd_notas_itens` | Campo InfoSimples `produtos[i]` | Notas |
|---|---|---|
| `numero_item` | `num` | |
| `codigo_item` | `codigo` | |
| `descricao` | `descricao` | |
| `quantidade` | `qtd` | Float |
| `unidade_medida` | `unidade` | |
| `valor_unitario` | Parse de `valor_unitario_comercial` | Formato BR, converter |
| `valor_total` | `normalizado_valor` | Float |
| `cfop` | `cfop` | Ex: `5405` |
| `cst_icms` | `icms.tributacao_icms` (código numérico) | Ex: `60` |
| `aliquota_icms` | `icms.aliquota` | Vazio quando ST |
| `valor_icms` | `icms.valor` | Vazio quando ST |
| `cst_pis` | `pis.cst` (código numérico) | Ex: `01` |
| `aliquota_pis` | `pis.aliquota` | |
| `valor_pis` | `pis.valor` | |
| `cst_cofins` | `cofins.cst` (código numérico) | |
| `aliquota_cofins` | `cofins.aliquota` | |
| `valor_cofins` | `cofins.valor` | |

### `participantes` (emitente e destinatário)

| Campo `participantes` | InfoSimples (emitente) | InfoSimples (destinatário) |
|---|---|---|
| `cnpj` | `emitente.normalizado_cnpj` | `destinatario.normalizado_cnpj` ou `normalizado_cpf` |
| `razao_social` | `emitente.nome` | `destinatario.nome` |
| `nome_fantasia` | `emitente.nome_fantasia` | — |
| `uf` | `emitente.uf` | `destinatario.uf` |
| `inscricao_estadual` | `emitente.ie` | `destinatario.ie` |
| `crt` | `emitente.normalizado_regime` | — |
| `endereco` | `emitente.endereco` | `destinatario.endereco` |
| `bairro` | `emitente.bairro` | `destinatario.bairro` |
| `cep` | `emitente.normalizado_cep` | `destinatario.normalizado_cep` |
| `municipio` | `emitente.normalizado_municipio` | `destinatario.normalizado_municipio` |
| `telefone` | `emitente.telefone` | `destinatario.telefone` |

## Pontos de Atenção

1. **CPF no campo `dest_cnpj`** — Destinatário pode ser PF (CPF 11 chars). O campo `varchar(14)` aceita, mas mistura semântica. Tratar no n8n: se `normalizado_cnpj` vazio, usar `normalizado_cpf`.

2. **Valores com vírgula** — Muitos campos vêm formatados BR (`"808,72"`). Sempre usar campos `normalizado_*` que já são float.

3. **Associação com cliente** — `cliente_id` vem da escolha manual do usuário na busca avulsa. Se vazio, salvar a nota sem cliente direto. Se preenchido, validar no Laravel antes do webhook e persistir em `xml_notas.cliente_id`.

4. **Situação da NF-e** — `nfe.situacao` (`AUTORIZADA`, `CANCELADA`, `DENEGADA`) é crucial para clearance mas não tem coluna dedicada. Hoje iria apenas no `payload` JSONB. Considerar adicionar `situacao_sefaz` + `verificado_sefaz_em`.

5. **Certificado digital** — A API exige `pkcs12_cert` e `pkcs12_pass` encriptados com AES-256-GCM. Precisa definir como armazenar/gerenciar certificados dos clientes.

6. **Formas de pagamento** — O array `cobranca.formas_pagamento` pode conter entradas vazias (lixo). Filtrar no parse.

## Ideias de Implementação — Clearance Fiscal

### 1. Validação de Notas na SEFAZ

Consultar chave de acesso de notas já importadas via EFD/XML e confirmar:
- **Situação** (`AUTORIZADA`, `CANCELADA`, `DENEGADA`) — alerta se divergente do EFD
- **Valores divergentes** — comparar `valor_total` SEFAZ vs EFD
- **Notas canceladas escrituradas** = risco fiscal alto

### 2. Enriquecimento de Notas EFD

O EFD traz dados limitados (C100 não tem todos os itens detalhados). A consulta InfoSimples traz:
- Produtos com NCM, CEST, EAN — enriquecer `efd_catalogo_itens`
- CST completo de ICMS, PIS, COFINS por item — cruzar com CSTs declarados
- Natureza da operação
- Informações complementares (protocolos, devoluções)

### 3. Cruzamento EFD vs NF-e SEFAZ (alertas automáticos)

| Cruzamento | Alerta |
|---|---|
| Valor EFD != Valor SEFAZ | "NF-e 123456: R$ 808,72 na SEFAZ, R$ 750,00 no EFD" |
| NF-e cancelada presente no EFD | "NF-e cancelada escriturada — retificar EFD" |
| NF-e ausente no EFD | "NF-e emitida para este CNPJ não consta no EFD" |
| CST divergente | "ICMS CST 60 na SEFAZ vs CST 00 no EFD — risco malha" |
| CFOP divergente | "CFOP 5405 na SEFAZ vs 5102 no EFD" |

### 4. Monitoramento de Notas Emitidas Contra o Cliente

Consulta periódica de notas emitidas por terceiros para o CNPJ do cliente, verificando se todas as entradas estão escrituradas. Manifestação do destinatário automatizada.

### 5. Análise Tributária por Item

Com detalhe por produto (NCM + CST + alíquotas):
- NCM vs tabela TIPI para validar alíquota IPI
- CST ICMS vs regime do emitente (Simples vs Normal)
- CEST para validar obrigatoriedade de ST
- Base de cálculo ST vs MVA oficial do estado

### 6. Colunas Sugeridas para Verificação SEFAZ

Para `xml_notas` e `efd_notas`:
- `situacao_sefaz` — `AUTORIZADA`, `CANCELADA`, `DENEGADA`
- `verificado_sefaz_em` — timestamp da última verificação

### 7. Fluxo n8n Sugerido

Consistente com a arquitetura (n8n faz tudo):
1. Laravel valida usuário, créditos, `tipo_documento`, `chave_acesso` e `cliente_id` opcional
2. Laravel envia `user_id`, `tipo_documento`, `chave_acesso` e `cliente_id` opcional ao n8n
3. n8n consulta InfoSimples em loop ou consulta unitária (rate-limited, custo pago por nota)
4. n8n faz upsert em `xml_notas` usando `user_id + nfe_id`
5. n8n grava `payload`, campos normalizados, participantes e associação opcional com cliente
6. n8n envia progresso/status via endpoint existente de consultas quando a execução for assíncrona

### 8. Estratégia de Custo (referência histórica de R$0,24/nota)

Consultar todas as notas é caro. Estratégia inteligente:
- **Automático:** notas com valor > X (ex: R$5.000) ou de participantes novos
- **Sob demanda:** botão "Verificar na SEFAZ" na tela de detalhe da nota
- **Amostragem:** verificar X% aleatório por importação para auditoria

> Observação: para decisão de MVP em `2026-04-15`, usamos o comparativo comercial mais recente entre InfoSimples e Focus, documentado no topo deste arquivo. Se os preços oficiais mudarem, este comparativo deve ser revalidado antes de qualquer implementação comercial.

## Exemplo de Chamada

```bash
curl --request POST \
  --url "https://api.infosimples.com/api/v2/consultas/receita-federal/nfe" \
  --data "nfe=11111111111111111111111111111111111111111111" \
  --data "pkcs12_cert=AES-256-GCM-ENCRYPTED-BASE64-CERTIFICATE" \
  --data "pkcs12_pass=AES-256-GCM-ENCRYPTED-BASE64-PASSWORD" \
  --data "token=INFOSIMPLES_TOKEN" \
  --data "timeout=300"
```

## Códigos de Resposta InfoSimples

Mesmos 5 grupos da CND Federal:
- **Sucesso (200/201):** dados retornados normalmente
- **Não encontrado (612):** chave de acesso inválida ou NF-e não existe
- **Erro do participante (608/611/619/620):** problema com os dados enviados
- **Temporário/retry (600/605/609/610/613/614/615/618):** tentar novamente
- **Fatal (601-607/617/621/622):** erro permanente, não retentar
