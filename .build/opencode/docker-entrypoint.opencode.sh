#!/bin/sh
set -e  # esci se qualche comando fallisce

echo "🔍 Environment variables check:"
echo "OPEN_AI_API_KEY=${OPEN_AI_API_KEY}"

echo "Starting open code ..."
opencode web --port 4096 --hostname 0.0.0.0
