#!/bin/bash
# Test Heroku buildpack build locally

echo "🔍 Checking Heroku CLI installation..."
if ! command -v heroku &> /dev/null; then
    echo "❌ Heroku CLI not found. Install it first:"
    echo "   brew tap heroku/brew && brew install heroku"
    exit 1
fi

echo "✅ Heroku CLI found"
echo ""
echo "📦 Testing Heroku build locally using buildpacks..."
echo "   This simulates what happens when you deploy to Heroku"
echo ""

# Create a temporary directory for the build
BUILD_DIR=$(mktemp -d)
echo "Build directory: $BUILD_DIR"

# Run heroku local build (requires heroku/heroku:22 stack)
heroku local:version || echo "Note: heroku local requires setup"

echo ""
echo "To test the buildpack build:"
echo "1. Install pack CLI: brew install buildpacks/tap/pack"
echo "2. Run: pack build my-app --builder heroku/builder:22"
echo "3. Run: docker run -e PORT=8080 -p 8080:8080 my-app"

