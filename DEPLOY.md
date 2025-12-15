# Deploy Laravel com Docker + Traefik (Portainer Stack)

## 1. Variáveis de ambiente críticas (.env)

Defina no .env usado para build/deploy (ou diretamente no Portainer):

- **APP_KEY**: chave gerada com `php artisan key:generate --show`
- **APP_ENV**: `production`
- **APP_DEBUG**: `false`
- **APP_URL**: `https://hub.meudominio.com`

- **DB_CONNECTION**: `pgsql`
- **DB_HOST**: nome/DNS do container Postgres na rede `network_public` (ex: `postgres`)
- **DB_PORT**: `5432`
- **DB_DATABASE**
- **DB_USERNAME**
- **DB_PASSWORD**

- **CACHE_STORE**: `database` (padrão simples)
- **SESSION_DRIVER**: `database` (padrão simples)
- **QUEUE_CONNECTION**: `sync` (tudo executa na hora, sem worker)

> Opcional (performance futura): trocar `CACHE_STORE` / `SESSION_DRIVER` / `QUEUE_CONNECTION` para `redis` e ajustar `REDIS_*`.

- **REDIS_HOST**: nome/DNS do serviço Redis na rede `network_public`. Em Docker Swarm, geralmente funciona com `redis` (nome do serviço). Se não funcionar, tente `redis_redis` (formato `stack_servicename`)
- **REDIS_PORT**: `6379`
- **REDIS_PASSWORD**: se houver

- **TRUSTED_PROXIES**: normalmente `*` atrás do Traefik, ou faixa IP do Traefik
- **TRUSTED_HOSTS**: `hub.meudominio.com`

Outras recomendadas:

- **LOG_CHANNEL**: `stack`
- **LOG_LEVEL**: `info` (ou `warning` em produção)

---

## 2. Ajuste de Trusted Proxies (Laravel atrás do Traefik)

No middleware `App\Http\Middleware\TrustProxies` (Laravel 10+/12), ajuste para usar env:

```php
protected $proxies = '*'; // ou env('TRUSTED_PROXIES', '*');

protected $headers = \Illuminate\Http\Request::HEADER_X_FORWARDED_ALL;
```

Alternativa mais flexível:

```php
protected function getTrustedProxies()
{
    return explode(',', env('TRUSTED_PROXIES', '*'));
}
```

E garanta em `.env`:

```env
TRUSTED_PROXIES=*
TRUSTED_HOSTS=hub.meudominio.com
```

Se usar `App\Http\Middleware\TrustHosts`, configure:

```php
protected function hosts()
{
    return [
        env('TRUSTED_HOSTS', 'hub.meudominio.com'),
    ];
}
```

Assim o Laravel respeita os headers `X-Forwarded-*` enviados pelo Traefik.

---

## 3. Build das imagens

Na VPS (ou no ambiente de CI), dentro da pasta do projeto:

```bash
# PHP-FPM + Laravel
docker build -t hub_php:latest -f docker/php/Dockerfile .

# Nginx
docker build -t hub_nginx:latest -f docker/nginx/Dockerfile .
```

Opcional: taguear com versão (`hub_php:1.0.0`, `hub_nginx:1.0.0`) para facilitar rollback.

---

## 4. Deploy da Stack no Portainer

1. **Rede Traefik**:
   - Certifique-se de que a rede externa `traefik` já existe (usada pelo Traefik).
   - Certifique-se de que a rede externa `network_public` existe e tem Postgres/Redis, etc.
   - Se os nomes forem diferentes, ajuste em `docker-compose.yml`.

2. **Criar Stack**:
   - No Portainer, vá em **Stacks > Add stack**.
   - Cole o conteúdo do `docker-compose.yml`.
   - Ajuste variáveis (ou use arquivo `.env` referenciado pelo Portainer).
   - Deploy da stack.

3. **Subida dos serviços**:
   - Verifique se `hub_php` e `hub_web` estão em `Running`.
   - `hub_scheduler` deve ficar `Running`.
   - `hub_worker` só é necessário se você configurar `QUEUE_CONNECTION=redis` (filas assíncronas); caso contrário, pode remover/comentar o serviço no compose.

---

## 5. Pós-deploy (comandos artisan no container)

Use o console do Portainer (ou `docker exec`) no serviço `hub_php`:

```bash
# Dentro do container hub_php
php artisan migrate --force

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Se alterar `.env` no futuro, limpe caches antes de recriar:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
```

---

## 6. Verificação de logs e health

- **Logs Nginx** (no serviço `hub_web`):
  - Via Portainer: botão **Logs** no serviço.
  - Ou CLI: `docker logs <container_hub_web>`

- **Logs Laravel** (no serviço `hub_php`):
  - Arquivos em `storage/logs/laravel.log`.
  - Use Portainer console:
    ```bash
    tail -f storage/logs/laravel.log
    ```

- **Health básico**:
  - Acesse `https://hub.meudominio.com` no navegador.
  - Teste rotas autenticadas e uso de DB.
  - Verifique se jobs agendados rodam (ex: logs de scheduler, jobs executando).

---

## 7. Rollback básico

1. **Escolher tag anterior**:
   - Use imagens versionadas, por exemplo:
     - `hub_php:1.0.0`, `hub_nginx:1.0.0`
     - `hub_php:1.1.0`, `hub_nginx:1.1.0`

2. **Rollback**:
   - No `docker-compose.yml`, mude a imagem das services para a tag anterior:
     ```yaml
     image: hub_php:1.0.0
     image: hub_nginx:1.0.0
     ```
   - Refaça o deploy da stack no Portainer (Update stack).

3. **Banco de dados**:
   - Rollback de código é rápido; rollback de DB exige backup/restore.
   - Sempre faça backup do Postgres antes de migrar (`pg_dump` ou ferramenta do provider).

---

## 8. Permissões e usuário de execução

- O container `hub_php` roda como usuário `app` (não root), dono de `storage` e `bootstrap/cache`.
- Se você mapear volumes futuros para `storage/`:
  - Garanta UID/GID compatíveis (ex: 1000:1000) ou ajuste com:
    ```bash
    chown -R app:app storage bootstrap/cache
    chmod -R ug+rwx storage/bootstrap/cache
    ```
- Como o código está dentro da imagem (sem volume de código), risco de permissão é baixo; apenas cuide se criar volumes extras para logs ou storage.

---

## 9. Conexão com serviços internos (Postgres, Redis, n8n etc.)

- Todos os serviços internos devem estar na mesma rede Docker `network_public`.
- Use o **nome do serviço** como host no `.env`, por exemplo:
  - `DB_HOST=postgres`
  - `REDIS_HOST=redis` (ou `redis_redis` se não funcionar - formato `stack_servicename` no Docker Swarm)
  - Para falar com `n8n` ou outros, use o hostname do serviço/container na rede `network_public`.

Laravel continua como “camada fina”: envie somente IDs/metadata para n8n (futuro S3/MinIO), não payloads grandes diretamente.
