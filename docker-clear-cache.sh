#!/bin/bash
# Script para limpar cache do Laravel dentro do container Docker

echo "Limpando cache do Laravel no container Docker..."

# Verificar se o container está rodando
if ! docker-compose ps | grep -q "app.*Up"; then
    echo "Erro: Container 'app' não está rodando!"
    echo "Execute: docker-compose up -d"
    exit 1
fi

# Limpar cache de rotas
echo "Limpando cache de rotas..."
docker-compose exec app php artisan route:clear

# Limpar cache de configuração
echo "Limpando cache de configuração..."
docker-compose exec app php artisan config:clear

# Limpar cache de aplicação
echo "Limpando cache de aplicação..."
docker-compose exec app php artisan cache:clear

# Limpar cache de views
echo "Limpando cache de views..."
docker-compose exec app php artisan view:clear

# Limpar todos os caches
echo "Limpando todos os caches..."
docker-compose exec app php artisan optimize:clear

echo ""
echo "✅ Cache limpo com sucesso!"
echo ""
echo "Se o problema persistir, tente reiniciar o container:"
echo "  docker-compose restart app"


