# Heroku Deployment Fixes

## Issues Fixed

### 1. ❌ npm lockfile sync error
```
npm error `npm ci` can only install packages when your package.json 
and package-lock.json are in sync
```

**Root Cause:** `package-lock.json` was out of sync with `package.json`

**Solution:** Switched from npm to Yarn (which was working in successful builds)
- ✅ Restored `yarn.lock` from git history
- ✅ Deleted `package-lock.json`
- ✅ Updated `heroku-postbuild` to use `yarn build`

---

### 2. ❌ Database connection timeout during build
```
Connection timed out [tls://ec2-98-95-163-14.compute-1.amazonaws.com:16510]
Script @php artisan package:discover --ansi handling the post-autoload-dump 
event returned with error code 1
```

**Root Cause:** `php artisan package:discover` runs during Composer's post-autoload-dump hook, which tries to connect to the database. **The database is not accessible during the build phase.**

**Solution:** Split package discovery into two phases:

#### Build Phase (`composer.json`):
```json
"post-autoload-dump": [
    "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
    "@php artisan package:discover --ansi || true"
]
```
- Added `|| true` to allow command to fail silently during build
- Composer installation completes successfully

#### Release Phase (`Procfile`):
```
release: php artisan package:discover --ansi && php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache
```
- Runs **after build** but **before the app starts**
- Database is now available ✅
- Also runs migrations and caches for optimal performance

---

## Files Changed

### 1. `package.json`
```diff
- "heroku-postbuild": "npm run build"
+ "heroku-postbuild": "yarn build"
```

### 2. `composer.json`
```diff
  "post-autoload-dump": [
      "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
-     "@php artisan package:discover --ansi"
+     "@php artisan package:discover --ansi || true"
  ],
```

### 3. `Procfile`
```diff
+ release: php artisan package:discover --ansi && php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache
  web: vendor/bin/heroku-php-apache2 public/
  worker: php artisan schedule:work
```

### 4. Lockfiles
```diff
- package-lock.json (deleted)
+ yarn.lock (restored from git)
```

---

## Deployment Process

The build will now follow this flow:

```
1. Heroku receives code
   ↓
2. Node.js Buildpack
   - Detects yarn.lock
   - Runs: yarn install
   - Runs: yarn build (heroku-postbuild)
   ✅ Frontend assets built
   ↓
3. PHP Buildpack
   - Runs: composer install --no-dev
   - Runs: post-autoload-dump scripts
   - php artisan package:discover (fails silently ✓)
   ✅ PHP dependencies installed
   ↓
4. Release Phase (NEW!)
   - php artisan package:discover (succeeds with DB access ✓)
   - php artisan migrate --force (runs migrations automatically)
   - php artisan config:cache (optimizes config)
   - php artisan route:cache (optimizes routes)
   - php artisan view:cache (optimizes views)
   ✅ App fully configured and optimized
   ↓
5. Start Web Dyno
   ✅ App is live!
```

---

## How to Deploy

### Option 1: Use the deployment script
```bash
./fix-heroku-deploy.sh
```

### Option 2: Manual deployment
```bash
# Stage changes
git add package.json yarn.lock Procfile composer.json
git rm package-lock.json

# Commit
git commit -m "fix: resolve Heroku build issues"

# Deploy
git push heroku main

# Watch logs
heroku logs --tail
```

---

## Expected Build Output

You should now see:

```
-----> Node.js app detected
-----> Installing node modules (yarn.lock)
       yarn install v1.22.22
       Done in 6.58s.
       
-----> Build
       Running heroku-postbuild (yarn)
       $ yarn build
       ✓ built in 15.72s
       Done in 16.38s.

-----> PHP app detected
-----> Installing dependencies...
       Installing dependencies from lock file
       Generating optimized autoload files
       > @php artisan package:discover --ansi || true
       ✅ Build succeeded!

-----> Discovering process types
       Procfile declares types -> release, web, worker

-----> Releasing...
       Running release command...
       > php artisan package:discover --ansi
       > php artisan migrate --force
       > php artisan config:cache
       > php artisan route:cache
       > php artisan view:cache
       ✅ Release phase complete!

-----> Launching...
       Released v102
       https://your-app.herokuapp.com/ deployed to Heroku
```

---

## Benefits of These Changes

1. ✅ **Faster builds** - Yarn is faster than npm
2. ✅ **Auto-migrations** - Database migrates automatically on deployment
3. ✅ **Optimized caching** - Config, routes, and views are cached for better performance
4. ✅ **Reliable deployments** - No more database timeout errors during build
5. ✅ **Proper separation** - Build phase doesn't require database access

---

## Troubleshooting

If the build still fails:

1. **Check database config:**
   ```bash
   heroku config:get DATABASE_URL
   ```

2. **View detailed logs:**
   ```bash
   heroku logs --tail
   ```

3. **Manually run release commands:**
   ```bash
   heroku run php artisan package:discover
   heroku run php artisan migrate
   ```

4. **Clear build cache:**
   ```bash
   heroku repo:purge_cache -a your-app-name
   git push heroku main
   ```

---

## Next Steps

After successful deployment:

1. Open your app:
   ```bash
   heroku open
   ```

2. Check app logs:
   ```bash
   heroku logs --tail
   ```

3. Monitor performance:
   ```bash
   heroku ps
   ```

Ready to deploy? Run:
```bash
./fix-heroku-deploy.sh
```

