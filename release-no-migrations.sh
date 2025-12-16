#!/bin/bash
# Heroku Release Phase Script - WITHOUT Auto-Migrations
# Safe for shared databases between dev/test/staging

set -e

echo "🚀 Starting Heroku Release Phase (no auto-migrations)..."
echo ""

# 1. Package Discovery (doesn't require database)
echo "📦 Running package discovery..."
php artisan package:discover --ansi || {
    echo "⚠️  Package discovery failed, continuing anyway..."
}
echo ""

# 2. Optimize application (safe, no database required)
echo "⚡ Optimizing application..."

echo "   • Caching configuration..."
php artisan config:cache || echo "   ⚠️  Config cache failed"

echo "   • Caching routes..."
php artisan route:cache || echo "   ⚠️  Route cache failed"

echo "   • Caching views..."
php artisan view:cache || echo "   ⚠️  View cache failed"

echo ""
echo "✅ Release phase completed!"
echo ""
echo "ℹ️  NOTE: Migrations are NOT run automatically."
echo "   To run migrations manually:"
echo "   $ heroku run php artisan migrate --force"

