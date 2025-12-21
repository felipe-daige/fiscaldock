#!/bin/bash
# Script para limpar cache do Laravel

echo "Limpando cache do Laravel..."

# Limpar cache de rotas
php artisan route:clear

# Limpar cache de configuração
php artisan config:clear

# Limpar cache de aplicação
php artisan cache:clear

# Limpar cache de views
php artisan view:clear

# Limpar cache de opcache (se disponível)
php artisan optimize:clear

echo "Cache limpo com sucesso!"
