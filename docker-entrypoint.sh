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
SEED_AUTH=back/auth/seed.php
SEED_ARTICLES=back/seeders/seed_daily_articles.php

if [ -f "$SEED_AUTH" ]; then
  echo "[entrypoint] found auth seed script: $SEED_AUTH attempting to run"
  attempts=0
  until php "$SEED_AUTH" || [ $attempts -ge 5 ]; do
    attempts=$((attempts+1))
    echo "[entrypoint] auth seed failed, retrying in 5s... ($attempts/5)"
    sleep 5
  done
  if [ $attempts -ge 5 ]; then
    echo "[entrypoint] auth seed script did not succeed after retries"
  else
    echo "[entrypoint] auth seed completed"
  fi
fi

if [ -f "$SEED_ARTICLES" ]; then
  echo "[entrypoint] found article seed script: $SEED_ARTICLES attempting to run"
  if php "$SEED_ARTICLES"; then
    echo "[entrypoint] article seeding completed"
  else
    echo "[entrypoint] article seeding failed"
  fi
fi

echo "[entrypoint] handing off to Apache"
exec apache2-foreground