#!/bin/bash
set -e

# Wacht tot database bereikbaar is
echo "Wachten op database..."
while ! php artisan db:monitor --databases=mysql 2>/dev/null; do
    sleep 2
done

# Laravel bootstrap
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

# Storage symlink aanmaken als die nog niet bestaat
php artisan storage:link 2>/dev/null || true

echo "App gestart."
exec "$@"
