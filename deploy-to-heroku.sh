#!/bin/bash
# Deploy to Heroku after fixing lockfile issue

set -e

echo "🚀 Heroku Deployment Script"
echo "=============================="
echo ""

# Check git status
echo "📋 Current git status:"
git status --short

echo ""
read -p "Ready to commit and push? (y/n): " confirm

if [ "$confirm" != "y" ]; then
    echo "Aborted."
    exit 0
fi

# Stage the deletion
echo ""
echo "📦 Staging yarn.lock deletion..."
git add yarn.lock 2>/dev/null || git rm yarn.lock 2>/dev/null || echo "yarn.lock already staged"

# Show what will be committed
echo ""
echo "📋 Changes to be committed:"
git status --short

echo ""
read -p "Proceed with commit? (y/n): " confirm_commit

if [ "$confirm_commit" != "y" ]; then
    echo "Aborted."
    exit 0
fi

# Commit
echo ""
echo "💾 Committing..."
git commit -m "fix: remove yarn.lock to use only npm for Heroku compatibility"

# Show remotes
echo ""
echo "📡 Git remotes:"
git remote -v

echo ""
echo "Choose deployment target:"
echo "1. Push to Heroku (git push heroku main)"
echo "2. Push to GitHub/origin first (git push origin main)"
echo "3. Push to both"
echo "4. Exit (commit only, don't push)"
echo ""
read -p "Enter choice (1-4): " push_choice

case $push_choice in
    1)
        echo ""
        echo "🚀 Pushing to Heroku..."
        git push heroku main
        ;;
    2)
        echo ""
        echo "🚀 Pushing to origin..."
        git push origin main
        ;;
    3)
        echo ""
        echo "🚀 Pushing to origin..."
        git push origin main
        echo ""
        echo "🚀 Pushing to Heroku..."
        git push heroku main
        ;;
    4)
        echo "✅ Committed but not pushed."
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
echo "To view logs: heroku logs --tail"
echo "To open app: heroku open"

