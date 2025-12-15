#!/bin/bash
# Heroku Release Phase Script
# Runs after build but before the app starts
# Database should be available at this point

set -e

echo "🚀 Starting Heroku Release Phase..."
echo ""

# Function to check database connectivity
check_database() {
    timeout 5 php -r "
        require __DIR__.'/vendor/autoload.php';
        \$app = require_once __DIR__.'/bootstrap/app.php';
        \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        try {
            DB::connection()->getPdo();
            echo 'OK';
            exit(0);
        } catch (Exception \$e) {
            exit(1);
        }
    " 2>&1
    return $?
}

# Function to wait for database with timeout
wait_for_database() {
    local max_attempts=30
    local attempt=1
    local wait_time=2
    
    echo "⏳ Waiting for database to be ready..."
    
    while [ $attempt -le $max_attempts ]; do
        echo "   Attempt $attempt/$max_attempts..."
        
        if check_database > /dev/null 2>&1; then
            echo "   ✅ Database is ready!"
            return 0
        fi
        
        if [ $attempt -lt $max_attempts ]; then
            echo "   ⏸️  Database not ready, waiting ${wait_time}s..."
            sleep $wait_time
        fi
        
        attempt=$((attempt + 1))
    done
    
    echo "   ❌ Database connection timeout after $((max_attempts * wait_time))s"
    return 1
}

# 1. Package Discovery (always run, can work without DB)
echo "📦 Running package discovery..."
php artisan package:discover --ansi || {
    echo "⚠️  Package discovery failed, continuing anyway..."
}
echo ""

# 2. Wait for database
if wait_for_database; then
    echo ""
    
    # 3. Run migrations
    echo "🔄 Running database migrations..."
    php artisan migrate --force || {
        echo "⚠️  Migrations failed!"
        exit 1
    }
    echo ""
    
    # 4. Optimize application
    echo "⚡ Optimizing application..."
    
    echo "   • Caching configuration..."
    php artisan config:cache
    
    echo "   • Caching routes..."
    php artisan route:cache || echo "   ⚠️  Route cache failed"
    
    echo "   • Caching views..."
    php artisan view:cache
    
    echo ""
    echo "✅ Release phase completed successfully!"
else
    echo ""
    echo "❌ ERROR: Database is not accessible!"
    echo ""
    echo "Please check:"
    echo "  1. Database addon is provisioned: heroku addons"
    echo "  2. DATABASE_URL is set: heroku config:get DATABASE_URL"
    echo "  3. Database is accessible from Heroku"
    echo ""
    echo "To provision a database:"
    echo "  heroku addons:create heroku-postgresql:mini"
    echo ""
    exit 1
fi

