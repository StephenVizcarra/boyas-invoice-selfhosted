#!/bin/bash

echo ""
echo "  Stopping Boyas Invoice..."

cd "$(dirname "$0")/.."
docker compose down

echo ""
echo "  Stopped. Your data is safely saved."
echo ""
sleep 2
