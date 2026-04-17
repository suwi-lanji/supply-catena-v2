#!/bin/sh
set -e
php artisan octane:install --server=swoole
echo "--- Starting One-Time Setup Job ---"

# Clear any potentially stale cache from the build process to ensure
# the new environment variables are loaded for the migration.
echo "[1/3] Clearing configuration cache..."
#php /var/www/html/artisan optimize:clear

# Run database migrations to build or update the database schema.
# The --force flag is required for running in a non-interactive environment.
#echo "[2/3] Running database migrations..."
#php /var/www/html/artisan migrate --force

# Pre-compile application assets for maximum performance.
# The main web container will use these cached files.
echo "[3/3] Caching application configuration, events, and views..."
#php /var/www/html/artisan config:cache
#php /var/www/html/artisan event:cache
#php /var/www/html/artisan view:cache
# IMPORTANT: Do NOT cache routes with 'php artisan route:cache'.
# Octane manages routes in memory and a cached file will cause issues.

echo "--- Setup Job Completed Successfully ---"
