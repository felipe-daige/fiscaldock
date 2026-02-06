# Problemas Pendentes e Possíveis Erros

Documentação de issues conhecidas, problemas pendentes de solução e cenários de erro que podem ocorrer no sistema.

---

## 1. SPEDs Travados (Status Pendente/Processando Eternamente)

**Status:** 🟡 Pendente de implementação

**Descrição:**
Quando ocorre um erro silencioso no n8n (crash, timeout de rede, erro não tratado), a importação SPED pode ficar eternamente com status `pendente` ou `processando`, sem nunca receber atualização.

**Cenários que causam o problema:**

| Cenário | Status resultante | n8n consegue avisar? |
|---------|-------------------|----------------------|
| Webhook não responde (n8n caiu) | `pendente` | ❌ Não |
| n8n processa mas não envia progresso | `processando` | ❌ Não |
| n8n trava no meio do processamento | `processando` | ❌ Não |
| Erro silencioso/não tratado no n8n | `pendente` ou `processando` | ❌ Não |
| Laravel/API fora do ar durante callback | `processando` | ❌ Não |

**Impacto:**
- Usuário não sabe se deve esperar ou reenviar
- Registros "fantasmas" no banco
- Confusão na lista de importações

**Solução proposta (não implementada):**

### Opção A: Timeout automático via Laravel Scheduler

```php
// app/Console/Commands/TimeoutImportacoesSped.php
class TimeoutImportacoesSped extends Command
{
    protected $signature = 'importacoes:timeout';

    public function handle()
    {
        // Marca como timeout: processando há mais de 30 min
        ImportacaoSped::where('status', 'processando')
            ->where('iniciado_em', '<', now()->subMinutes(30))
            ->update([
                'status' => 'timeout',
                'error_code' => 'PROCESSING_TIMEOUT',
                'error_message' => 'Processamento excedeu o tempo limite de 30 minutos',
            ]);

        // Marca como timeout: pendente há mais de 10 min
        ImportacaoSped::where('status', 'pendente')
            ->where('created_at', '<', now()->subMinutes(10))
            ->update([
                'status' => 'timeout',
                'error_code' => 'PENDING_TIMEOUT',
                'error_message' => 'Importação não iniciou em 10 minutos',
            ]);
    }
}
```

```php
// app/Console/Kernel.php
$schedule->command('importacoes:timeout')->everyFiveMinutes();
```

### Opção B: Retry manual pelo usuário

Adicionar botão "Tentar Novamente" na lista de importações para status `erro` ou `timeout`.

### Opção C: Garantir envio de erro no n8n

Envolver todo o workflow em try/catch global que SEMPRE envia status de erro.

**Recomendação:** Implementar Opção A + B combinadas.

**Arquivos relacionados:**
- `app/Models/ImportacaoSped.php`
- `app/Http/Controllers/SpedUploadController.php`
- `resources/views/autenticado/monitoramento/sped.blade.php`

---

## 2. [Template para novos problemas]

**Status:** 🔴 Crítico | 🟡 Pendente | 🟢 Resolvido

**Descrição:**
[Descrever o problema]

**Como reproduzir:**
1. [Passo 1]
2. [Passo 2]

**Impacto:**
- [Impacto 1]
- [Impacto 2]

**Solução proposta:**
[Descrever solução]

**Arquivos relacionados:**
- [arquivo1.php]
- [arquivo2.blade.php]

---

## Legenda de Status

| Ícone | Significado |
|-------|-------------|
| 🔴 | Crítico - precisa resolver urgente |
| 🟡 | Pendente - pode esperar mas precisa resolver |
| 🟢 | Resolvido - documentado para referência |
| 🔵 | Em andamento - sendo trabalhado |

---

## Histórico

| Data | Problema | Ação |
|------|----------|------|
| 2026-01-31 | SPEDs travados | Documentado, aguardando implementação |

---

**Última atualização:** 2026-01-31
