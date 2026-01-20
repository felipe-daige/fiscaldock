# DEBUG: Dados SSE Chegam Vazios no Frontend

## Problema

Ao concluir importação SPED, os cards de resultado mostram todos 0 e lista de participantes não carrega.

## Status da Investigação

### ✅ O que FUNCIONA

1. **n8n envia dados corretamente** - Log do Laravel confirma:
```json
"body":{"user_id":2,"tab_id":"xxx","progresso":100,"status":"concluido",
  "dados":{"total_cnpjs":24,"total_cpfs":11,"novos_salvos":24,"importacao_id":134,"participante_ids":["1027","1028",...]}}
```

2. **API recebe e valida** - Retorna 200 OK

3. **Frontend conecta ao SSE** - Recebe eventos corretamente

### ❌ O que NÃO FUNCIONA

**O campo `dados` não está sendo salvo no cache ou não está sendo lido pelo SSE.**

Console do navegador mostra:
```
[Monitoramento SPED] Status concluido - dadosN8n: {}  ← VAZIO!
```

Log do Laravel mostra `has_dados` não aparece (deveria ser `true`):
```
"has_error":false}  ← falta has_dados:true
```

## Arquivos Envolvidos

| Arquivo | Função |
|---------|--------|
| `app/Http/Controllers/Api/DataReceiverController.php` | Recebe payload do n8n, salva no cache (linha ~1695) |
| `app/Http/Controllers/Dashboard/MonitoramentoController.php` | SSE lê do cache e envia ao frontend |
| `resources/views/autenticado/monitoramento/sped.blade.php` | Frontend processa SSE e exibe dados |

## Hipóteses a Testar

### 1. Validação Laravel descartando `dados`

**Código atual (linha ~1705):**
```php
'dados' => 'nullable|array',
'dados.*' => 'nullable',
```

**Testar:** Adicionar log ANTES da validação para ver se `$request->input('dados')` tem valor.

### 2. Cache não salvando `dados`

**Código (linha ~1732):**
```php
if (!empty($validated['dados'])) {
    $cacheData['dados'] = $validated['dados'];
}
```

**Testar:** Verificar se `$validated['dados']` está vazio após validação.

### 3. SSE lendo cache antes de atualizar

**Possível race condition** - SSE lê cache com dados antigos antes do novo payload ser salvo.

## Correção Sugerida (NÃO TESTADA)

Forçar inclusão de `dados` no cache mesmo se vazio:

```php
// Em DataReceiverController.php, linha ~1732
// DE:
if (!empty($validated['dados'])) {
    $cacheData['dados'] = $validated['dados'];
}

// PARA:
$cacheData['dados'] = $validated['dados'] ?? [];
```

## Logs de Debug Adicionados

Já estão no código (reiniciar container para ativar):

```php
Log::info('DEBUG handleNewProgressFormat - request raw', [
    'has_dados_key' => $request->has('dados'),
    'dados_is_array' => is_array($request->input('dados')),
    'dados_type' => gettype($request->input('dados')),
    'dados_not_empty' => !empty($request->input('dados')),
]);

Log::info('DEBUG handleNewProgressFormat - after validation', [
    'validated_has_dados' => isset($validated['dados']),
    'validated_dados_not_empty' => !empty($validated['dados'] ?? null),
]);
```

## Próximos Passos

1. **Testar novamente** - Fazer upload SPED e verificar se logs DEBUG aparecem
2. **Verificar logs:**
   ```bash
   docker compose -f docker-compose.dev.yml exec app tail -50 storage/logs/laravel.log | grep "DEBUG handleNewProgressFormat"
   ```
3. **Se `dados_not_empty: false`** → Problema na validação Laravel
4. **Se `validated_dados_not_empty: false`** → Validação está descartando dados
5. **Se ambos `true`** → Problema no cache ou SSE

## Comando Útil

```bash
# Ver logs em tempo real enquanto testa
docker compose -f docker-compose.dev.yml exec app tail -f storage/logs/laravel.log | grep -E "(DEBUG|dados|concluido)"
```

## Correções já feitas no Frontend

`sped.blade.php` - Adicionado suporte aos nomes de campos do n8n:
- `novos_salvos` (linha 1427, 1444)
- `duplicados_identificados` (linha 1430, 1449)
