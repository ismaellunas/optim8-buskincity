# Running the Role Cleanup Migration

## Issue
The migration file has been created but needs to be executed to actually remove the roles from the database.

## Prerequisites

### Fix Redis Connection (Required)
The application requires Redis to be running. Choose one:

**Option A: Start Redis Service**
```bash
# If using Docker
docker-compose up -d redis

# If using Homebrew (macOS)
brew services start redis

# If using systemd (Linux)
sudo systemctl start redis
```

**Option B: Temporarily Change Cache Driver**
Edit your `.env` file and change:
```
CACHE_DRIVER=file
```
or
```
CACHE_DRIVER=array
```

Then run the migration.

## Running the Migration

Once Redis is available, run:

```bash
php artisan migrate
```

Or to run just this specific migration:

```bash
php artisan migrate --path=database/migrations/2024_12_19_000000_remove_unused_roles.php
```

## Verify the Migration Ran

Check if the migration was executed:

```bash
php artisan migrate:status | grep remove_unused_roles
```

You should see it listed as "Ran".

## Verify Roles Were Removed

### Option 1: Using Tinker
```bash
php artisan tinker
```

```php
use App\Models\Role;

$roles = ['SEO Manager', 'City Organizer', 'Product Manager', 'Tester', 'City-specific manager'];
foreach ($roles as $roleName) {
    $role = Role::where('name', $roleName)->first();
    echo $roleName . ': ' . ($role ? 'STILL EXISTS ❌' : 'REMOVED ✅') . "\n";
}
```

### Option 2: Check Admin UI
1. Login as Administrator
2. Go to **Users > Edit** (any user)
3. Open the **Role** dropdown
4. Verify the roles are no longer listed

## Troubleshooting

### Migration Already Ran
If you see "Nothing to migrate", the migration may have already run. Check the database directly or use Tinker to verify roles are gone.

### Redis Connection Error
If you continue to get Redis errors:
1. Check your `.env` file for `REDIS_HOST` and `REDIS_PORT`
2. Verify Redis is running: `redis-cli ping` (should return "PONG")
3. Consider using `CACHE_DRIVER=file` temporarily

### Roles Still Exist After Migration
If roles still exist after running the migration:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify the migration actually ran: `php artisan migrate:status`
3. Check if there are any database transaction issues

