#!/bin/sh
set -e # Exit immediately if a command exits with a non-zero status.


echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
