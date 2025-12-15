#!/bin/bash
set -e

# Run Laravel artisan commands
echo "Running Laravel setup..."

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate
fi

# Run migrations (optional - uncomment if needed)
# php artisan migrate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set Heroku PORT (Heroku sets this dynamically)
if [ -n "$PORT" ]; then
    sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf
    sed -i "s/80/$PORT/g" /etc/apache2/ports.conf
fi

# Start Apache
exec "$@"

