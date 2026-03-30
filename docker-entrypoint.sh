#!/bin/sh
set -e

echo "[entrypoint] starting"

cd /var/www/html || exit 1

# Install composer dependencies
if [ -f "composer.json" ]; then
  echo "[entrypoint] installing composer dependencies"
  composer install --no-interaction --prefer-dist --optimize-autoloader || true
fi

echo "[entrypoint] handing off to Apache"
exec apache2-foreground
