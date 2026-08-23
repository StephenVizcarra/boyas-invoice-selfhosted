#!/bin/bash
#
# Builds a fresh Docker image from source, starts a container with clean
# volumes, verifies the main page and API endpoints return 200, then tears
# everything down. Run this before pushing Docker-related changes.
#
set -euo pipefail

PROJECT="boyas_smoke_test"
CONTAINER="boyas_smoke_test"
PORT=9090
COMPOSE_FILE="$(mktemp)"
REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"

cd "$REPO_ROOT"

trap 'docker compose -p "$PROJECT" -f "$COMPOSE_FILE" down -v >/dev/null 2>&1; rm -f "$COMPOSE_FILE"' EXIT

cat > "$COMPOSE_FILE" <<YAML
services:
  app:
    build:
      context: $REPO_ROOT
      dockerfile: Dockerfile
    container_name: $CONTAINER
    ports:
      - "$PORT:80"
    volumes:
      - ${PROJECT}_storage:/var/www/html/storage/app
      - ${PROJECT}_database:/var/www/html/database
    environment:
      APP_KEY: ""
      APP_NAME: "Boyas Invoice"
      APP_URL: "http://localhost:$PORT"

volumes:
  ${PROJECT}_storage:
    driver: local
  ${PROJECT}_database:
    driver: local
YAML

echo ""
echo "  Docker Smoke Test"
echo "  ======================================"
echo ""

# --- Build and start ---
printf "  Building image..."
if ! docker compose -p "$PROJECT" -f "$COMPOSE_FILE" build --quiet 2>&1; then
    echo " FAILED"
    echo "  Image build failed."
    exit 1
fi
echo " done."

printf "  Starting container..."
docker compose -p "$PROJECT" -f "$COMPOSE_FILE" up -d >/dev/null 2>&1
echo " done."

# --- Wait for healthy ---
printf "  Waiting for app to be ready"
ready=false
for i in $(seq 1 30); do
    sleep 2
    if curl -sf "http://localhost:$PORT/up" >/dev/null 2>&1; then
        ready=true
        break
    fi
    printf "."
done

if ! $ready; then
    echo " FAILED"
    echo ""
    echo "  Container did not become healthy. Logs:"
    echo "  --------------------------------------"
    docker logs "$CONTAINER" 2>&1 | tail -30
    exit 1
fi
echo " ready."

# --- Test endpoints ---
echo ""
PASS=0
FAIL=0

check() {
    local label="$1"
    local url="$2"
    local code
    code=$(curl -s -o /dev/null -w "%{http_code}" "$url")
    if [ "$code" = "200" ]; then
        echo "  PASS  $label ($code)"
        PASS=$((PASS + 1))
    else
        echo "  FAIL  $label ($code)"
        FAIL=$((FAIL + 1))
    fi
}

check "GET  /"               "http://localhost:$PORT/"
check "GET  /up"             "http://localhost:$PORT/up"
check "GET  /api/sender"     "http://localhost:$PORT/api/sender"
check "GET  /api/recipients" "http://localhost:$PORT/api/recipients"
check "GET  /api/invoices"   "http://localhost:$PORT/api/invoices"

# --- Summary ---
echo ""
echo "  --------------------------------------"
if [ "$FAIL" -eq 0 ]; then
    echo "  All $PASS checks passed."
    echo ""
    exit 0
else
    echo "  $FAIL of $((PASS + FAIL)) checks failed."
    echo ""
    echo "  Container logs:"
    echo "  --------------------------------------"
    docker logs "$CONTAINER" 2>&1 | tail -30
    exit 1
fi
