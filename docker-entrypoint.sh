#!/bin/sh
set -e

echo "[entrypoint] starting"

cd /var/www/html || exit 1

# Install composer dependencies
if [ -f "composer.json" ]; then
  echo "[entrypoint] installing composer dependencies"
  composer install --no-interaction --prefer-dist --optimize-autoloader || true
fi

# Wait for DB and run seed script if present
SEED_SCRIPT=back/auth/seed.php
if [ -f "$SEED_SCRIPT" ]; then
  echo "[entrypoint] found seed script: $SEED_SCRIPT — attempting to run"
  attempts=0
  until php "$SEED_SCRIPT" || [ $attempts -ge 5 ]; do
    attempts=$((attempts+1))
    echo "[entrypoint] seed failed, retrying in 5s... ($attempts/5)"
    sleep 5
  done
  if [ $attempts -ge 5 ]; then
    echo "[entrypoint] seed script did not succeed after retries — continuing"
  else
    echo "[entrypoint] seed completed"
  fi
else
  echo "[entrypoint] no seed script found at $SEED_SCRIPT"
fi

echo "[entrypoint] handing off to Apache"
exec apache2-foreground
