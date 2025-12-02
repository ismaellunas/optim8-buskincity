# Fix Environment Issues and Run Role Cleanup

## Issues Found

1. **.env file syntax errors** (lines 59-60): Unquoted values with special characters
2. **Cache driver doesn't support tagging**: The app requires Redis/Memcached for tagged cache

## Solution Steps

### Step 1: Fix .env File Syntax Errors

Open your `.env` file and find lines 59-60. They likely look like:

```env
RECAPTCHA_SITE_KEY=6LcGYrsrAAAAANZoY9kkmI3p4j_-qdrz4FiIdHnC
RECAPTCHA_SECRET_KEY=6LcGYrsrAAAAACwWuDi27di1Ah7qIEC-nq_FYdDu
```

**Fix:** Add quotes around values with special characters:

```env
RECAPTCHA_SITE_KEY="6LcGYrsrAAAAANZoY9kkmI3p4j_-qdrz4FiIdHnC"
RECAPTCHA_SECRET_KEY="6LcGYrsrAAAAACwWuDi27di1Ah7qIEC-nq_FYdDu"
```

Or if the values don't have special characters, ensure there are no hidden characters or line breaks.

### Step 2: Fix Redis Connection

**Option A: Start Redis (Recommended)**

If using Laravel Sail:
```bash
sail up -d redis
# or
docker-compose up -d redis
```

If using local Redis:
```bash
# macOS
brew services start redis

# Linux
sudo systemctl start redis

# Or run directly
redis-server
```

**Option B: Use Array Cache (Temporary Workaround)**

⚠️ **Warning:** This will break tagged cache functionality, but allows migrations to run.

Edit `.env`:
```env
CACHE_DRIVER=array
```

Then modify `app/Entities/Caches/BaseCache.php` to handle non-tagging stores (see Step 3).

### Step 3: Modify BaseCache to Handle Non-Tagging Stores (If using array cache)

Edit `app/Entities/Caches/BaseCache.php`:

```php
public function remember(
    string $key,
    Closure $callback,
    mixed $default = null
): mixed {
    try {
        $cache = Cache::getStore();
        
        // Check if cache store supports tagging
        if (method_exists($cache, 'tags')) {
            return Cache::tags($this->getTags())->rememberForever(
                $key,
                $callback
            );
        } else {
            // Fallback for non-tagging stores
            return Cache::rememberForever(
                $this->tag . ':' . $key,
                $callback
            );
        }
    } catch (ConnectionException $e) {
        if (env('DEPLOYMENT')) {
            return $default;
        }
        throw $e;
    }
}
```

Do the same for `rememberWithTime()` method.

### Step 4: Run the Migration

Once Redis is running or cache is fixed:

```bash
sail artisan migrate
```

Or specifically:
```bash
sail artisan migrate --path=database/migrations/2024_12_19_000000_remove_unused_roles.php
```

### Step 5: Verify

```bash
sail artisan tinker
```

```php
use App\Models\Role;

$roles = ['SEO Manager', 'City Organizer', 'Product Manager', 'Tester', 'City-specific manager'];
foreach ($roles as $roleName) {
    $role = Role::where('name', $roleName)->first();
    echo $roleName . ': ' . ($role ? 'STILL EXISTS ❌' : 'REMOVED ✅') . "\n";
}
```

## Alternative: Direct Script (If migration still fails)

If you continue to have issues, use the direct script:

```bash
php remove_unused_roles_direct.php
```

Then manually mark the migration as run:
```bash
sail artisan migrate:status
# Note the migration name, then:
sail artisan db
# INSERT INTO migrations (migration, batch) VALUES ('2024_12_19_000000_remove_unused_roles', [next_batch_number]);
```

## Recommended Approach

1. **Fix .env syntax errors** (Step 1)
2. **Start Redis** (Step 2, Option A)
3. **Run migration** (Step 4)
4. **Verify** (Step 5)

This is the cleanest solution and maintains full application functionality.

