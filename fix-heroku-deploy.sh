#!/bin/bash
# Fix Heroku deployment by switching from npm to yarn
# This resolves the "npm lockfile is not in sync" error

set -e

echo "🔧 Heroku Deployment Fix"
echo "========================="
echo ""
echo "This will:"
echo "  1. Delete package-lock.json"
echo "  2. Add yarn.lock (restored from git)"
echo "  3. Update heroku-postbuild to use yarn"
echo "  4. Fix database connection timeout during build"
echo "  5. Commit and push to Heroku"
echo ""

# Show current changes
echo "📋 Current changes:"
git status --short
echo ""

read -p "Ready to commit these changes? (y/n): " confirm

if [ "$confirm" != "y" ]; then
    echo "Aborted."
    exit 0
fi

# Stage changes
echo ""
echo "📦 Staging changes..."
git add package.json yarn.lock Procfile composer.json
git add package-lock.json 2>/dev/null || echo "package-lock.json already deleted"

# Commit
echo ""
echo "💾 Committing..."
git commit -m "fix: resolve Heroku build issues (npm lockfile sync & database timeout)

Build fixes:
- Switch from npm to yarn (restore yarn.lock, remove package-lock.json)
- Update heroku-postbuild to use 'yarn build'
- Fix database connection timeout during build phase

Changes:
1. composer.json: Allow package:discover to fail during build (|| true)
2. Procfile: Add release phase for post-deployment tasks
   - Run package:discover after build when DB is available
   - Run migrations automatically on deployment
   - Cache config, routes, and views for performance

Fixes:
- npm error: lockfile not in sync
- Connection timed out during php artisan package:discover"

# Show remotes
echo ""
echo "📡 Git remotes:"
git remote -v | grep -E "(origin|heroku)"

echo ""
echo "Choose deployment target:"
echo "1. Push to Heroku only (git push heroku main)"
echo "2. Push to origin first, then Heroku"
echo "3. Push to origin only"
echo "4. Exit (committed but not pushed)"
echo ""
read -p "Enter choice (1-4): " push_choice

case $push_choice in
    1)
        echo ""
        echo "🚀 Pushing to Heroku..."
        git push heroku main || git push heroku master
        ;;
    2)
        echo ""
        echo "🚀 Pushing to origin..."
        git push origin main || git push origin master
        echo ""
        echo "🚀 Pushing to Heroku..."
        git push heroku main || git push heroku master
        ;;
    3)
        echo ""
        echo "🚀 Pushing to origin..."
        git push origin main || git push origin master
        ;;
    4)
        echo "✅ Committed but not pushed."
        echo ""
        echo "To push manually:"
        echo "  git push heroku main"
        exit 0
        ;;
    *)
        echo "Invalid choice. Committed but not pushed."
        exit 1
        ;;
esac

echo ""
echo "=============================="
echo "✅ Deployment complete!"
echo ""
echo "The build should now succeed with:"
echo "  - Node.js app detected"
echo "  - Installing node modules (yarn.lock)"
echo "  - Running heroku-postbuild (yarn)"
echo ""
echo "To view logs: heroku logs --tail"
echo "To open app: heroku open"

