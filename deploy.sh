#!/bin/bash
set -e
echo "Deploying FiscalDock..."
docker compose pull
docker compose up -d
echo "Deploy complete!"
