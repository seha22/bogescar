#!/usr/bin/env bash

set -euo pipefail

BRANCH="${1:-main}"
APP_DIR="${2:-$(pwd)}"
COMPOSE_FILE="docker-compose.prod.yml"

cd "$APP_DIR"

if [[ ! -f "$COMPOSE_FILE" ]]; then
  echo "Missing $COMPOSE_FILE in $APP_DIR"
  exit 1
fi

echo "Deploy branch: $BRANCH"
echo "App directory: $APP_DIR"

git fetch --all --prune
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

docker compose -f "$COMPOSE_FILE" up -d --build --remove-orphans
docker compose -f "$COMPOSE_FILE" exec -T app php artisan migrate --force
docker compose -f "$COMPOSE_FILE" exec -T app php artisan storage:link || true
docker compose -f "$COMPOSE_FILE" exec -T app php artisan optimize
docker compose -f "$COMPOSE_FILE" exec -T app php artisan queue:restart || true

docker compose -f "$COMPOSE_FILE" ps
