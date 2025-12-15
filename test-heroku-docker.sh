#!/bin/bash
# Test Heroku Docker Build Locally
# This script helps you test if your Docker build will succeed on Heroku

set -e

echo "🚀 Heroku Docker Build Test Script"
echo "=================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    print_error "Docker is not running. Please start Docker Desktop."
    exit 1
fi
print_status "Docker is running"

echo ""
echo "Choose testing method:"
echo "1. Test with Dockerfile.heroku (Container Stack - recommended for testing)"
echo "2. Test with Cloud Native Buildpacks (Most accurate Heroku simulation)"
echo "3. Run local environment check"
echo ""
read -p "Enter choice (1-3): " choice

case $choice in
    1)
        echo ""
        echo "📦 Building Docker image from Dockerfile.heroku..."
        echo "This tests if your Dockerfile builds successfully"
        echo ""
        
        # Build the image
        docker build -f Dockerfile.heroku -t optim8-buskincity:heroku-test .
        
        if [ $? -eq 0 ]; then
            print_status "Docker image built successfully!"
            echo ""
            echo "To run the container:"
            echo ""
            echo "docker run -it --rm \\"
            echo "  -e APP_KEY=base64:$(openssl rand -base64 32) \\"
            echo "  -e APP_ENV=local \\"
            echo "  -e APP_DEBUG=true \\"
            echo "  -e DB_CONNECTION=pgsql \\"
            echo "  -e DB_HOST=host.docker.internal \\"
            echo "  -e DB_PORT=5432 \\"
            echo "  -e DB_DATABASE=your_db_name \\"
            echo "  -e DB_USERNAME=your_db_user \\"
            echo "  -e DB_PASSWORD=your_db_pass \\"
            echo "  -e PORT=8080 \\"
            echo "  -p 8080:8080 \\"
            echo "  optim8-buskincity:heroku-test"
            echo ""
            
            read -p "Run the container now? (y/n): " run_now
            if [ "$run_now" = "y" ]; then
                docker run -it --rm \
                  -e APP_KEY=base64:$(openssl rand -base64 32) \
                  -e APP_ENV=local \
                  -e APP_DEBUG=true \
                  -e PORT=8080 \
                  -p 8080:8080 \
                  optim8-buskincity:heroku-test
            fi
        else
            print_error "Docker build failed!"
            echo "Review the error messages above to fix your Dockerfile"
            exit 1
        fi
        ;;
    
    2)
        echo ""
        echo "📦 Testing with Cloud Native Buildpacks..."
        echo ""
        
        # Check if pack is installed
        if ! command -v pack &> /dev/null; then
            print_warning "Pack CLI not found. Installing..."
            echo "Installing pack via Homebrew..."
            brew install buildpacks/tap/pack
        fi
        print_status "Pack CLI is available"
        
        echo ""
        echo "Building with Heroku buildpacks (this may take a while)..."
        pack build optim8-buskincity:buildpack-test \
          --builder heroku/builder:22 \
          --buildpack heroku/php \
          --buildpack heroku/nodejs
        
        if [ $? -eq 0 ]; then
            print_status "Buildpack build successful!"
            echo ""
            echo "To run the container:"
            echo ""
            echo "docker run -it --rm \\"
            echo "  -e PORT=8080 \\"
            echo "  -e DATABASE_URL='postgres://user:pass@host.docker.internal:5432/dbname' \\"
            echo "  -p 8080:8080 \\"
            echo "  optim8-buskincity:buildpack-test"
        else
            print_error "Buildpack build failed!"
            exit 1
        fi
        ;;
    
    3)
        echo ""
        echo "🔍 Running environment checks..."
        echo ""
        
        # Check PHP version
        if command -v php &> /dev/null; then
            print_status "PHP version: $(php -v | head -n 1)"
        else
            print_warning "PHP not found"
        fi
        
        # Check Composer
        if command -v composer &> /dev/null; then
            print_status "Composer version: $(composer --version)"
        else
            print_warning "Composer not found"
        fi
        
        # Check Node
        if command -v node &> /dev/null; then
            print_status "Node version: $(node --version)"
        else
            print_warning "Node not found"
        fi
        
        # Check npm
        if command -v npm &> /dev/null; then
            print_status "npm version: $(npm --version)"
        else
            print_warning "npm not found"
        fi
        
        echo ""
        echo "📋 Checking critical files..."
        
        files=("composer.json" "package.json" "Procfile" ".env.example")
        for file in "${files[@]}"; do
            if [ -f "$file" ]; then
                print_status "$file exists"
            else
                print_error "$file missing"
            fi
        done
        
        echo ""
        echo "🔍 Checking dependencies..."
        
        # Check if composer.lock exists
        if [ -f "composer.lock" ]; then
            print_status "composer.lock exists"
        else
            print_warning "composer.lock missing - run 'composer install'"
        fi
        
        # Check if package-lock.json exists
        if [ -f "package-lock.json" ]; then
            print_status "package-lock.json exists"
        else
            print_warning "package-lock.json missing - run 'npm install'"
        fi
        ;;
    
    *)
        print_error "Invalid choice"
        exit 1
        ;;
esac

echo ""
echo "=================================="
echo "✅ Testing complete!"

