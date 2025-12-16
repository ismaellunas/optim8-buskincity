#!/bin/bash
# Revert to Previous Working Configuration
# This removes the release phase that requires a database

set -e

echo "╔══════════════════════════════════════════════════════════╗"
echo "║     Reverting to Previous Working Configuration          ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo ""

echo "This will:"
echo "  • Remove the release phase from Procfile"
echo "  • Keep composer.json changes (|| true for safety)"
echo "  • Keep yarn.lock (fixed npm lockfile issue)"
echo "  • Your app will deploy without requiring database"
echo ""

read -p "Continue? (y/n): " confirm

if [ "$confirm" != "y" ]; then
    echo "Aborted."
    exit 0
fi

echo ""
echo "📦 Reverting Procfile to original version..."

# Restore original Procfile (without release phase)
cat > Procfile << 'EOF'
web: vendor/bin/heroku-php-apache2 public/
worker: php artisan schedule:work
EOF

echo "✅ Procfile reverted"
echo ""
echo "📋 Current Procfile:"
cat Procfile
echo ""

read -p "Ready to commit and deploy? (y/n): " deploy_confirm

if [ "$deploy_confirm" != "y" ]; then
    echo ""
    echo "✅ Procfile reverted but not committed."
    echo ""
    echo "To commit later, run:"
    echo "  git add Procfile"
    echo "  git commit -m 'revert: remove release phase (no database required)'"
    echo "  git push heroku main"
    exit 0
fi

echo ""
echo "💾 Committing changes..."
git add Procfile

git commit -m "revert: remove release phase (no database required)

This reverts to the previous working configuration where:
- No release phase (no database connection required)
- Build succeeds immediately
- App starts without migrations/optimization

The following fixes are KEPT:
- yarn.lock (fixes npm lockfile sync error)
- composer.json with || true (allows package:discover to fail gracefully)
- package.json with yarn build

If you want to add database support later, you can:
1. Provision PostgreSQL: heroku addons:create heroku-postgresql:mini
2. Re-add release phase: Add 'release: bash release.sh' to Procfile"

echo ""
echo "🚀 Pushing to Heroku..."
git push heroku main || git push heroku master

echo ""
echo "════════════════════════════════════════════════════════"
echo "✅ Deployment complete!"
echo ""
echo "Your app should now be running without database dependency."
echo ""
echo "To view logs:"
echo "  heroku logs --tail"
echo ""
echo "To open your app:"
echo "  heroku open"

