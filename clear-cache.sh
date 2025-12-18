#!/usr/bin/env bash

# Limpa OPcache e caches do Laravel no container fiscaldock-app
# Detecta o primeiro container que usa a imagem fiscaldock-app:latest

set -euo pipefail

# Permite informar o container como primeiro argumento (opcional)
CONTAINER_ID="${1:-}"

if [[ -z "$CONTAINER_ID" ]]; then
    CONTAINER_ID="$(docker ps --filter "ancestor=fiscaldock-app:latest" --format '{{.ID}}' | head -n1)"
fi

if [[ -z "$CONTAINER_ID" ]]; then
    echo "Nenhum container com a imagem fiscaldock-app:latest encontrado."
    echo "Use: $0 <container_id> para especificar manualmente."
    exit 1
fi

echo "Usando container: $CONTAINER_ID"

echo "→ Limpando OPcache"
docker exec "$CONTAINER_ID" php -r "opcache_reset();"

echo "→ Limpando cache de views"
docker exec "$CONTAINER_ID" php artisan view:clear

echo "→ Limpando cache de configuração"
docker exec "$CONTAINER_ID" php artisan config:clear

echo "→ Limpando cache de aplicação"
docker exec "$CONTAINER_ID" php artisan cache:clear

echo "✓ Limpeza concluída."
