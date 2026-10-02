#!/bin/sh
set -e

# Ensure directory permissions for storage and bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Warm production caches when APP_KEY is available
if [ -n "$APP_KEY" ]; then
    echo "Caching configuration, routes, and views..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

# Execute default container startup
exec /start.sh "$@"
