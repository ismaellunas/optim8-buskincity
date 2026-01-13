# Heroku Database Setup Guide

## The Issue

Your build succeeded ✅, but the **release phase failed** with:
```
Connection timed out [tls://ec2-98-95-163-14.compute-1.amazonaws.com:16510]
```

This means:
- ✅ Code deployed successfully
- ✅ Dependencies installed
- ❌ Database is not accessible during release phase

---

## Quick Fix: Check & Provision Database

### Step 1: Check if Database is Provisioned

Run this command to see your Heroku addons:
```bash
heroku addons
```

**Look for:** `heroku-postgresql` in the output

**If you see it:** ✅ Database is provisioned, skip to Step 3  
**If you DON'T see it:** ❌ You need to provision a database (Step 2)

---

### Step 2: Provision a PostgreSQL Database

Choose a plan based on your needs:

#### **Option A: Mini Plan (Recommended for Production)**
```bash
heroku addons:create heroku-postgresql:mini
```
- Cost: ~$5/month
- 10GB storage
- Best for production apps

#### **Option B: Essential Plan (Free Tier - Limited)**
```bash
heroku addons:create heroku-postgresql:essential-0
```
- Cost: Free
- 1GB storage, row limits
- Good for testing/development

#### **Option C: Standard Plan (Larger Apps)**
```bash
heroku addons:create heroku-postgresql:standard-0
```
- Cost: ~$50/month
- 64GB storage, better performance

After provisioning, wait ~2 minutes for the database to be ready, then run:
```bash
heroku pg:wait
```

---

### Step 3: Verify Database Configuration

Check that the `DATABASE_URL` is set:
```bash
heroku config:get DATABASE_URL
```

**Expected output:** A long URL starting with `postgres://`

Example:
```
postgres://username:password@ec2-xx-xx-xx-xx.compute-1.amazonaws.com:5432/dbname
```

**If empty:** Your database addon isn't attached properly. Run:
```bash
heroku addons:attach YOUR_DATABASE_NAME
```

---

### Step 4: Check Laravel Database Configuration

Verify your `config/database.php` can read from `DATABASE_URL`:

```bash
heroku run php artisan tinker --execute="echo config('database.default');"
```

**Expected output:** `pgsql` (for PostgreSQL)

---

### Step 5: Test Database Connection

```bash
heroku run php artisan migrate:status
```

**If successful:** ✅ Database is connected!  
**If it times out:** ❌ Check firewall/network settings

---

### Step 6: Deploy Again

Now that the database is ready, deploy again:

```bash
git add release.sh Procfile
git commit -m "feat: add robust release script with database health checks"
git push heroku main
```

Watch the deployment:
```bash
heroku logs --tail
```

---

## What the New Release Script Does

The `release.sh` script now:

1. **✅ Runs package discovery** (works without database)
2. **⏳ Waits up to 60 seconds** for database to be ready
3. **🔄 Runs migrations** if database is accessible
4. **⚡ Optimizes caching** (config, routes, views)
5. **❌ Fails gracefully** with helpful error message if database is unavailable

---

## Troubleshooting

### Database Connection Timeout

**Symptom:**
```
Connection timed out [tls://ec2-...amazonaws.com:16510]
```

**Solutions:**

1. **Check database status:**
   ```bash
   heroku pg:info
   ```

2. **Check if database is available:**
   ```bash
   heroku pg:wait
   ```

3. **Restart database connection pool:**
   ```bash
   heroku pg:killall
   ```

4. **Check database credentials:**
   ```bash
   heroku config | grep DATABASE
   ```

---

### Release Phase Fails

**Symptom:**
```
ERROR: Database is not accessible!
```

**The release script will tell you what to check:**
1. Database addon is provisioned: `heroku addons`
2. DATABASE_URL is set: `heroku config:get DATABASE_URL`
3. Database is accessible from Heroku

**Quick fix:**
```bash
# Provision database
heroku addons:create heroku-postgresql:mini

# Wait for it to be ready
heroku pg:wait

# Deploy again
git push heroku main
```

---

### Migrations Fail

**Symptom:**
```
⚠️  Migrations failed!
```

**Check migration status:**
```bash
heroku run php artisan migrate:status
```

**Reset migrations (CAUTION: drops all tables):**
```bash
heroku run php artisan migrate:fresh --force
```

**Or run migrations manually:**
```bash
heroku run php artisan migrate --force
```

---

## Environment Variables

Make sure these are set on Heroku:

```bash
# Check all config
heroku config

# Required variables:
# - APP_KEY (Laravel encryption key)
# - DATABASE_URL (set automatically by PostgreSQL addon)
# - APP_ENV=production
# - APP_DEBUG=false
```

**Generate APP_KEY if missing:**
```bash
heroku run php artisan key:generate
```

---

## Database Plans Comparison

| Plan | Cost | Storage | Rows | Use Case |
|------|------|---------|------|----------|
| **Essential-0** | Free | 1 GB | 10k rows | Testing/Dev |
| **Mini** | $5/mo | 10 GB | Unlimited | Small Production |
| **Basic** | $9/mo | 10 GB | Unlimited | Production |
| **Standard-0** | $50/mo | 64 GB | Unlimited | Larger Apps |

More info: https://elements.heroku.com/addons/heroku-postgresql

---

## Next Steps

1. **Provision database** (if not already done)
   ```bash
   heroku addons:create heroku-postgresql:mini
   heroku pg:wait
   ```

2. **Deploy with new release script**
   ```bash
   git add release.sh Procfile
   git commit -m "feat: add robust release script"
   git push heroku main
   ```

3. **Watch logs**
   ```bash
   heroku logs --tail
   ```

4. **Open your app**
   ```bash
   heroku open
   ```

---

## Quick Reference Commands

```bash
# Check database status
heroku pg:info

# View database credentials
heroku config:get DATABASE_URL

# Connect to database console
heroku pg:psql

# Run migrations manually
heroku run php artisan migrate --force

# View release phase logs
heroku releases
heroku releases:output

# Rollback to previous release
heroku rollback
```

---

## Success Indicators

When everything is working, you'll see:

```
🚀 Starting Heroku Release Phase...
📦 Running package discovery...
⏳ Waiting for database to be ready...
   Attempt 1/30...
   ✅ Database is ready!
🔄 Running database migrations...
   ✓ Migrations completed
⚡ Optimizing application...
   • Caching configuration...
   • Caching routes...
   • Caching views...
✅ Release phase completed successfully!

-----> Launching...
       Released v103
       https://your-app.herokuapp.com/ deployed to Heroku
```

---

## Still Having Issues?

1. **Check Heroku logs:**
   ```bash
   heroku logs --tail --source app,heroku
   ```

2. **View release output:**
   ```bash
   heroku releases:output
   ```

3. **Test database locally:**
   ```bash
   heroku pg:psql -c "SELECT 1;"
   ```

4. **Contact Heroku support** if database provisioning fails

---

**Ready to try again?** Run:
```bash
heroku addons:create heroku-postgresql:mini
heroku pg:wait
git add release.sh Procfile
git commit -m "feat: add robust release script with database health checks"
git push heroku main
```

