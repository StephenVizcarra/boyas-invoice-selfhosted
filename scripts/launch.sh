#!/bin/bash

echo ""
echo "  Boyas Invoice"
echo "  --------------------------------------"
echo ""

# --- 1. Docker check ---
printf "  [1/3] Checking Docker..."

if ! docker info >/dev/null 2>&1; then
    echo " not running."
    echo ""
    echo "  Starting Docker Desktop - this can take up to 60 seconds..."
    open -a "Docker Desktop" 2>/dev/null

    if [ $? -ne 0 ]; then
        echo ""
        echo "  Docker Desktop is not installed."
        echo "  Download it from: https://www.docker.com/products/docker-desktop/"
        echo ""
        read -p "  Press Enter to exit"
        exit 1
    fi

    waited=0
    while ! docker info >/dev/null 2>&1; do
        sleep 3
        waited=$((waited + 3))
        echo "  Still waiting... ${waited}s"
        if [ "$waited" -ge 90 ]; then
            echo ""
            echo "  Docker did not start in time."
            echo "  Please open Docker Desktop manually and try again."
            echo ""
            read -p "  Press Enter to exit"
            exit 1
        fi
    done
fi

echo " ready."

# --- 2. First-run warning ---
if ! docker ps -a --filter "name=boyas_invoice" --format "{{.Names}}" | grep -q "boyas_invoice"; then
    echo ""
    echo "  First launch: building the app. This takes about 3-5 minutes."
    echo "  Stretch your loegs. You only wait this long once."
    echo ""
fi

# --- 3. Start the app ---
echo "  [2/3] Starting Boyas Invoice..."

cd "$(dirname "$0")/.."

docker compose up -d

if [ $? -ne 0 ]; then
    echo ""
    echo "  Failed to start the app."
    echo "  Try running the launcher again. If the problem persists, contact the author."
    echo ""
    read -p "  Press Enter to exit"
    exit 1
fi

# --- 4. Wait for health ---
echo ""
printf "  [3/3] Waiting for app to be ready"

ready=false
for i in $(seq 1 40); do
    sleep 2
    if curl -sf http://localhost:8080/up >/dev/null 2>&1; then
        ready=true
        break
    fi
    printf "."
done

if $ready; then
    echo " ready!"
else
    echo " taking a bit longer than usual."
    echo "  Try refreshing your browser in a moment."
fi

# --- 5. Open browser ---
echo ""
echo "  Opening http://localhost:8080 ..."
echo "  To stop the app later, double-click 'Stop Boyas Invoice.command'"
echo ""

open "http://localhost:8080"