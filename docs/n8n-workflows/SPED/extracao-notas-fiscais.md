# Extração de Notas Fiscais do SPED

Guia de implementação da funcionalidade de extração de notas fiscais (NF-e, NFSe) dos arquivos SPED para análise de BI Fiscal.

---

## Visão Geral

### Objetivo
Permitir que o usuário, durante a importação de arquivos SPED, opte por também extrair as notas fiscais contidas no arquivo para alimentar o módulo de BI Fiscal.

### Fluxo
1. Usuário seleciona tipo SPED
2. Usuário marca checkbox "Extrair Notas Fiscais" (opcional)
3. Usuário faz upload do arquivo
4. Laravel envia para n8n com `extrair_notas=true/false`
5. n8n:
   - Extrai participantes (registro |0150|)
   - SE `extrair_notas=true`: extrai notas (registros C100, A100)
   - INSERT em `notas_sped`
   - UPDATE `importacoes_sped` com totais
   - Envia progresso final com `dados.notas_extraidas`
6. Frontend exibe resultado com quantidade de notas

---

## Registros SPED Relevantes

### EFD Fiscal (ICMS/IPI)

| Registro | Descrição | Campos Principais |
|----------|-----------|-------------------|
| **C100** | Documento fiscal (NF-e, NFC-e) | IND_OPER, COD_PART, CHV_NFE, VL_DOC |
| C170 | Itens do documento | COD_ITEM, VL_ITEM, VL_ICMS |

### EFD Contribuições (PIS/COFINS)

| Registro | Descrição | Campos Principais |
|----------|-----------|-------------------|
| **A100** | Documento de serviço (NFSe) | IND_OPER, COD_PART, VL_DOC |
| **C100** | Documento fiscal de mercadoria | IND_OPER, COD_PART, CHV_NFE, VL_DOC |
| C170 | Itens do documento | COD_ITEM, VL_ITEM |

---

## Webhook Payload

### Laravel → n8n

```json
{
  "user_id": 1,
  "importacao_id": 123,
  "tab_id": "uuid",
  "tipo_efd": "EFD_FISCAL",
  "cliente_id": 10,
  "extrair_notas": true,
  "filename": "SPED_EFD_2024.txt",
  "progress_url": "https://fiscaldock.com.br/api/monitoramento/sped/importacao-txt/progress"
}
```

**Campo novo:** `extrair_notas` (boolean) - indica se deve extrair notas fiscais além dos participantes.

---

## Code Node - Extração de Notas

Adicionar este Code Node **após** a extração de participantes e **antes** do INSERT no banco:

```javascript
// Extrair Notas Fiscais do SPED
const items = $input.all();
const data = items[0].json;

// Só processar se extrair_notas = true
if (!data.extrair_notas) {
    return [{ json: { ...data, notas: [], total_notas: 0 } }];
}

const lines = data.sped_lines || [];
const tipoEfd = data.tipo_efd;
const notas = [];

// Mapear códigos de participantes para IDs
const participantesPorCodigo = {};
if (data.participantes_processados) {
    data.participantes_processados.forEach(p => {
        if (p.cod_part) {
            participantesPorCodigo[p.cod_part] = p.id;
        }
    });
}

for (const line of lines) {
    const campos = line.split('|').filter(c => c !== '');

    if (campos[0] === 'C100') {
        // Registro C100 - Documento Fiscal
        // Formato: |C100|IND_OPER|IND_EMIT|COD_PART|COD_MOD|COD_SIT|SER|NUM_DOC|CHV_NFE|DT_DOC|DT_E_S|VL_DOC|...

        const codPart = campos[3] || null;
        const participanteId = codPart ? participantesPorCodigo[codPart] : null;

        // Converter data DDMMAAAA para YYYY-MM-DD
        function parseDataSped(dataStr) {
            if (!dataStr || dataStr.length !== 8) return null;
            const dia = dataStr.substring(0, 2);
            const mes = dataStr.substring(2, 4);
            const ano = dataStr.substring(4, 8);
            return `${ano}-${mes}-${dia}`;
        }

        const nota = {
            registro: 'C100',
            tipo_nota: parseInt(campos[1]) || 0,        // 0=entrada, 1=saída
            ind_emit: parseInt(campos[2]) || 0,         // 0=própria, 1=terceiros
            cod_part: codPart,
            participante_id: participanteId,
            modelo_doc: campos[4] || '55',              // 55=NFe, 65=NFCe
            situacao: campos[5] || '00',                // 00=regular
            serie: campos[6] || '',
            numero_nota: campos[7] || '',
            nfe_id: campos[8] || null,            // 44 dígitos
            data_emissao: parseDataSped(campos[9]),
            data_entrada_saida: parseDataSped(campos[10]),
            valor_total: parseFloat((campos[11] || '0').replace(',', '.')) || 0,
            // Tributos (posições podem variar dependendo da versão do leiaute)
            valor_icms: parseFloat((campos[21] || '0').replace(',', '.')) || 0,
            valor_icms_st: parseFloat((campos[23] || '0').replace(',', '.')) || 0,
            valor_ipi: parseFloat((campos[25] || '0').replace(',', '.')) || 0,
        };

        // Só adicionar se tiver dados mínimos
        if (nota.numero_nota || nota.nfe_id) {
            notas.push(nota);
        }
    }

    // Para EFD Contribuições, também capturar A100 (serviços)
    if (tipoEfd === 'EFD_CONTRIB' && campos[0] === 'A100') {
        // Registro A100 - Documento de Serviço
        // Formato: |A100|IND_OPER|IND_EMIT|COD_PART|COD_SIT|SER|SUB|NUM_DOC|CHV_NFSE|DT_DOC|DT_EXE_SERV|VL_DOC|...

        const codPart = campos[3] || null;
        const participanteId = codPart ? participantesPorCodigo[codPart] : null;

        function parseDataSped(dataStr) {
            if (!dataStr || dataStr.length !== 8) return null;
            const dia = dataStr.substring(0, 2);
            const mes = dataStr.substring(2, 4);
            const ano = dataStr.substring(4, 8);
            return `${ano}-${mes}-${dia}`;
        }

        const nota = {
            registro: 'A100',
            tipo_nota: parseInt(campos[1]) || 0,
            ind_emit: parseInt(campos[2]) || 0,
            cod_part: codPart,
            participante_id: participanteId,
            modelo_doc: 'SE',  // Serviço
            situacao: campos[4] || '00',
            serie: campos[5] || '',
            numero_nota: campos[7] || '',
            nfe_id: campos[8] || null,  // CHV_NFSE
            data_emissao: parseDataSped(campos[9]),
            data_entrada_saida: parseDataSped(campos[10]),
            valor_total: parseFloat((campos[11] || '0').replace(',', '.')) || 0,
            valor_icms: 0,
            valor_icms_st: 0,
            valor_ipi: 0,
        };

        if (nota.numero_nota || nota.nfe_id) {
            notas.push(nota);
        }
    }
}

return [{
    json: {
        ...data,
        notas,
        total_notas: notas.length
    }
}];
```

---

## SQL - INSERT notas_sped

### PostgreSQL Node Configuration

```sql
INSERT INTO notas_sped (
    user_id,
    cliente_id,
    importacao_sped_id,
    emit_participante_id,
    dest_participante_id,
    tipo_efd,
    registro,
    tipo_nota,
    modelo_doc,
    serie,
    numero_nota,
    nfe_id,
    data_emissao,
    data_entrada_saida,
    valor_total,
    valor_icms,
    valor_icms_st,
    valor_ipi,
    valor_pis,
    valor_cofins,
    cfop_principal,
    created_at,
    updated_at
) VALUES (
    $1,   -- user_id
    $2,   -- cliente_id (pode ser null)
    $3,   -- importacao_sped_id
    $4,   -- emit_participante_id (se tipo_nota=1)
    $5,   -- dest_participante_id (se tipo_nota=0)
    $6,   -- tipo_efd
    $7,   -- registro (C100, A100)
    $8,   -- tipo_nota (0=entrada, 1=saída)
    $9,   -- modelo_doc
    $10,  -- serie
    $11,  -- numero_nota
    $12,  -- nfe_id
    $13,  -- data_emissao
    $14,  -- data_entrada_saida
    $15,  -- valor_total
    $16,  -- valor_icms
    $17,  -- valor_icms_st
    $18,  -- valor_ipi
    $19,  -- valor_pis (0 por enquanto)
    $20,  -- valor_cofins (0 por enquanto)
    NULL, -- cfop_principal (extrair de C170 se necessário)
    NOW(),
    NOW()
)
ON CONFLICT (user_id, importacao_sped_id, nfe_id)
WHERE nfe_id IS NOT NULL
DO UPDATE SET
    updated_at = NOW()
RETURNING id;
```

**Nota:** Para evitar duplicatas de notas com mesma chave de acesso, é necessário criar um índice único parcial:

```sql
CREATE UNIQUE INDEX notas_sped_unique_chave
ON notas_sped (user_id, importacao_sped_id, nfe_id)
WHERE nfe_id IS NOT NULL;
```

---

## SQL - UPDATE importacoes_sped

Atualizar registro da importação ao final do processamento:

```sql
UPDATE importacoes_sped SET
    status = 'concluido',
    total_participantes = {{ $json.total_participantes }},
    novos = {{ $json.novos }},
    duplicados = {{ $json.duplicados }},
    participante_ids = {{ JSON.stringify($json.participante_ids || []) }}::jsonb,
    total_notas = {{ $json.total_notas || 0 }},
    notas_extraidas = {{ $json.notas_inseridas || 0 }},
    concluido_em = NOW()
WHERE id = {{ $json.importacao_id }};
```

---

## Payload de Progresso

### Status Final (n8n → Laravel)

```json
{
    "user_id": 1,
    "tab_id": "uuid",
    "progresso": 100,
    "status": "concluido",
    "mensagem": "Processamento concluído!",
    "dados": {
        "total_participantes": 150,
        "novos_salvos": 30,
        "duplicados_identificados": 120,
        "importacao_id": 123,
        "participante_lita_geral_ids": [1, 2, 3],
        "participante_novos_ids": [1, 2, 3],
        "participante_repetido_ids": [4, 5, 6],
        "total_notas": 500,
        "notas_extraidas": 500
    }
}
```

**Campos novos:**
- `total_notas`: Quantidade de notas encontradas no arquivo
- `notas_extraidas`: Quantidade de notas efetivamente inseridas no banco

---

## Fluxo Completo do Workflow

```
WEBHOOK (SPED file)
    │
    ▼
VALIDAR TIPO ARQUIVO
    │
    ▼
EXTRAIR PARTICIPANTES (0150)
    │
    ▼
UPSERT participantes
    │
    ▼
IF extrair_notas = true ─────────┐
    │                            │
    ▼                            │
EXTRAIR NOTAS (C100, A100)       │ (skip if false)
    │                            │
    ▼                            │
INSERT notas_sped                │
    │                            │
    ▼◄───────────────────────────┘
    │
UPDATE importacoes_sped
    │
    ▼
ENVIAR PROGRESSO 100%
```

---

## Verificação

### Teste sem extração

```bash
# Upload SPED sem marcar checkbox
# Verificar que só participantes são importados
# notas_extraidas deve ser 0

SELECT id, status, total_participantes, novos, duplicados,
       extrair_notas, total_notas, notas_extraidas
FROM importacoes_sped
WHERE id = ?;
```

### Teste com extração

```bash
# Upload SPED marcando checkbox
# Verificar que participantes E notas são importados
# UI deve mostrar "X notas fiscais extraídas"

SELECT count(*) as total_notas FROM notas_sped
WHERE importacao_sped_id = ?;

SELECT * FROM notas_sped
WHERE importacao_sped_id = ?
LIMIT 5;
```

### Verificar relacionamentos

```sql
SELECT
    ns.id,
    ns.numero_nota,
    ns.nfe_id,
    ns.valor_total,
    ns.tipo_nota,
    p.razao_social as participante
FROM notas_sped ns
LEFT JOIN participantes p ON
    CASE
        WHEN ns.tipo_nota = 0 THEN ns.emit_participante_id = p.id
        ELSE ns.dest_participante_id = p.id
    END
WHERE ns.importacao_sped_id = ?
LIMIT 10;
```

---

## Precificação (MVP)

| Camada | Custo |
|--------|-------|
| Extração básica | **GRATUITO** |

**Nota:** Após validação do MVP com dados reais de uso, será definida precificação baseada em:
- Quantidade de notas por arquivo
- Frequência de importações
- Volume mensal de notas

---

## Próximos Passos

1. [ ] Implementar Code Node no n8n
2. [ ] Criar índice único parcial para `nfe_id`
3. [ ] Testar com arquivos EFD Fiscal reais
4. [ ] Testar com arquivos EFD Contribuições reais
5. [ ] Implementar VCI nas notas SPED
6. [ ] Criar visualizações no BI Dashboard

---

## Changelog

### 2026-01-31 - v1.0
- Implementação inicial da extração de notas
- Suporte a registros C100 (NFe) e A100 (NFSe)
- Integração com frontend (checkbox + exibição de resultados)
- Documentação completa

---

**Última Revisão:** 2026-01-31
**Autor:** FiscalDock Development Team
