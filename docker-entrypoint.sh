#!/bin/bash
set -e

echo "=== Iniciando entrypoint da aplicação ==="

# Aguardar conexão com Postgres (opcional, usando PHP)
if [ -n "$DB_HOST" ]; then
    echo "Aguardando conexão com PostgreSQL em $DB_HOST:${DB_PORT:-5432}..."
    for i in {1..15}; do
        if php -r "try { new PDO('pgsql:host=${DB_HOST};port=${DB_PORT:-5432}', '${DB_USERNAME}', '${DB_PASSWORD}'); echo 'PostgreSQL disponível!\n'; exit(0); } catch (Exception \$e) { exit(1); }" 2>/dev/null; then
            break
        fi
        echo "Tentativa $i/15 - PostgreSQL não está disponível ainda, aguardando..."
        sleep 2
    done
fi

# Corrigir permissões do storage e bootstrap/cache
echo "Ajustando permissões..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Criar symlink de storage se não existir
if [ ! -L /var/www/html/public/storage ]; then
    echo "Criando symlink de storage..."
    php artisan storage:link || echo "Aviso: storage:link falhou ou já existe"
fi

# Executar migrações
echo "Executando migrações do banco de dados..."
php artisan migrate --force || echo "Aviso: Migrações falharam, continuando..."

# Limpar caches antigos (se existirem)
echo "Limpando caches antigos..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Gerar caches de produção
echo "Gerando caches de produção..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Caches gerados com sucesso ==="
echo "=== Iniciando Supervisor (Nginx + PHP-FPM) ==="

# Iniciar Supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
