#!/bin/bash

set -e  # esci se qualche comando fallisce

if [ ! -d "app" ]; then
  echo "Installazione laravel..."
  composer global require laravel/installer

  # Determine composer global home and bin dir, make laravel executable available.
  # Try to get composer home/bin via composer; fall back to common locations.
  COMPOSER_HOME="$(composer config -g home 2>/dev/null || echo "$HOME/.composer")"
  COMPOSER_BIN="$(composer global config bin-dir --absolute 2>/dev/null || echo "${COMPOSER_HOME}/vendor/bin")"
  export PATH="$COMPOSER_BIN:$HOME/.config/composer/vendor/bin:$HOME/.composer/vendor/bin:$PATH"
  # refresh shell command lookup (no-op on some shells)
  hash -r || true

  # Create Laravel project in a temporary directory and move files to current root.
  echo "Using temporary dir: ${TMP_DIR}"
  # Use explicit binary path to avoid depending solely on PATH
  if [ -x "${COMPOSER_BIN}/laravel" ]; then
    echo "DEBUG: using ${COMPOSER_BIN}/laravel"
    "${COMPOSER_BIN}/laravel" new laravel-app
  elif command -v laravel >/dev/null 2>&1; then
    echo "DEBUG: using laravel from PATH"
    laravel new laravel-app
  else
    echo "ERROR: laravel binary not found after composer global require"
    echo "ERROR: aborting; see debug output above to troubleshoot composer global bin location"
    exit 1
  fi

  echo "Copying Laravel files into project root..."
  # enable dotglob so hidden files like .env are copied too
  shopt -s dotglob
  cp -a laravel-app/. .
  shopt -u dotglob

  # cleanup
  rm -rf laravel-app
fi

if [ ! -d "vendor" ]; then
  echo "Installazione dipendenze php..."
  composer install
fi

echo "🔍 Environment variables check:"
echo "DB_HOST=${DB_HOST}"
echo "DB_DATABASE=${DB_DATABASE}"
echo "DB_PORT=${DB_PORT}"
echo "DB_USERNAME=${DB_USERNAME}"
echo "DB_PASSWORD=${DB_PASSWORD}"

if [ "$DB_HOST" = "database" ]; then
    echo "⏳ Waiting for database to be ready..."
    until PGPASSWORD="${DB_PASSWORD:-secret}" psql -h "${DB_HOST:-database}" -U "${DB_USERNAME:-root}" -d "${DB_DATABASE:-postgres}" -c "SELECT 1" >/dev/null 2>&1; do
      echo "⏳ Waiting again..."
      sleep 5
    done
else
  echo "Skipping database check because DB_HOST is not database"
fi

echo "Run migrations"
if [ "$DB_HOST" = "database" ]; then
  php artisan migrate
else
  echo "Skipping migrations because DB_HOST is not database"
fi

echo "Start FrankenPhp"
frankenphp run --config /etc/caddy/Caddyfile --adapter caddyfile


