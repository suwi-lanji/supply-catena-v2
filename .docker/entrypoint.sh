#!/bin/sh
set -e # Exit immediately if a command exits with a non-zero status.

echo "Running database migrations..."
# The --force flag is crucial for running in a non-interactive environment
php /var/www/html/artisan migrate --force

echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
