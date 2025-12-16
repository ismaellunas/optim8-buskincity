#!/bin/bash
# Fix Redis Connection Error on Heroku

set -e

echo "╔══════════════════════════════════════════════════════════╗"
echo "║     🔧 Fixing Redis Connection Error                    ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""

echo "This will configure your Heroku app to NOT use Redis."
echo ""

echo "1️⃣  Checking current configuration..."
echo "─────────────────────────────────────────────────────────"
heroku config | grep -E "(SESSION|CACHE|QUEUE|REDIS)" || echo "No Redis-related variables found"
echo ""

read -p "Continue to fix? (y/n): " confirm

if [ "$confirm" != "y" ]; then
    echo "Aborted."
    exit 0
fi

echo ""
echo "2️⃣  Setting environment variables to NOT use Redis..."
echo "─────────────────────────────────────────────────────────"

echo "   • Setting SESSION_DRIVER=database"
heroku config:set SESSION_DRIVER=database

echo "   • Setting CACHE_DRIVER=file"
heroku config:set CACHE_DRIVER=file

echo "   • Setting QUEUE_CONNECTION=sync"
heroku config:set QUEUE_CONNECTION=sync

echo ""
echo "3️⃣  Checking if REDIS_URL is set..."
echo "─────────────────────────────────────────────────────────"

REDIS_URL=$(heroku config:get REDIS_URL 2>/dev/null)
if [ -n "$REDIS_URL" ]; then
    echo "   ⚠️  REDIS_URL is set: $REDIS_URL"
    read -p "   Remove it? (y/n): " remove_redis
    if [ "$remove_redis" = "y" ]; then
        heroku config:unset REDIS_URL
        echo "   ✅ REDIS_URL removed"
    fi
else
    echo "   ✅ REDIS_URL not set (good!)"
fi

echo ""
echo "════════════════════════════════════════════════════════"
echo "✅ Configuration updated!"
echo ""
echo "Your app will restart automatically."
echo ""
echo "Check logs:"
echo "  heroku logs --tail"
echo ""
echo "Open app:"
echo "  heroku open"

