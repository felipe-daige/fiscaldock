# Instruções de Build e Deploy - FiscalDock

Este documento contém as instruções completas para fazer o build da imagem Docker e fazer o deploy da aplicação Laravel no Docker Swarm com Traefik.

## Pré-requisitos

- Docker e Docker Swarm configurados na VPS
- Portainer instalado e configurado
- Traefik rodando como proxy reverso na rede `traefik`
- Postgres e Redis rodando na rede `network_public`
- Acesso SSH à VPS

## 1. Build da Imagem Docker

Como o Docker Swarm não suporta a diretiva `build:` no `docker-compose.yml`, você precisa buildar a imagem localmente na VPS antes de fazer o deploy.

### Comando de Build

Na VPS, dentro da pasta do projeto:

```bash
# Build da imagem
docker build -t fiscaldock-app:latest -f Dockerfile .

# Opcional: taguear com versão para facilitar rollback
docker tag fiscaldock-app:latest fiscaldock-app:1.0.0
```

### Verificar a Imagem

```bash
# Listar imagens
docker images | grep fiscaldock-app

# Testar a imagem localmente (opcional)
docker run --rm fiscaldock-app:latest php -v
```

## 2. Configurar Variáveis de Ambiente

Antes de fazer o deploy, configure as variáveis de ambiente no Portainer ou crie um arquivo `.env` na VPS.

### Variáveis Obrigatórias

```env
# Aplicação
APP_KEY=base64:...  # Gerar com: php artisan key:generate --show
APP_ENV=production
APP_DEBUG=false
APP_URL=https://fiscaldock.com.br

# Banco de Dados
DB_CONNECTION=pgsql
DB_HOST=postgres  # Nome do serviço Postgres na rede network_public
DB_PORT=5432
DB_DATABASE=seu_banco
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

# Proxy & Security
TRUSTED_PROXIES=*
TRUSTED_HOSTS=fiscaldock.com.br,www.fiscaldock.com.br
```

### Variáveis Opcionais

```env
# Cache & Session
CACHE_STORE=database  # ou 'redis' para melhor performance
SESSION_DRIVER=database  # ou 'redis'
QUEUE_CONNECTION=sync  # ou 'redis' para filas assíncronas

# Redis (se usar CACHE_STORE=redis ou QUEUE_CONNECTION=redis)
REDIS_HOST=redis  # Nome do serviço Redis na rede network_public
REDIS_PORT=6379
REDIS_PASSWORD=  # Deixe vazio se não usar senha

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=info
```

## 3. Deploy no Portainer

### 3.1. Criar Stack no Portainer

1. Acesse o Portainer
2. Vá em **Stacks** > **Add stack**
3. Nome da stack: `fiscaldock` (ou outro nome de sua preferência)
4. Cole o conteúdo do arquivo `docker-compose.yml`
5. Configure as variáveis de ambiente:
   - Opção 1: Use o editor de variáveis do Portainer
   - Opção 2: Carregue um arquivo `.env` (se disponível)
6. Clique em **Deploy the stack**

### 3.2. Verificar Redes Docker

Certifique-se de que as redes existem:

```bash
# Verificar rede Traefik
docker network ls | grep traefik

# Verificar rede network_public
docker network ls | grep network_public

# Se não existirem, criar (ajuste os nomes conforme necessário)
docker network create --driver overlay traefik
docker network create --driver overlay network_public
```

## 4. Verificação Pós-Deploy

### 4.1. Verificar Status dos Serviços

No Portainer, verifique se os serviços estão rodando:

- `fiscaldock_app` - Status: Running
- `fiscaldock_scheduler` - Status: Running
- `fiscaldock_worker` - Status: Running (ou 0 replicas se desabilitado)

### 4.2. Verificar Logs

```bash
# Logs do serviço principal
docker service logs fiscaldock_app --tail 50

# Logs do scheduler
docker service logs fiscaldock_scheduler --tail 50

# Logs do worker (se habilitado)
docker service logs fiscaldock_worker --tail 50
```

Ou via Portainer: clique no serviço > **Logs**

### 4.3. Testar Aplicação

1. Acesse `https://fiscaldock.com.br` no navegador
2. Verifique se o redirecionamento `www.fiscaldock.com.br` → `fiscaldock.com.br` funciona
3. Teste rotas autenticadas
4. Verifique se as migrações foram executadas (verifique no banco de dados)

### 4.4. Verificar Certificados SSL

Os certificados Let's Encrypt devem ser gerados automaticamente pelo Traefik. Verifique no dashboard do Traefik ou nos logs:

```bash
docker service logs traefik_traefik --tail 100 | grep -i cert
```

## 5. Comandos Úteis

### Executar Comandos Artisan

```bash
# Via Portainer: Console do serviço app
docker service ps fiscaldock_app
docker exec -it <container_id> php artisan <comando>

# Exemplos:
docker exec -it <container_id> php artisan migrate:status
docker exec -it <container_id> php artisan config:clear
docker exec -it <container_id> php artisan route:list
```

### Limpar Caches

```bash
docker exec -it <container_id> php artisan config:clear
docker exec -it <container_id> php artisan route:clear
docker exec -it <container_id> php artisan view:clear
```

### Recriar Caches

```bash
docker exec -it <container_id> php artisan config:cache
docker exec -it <container_id> php artisan route:cache
docker exec -it <container_id> php artisan view:cache
```

## 6. Atualização da Aplicação

### 6.1. Processo de Atualização

1. **Fazer backup do banco de dados** (importante!)
2. Fazer pull das alterações no código
3. Buildar nova imagem:
   ```bash
   docker build -t fiscaldock-app:latest -f Dockerfile .
   docker tag fiscaldock-app:latest fiscaldock-app:1.1.0  # Versão nova
   ```
4. No Portainer, atualizar a stack:
   - Edite a stack
   - Mantenha o `docker-compose.yml` (ou atualize se necessário)
   - Clique em **Update the stack**
5. O Swarm irá fazer rolling update automaticamente

### 6.2. Rollback

Se algo der errado:

1. No Portainer, edite a stack
2. Altere a imagem no `docker-compose.yml`:
   ```yaml
   image: fiscaldock-app:1.0.0  # Versão anterior
   ```
3. Atualize a stack

## 7. Troubleshooting

### Problema: Container não inicia

- Verifique os logs: `docker service logs fiscaldock_app`
- Verifique se as variáveis de ambiente estão corretas
- Verifique se o Postgres está acessível: `docker exec -it <container_id> php -r "new PDO('pgsql:host=postgres', 'user', 'pass');"`

### Problema: Erro 502 Bad Gateway

- Verifique se o Traefik está rodando
- Verifique se o serviço está na rede `traefik`
- Verifique os logs do Traefik: `docker service logs traefik_traefik`

### Problema: Migrações não executam

- Verifique as permissões do storage: `docker exec -it <container_id> ls -la /var/www/html/storage`
- Execute manualmente: `docker exec -it <container_id> php artisan migrate --force`

### Problema: Certificado SSL não é gerado

- Verifique se o domínio aponta para o IP da VPS
- Verifique os logs do Traefik
- Verifique se o `certresolver=letsencryptresolver` está correto no Traefik

### Problema: Redirecionamento www não funciona

- Verifique se o middleware está configurado corretamente nos labels
- Verifique se ambos os routers estão configurados
- Verifique os logs do Traefik

## 8. Estrutura de Arquivos

```
.
├── Dockerfile                 # Dockerfile monolítico (PHP + Nginx + Supervisor)
├── docker-compose.yml         # Stack para Docker Swarm
├── docker-entrypoint.sh      # Script de inicialização
├── deployment/
│   ├── nginx.conf            # Configuração Nginx
│   └── supervisord.conf      # Configuração Supervisor
└── BUILD.md                  # Este arquivo
```

## 9. Notas Importantes

- O container roda Nginx e PHP-FPM no mesmo processo (via Supervisor)
- Storage é persistido via volume Docker (`app_storage`)
- Migrações rodam automaticamente no startup (com `--force`)
- Caches são gerados automaticamente no startup
- Middleware Traefik redireciona www para non-www (301 permanente)
- Ambos os domínios (com e sem www) têm certificado SSL via Let's Encrypt
- O worker está desabilitado por padrão (replicas: 0). Habilite apenas se usar filas Redis

## 10. Suporte

Para problemas ou dúvidas, consulte:
- Logs dos serviços no Portainer
- Documentação do Laravel: https://laravel.com/docs
- Documentação do Traefik: https://doc.traefik.io/traefik/
- Documentação do Docker Swarm: https://docs.docker.com/engine/swarm/
