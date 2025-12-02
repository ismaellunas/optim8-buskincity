# Running the Python Script to Remove Unused Roles

This Python script bypasses Laravel entirely and connects directly to your database to remove the unused roles.

## Prerequisites

### 1. Install Python Dependencies

**For MySQL:**
```bash
pip install python-dotenv mysql-connector-python
```

**For PostgreSQL:**
```bash
pip install python-dotenv psycopg2-binary
```

**Or use the requirements file:**
```bash
pip install -r requirements_remove_roles.txt
```

### 2. Verify .env File

Make sure your `.env` file has the database configuration:
```env
DB_CONNECTION=mysql  # or pgsql, sqlite
DB_HOST=127.0.0.1
DB_PORT=3306        # 5432 for PostgreSQL
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## Running the Script

### Basic Usage

```bash
python3 remove_unused_roles.py
```

### With Laravel Sail

If your database is in a Docker container (Laravel Sail):

```bash
# Option 1: Run from host (if database port is exposed)
python3 remove_unused_roles.py

# Option 2: Run inside container
sail exec laravel.test python3 remove_unused_roles.py
```

### With Docker Compose

```bash
docker-compose exec app python3 remove_unused_roles.py
```

## What the Script Does

1. **Reads database config** from `.env` file
2. **Connects directly** to the database (bypasses Laravel)
3. **For each role:**
   - Finds the role by name
   - Removes all user-role associations (`model_has_roles`)
   - Removes all role-permission associations (`role_has_permissions`)
   - Deletes the role from `roles` table
4. **Commits all changes** in a transaction
5. **Shows summary** of what was removed

## Roles Removed

- SEO Manager
- City Organizer
- Product Manager
- Tester
- City-specific manager

## Verification

After running the script, verify the roles were removed:

### Option 1: Using Laravel Tinker
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

### Option 2: Check Admin UI
1. Login as Administrator
2. Go to **Users > Edit** (any user)
3. Open the **Role** dropdown
4. Verify the roles are no longer listed

## Troubleshooting

### "mysql-connector-python not installed"
```bash
pip install mysql-connector-python
```

### "psycopg2-binary not installed"
```bash
pip install psycopg2-binary
```

### "Could not connect to database"
- Verify database credentials in `.env`
- Check if database server is running
- For Docker: Ensure container is running: `docker ps`
- Check database port is accessible

### "DB_DATABASE not set"
- Make sure `.env` file exists in project root
- Verify `DB_DATABASE` is set in `.env`

### Permission Denied
```bash
chmod +x remove_unused_roles.py
```

## Marking Migration as Run (Optional)

After successfully running the script, you may want to mark the migration as run so Laravel knows it's been executed:

```bash
sail artisan tinker
```

```php
DB::table('migrations')->insert([
    'migration' => '2024_12_19_000000_remove_unused_roles',
    'batch' => DB::table('migrations')->max('batch') + 1
]);
```

Or manually in your database:
```sql
INSERT INTO migrations (migration, batch) 
VALUES ('2024_12_19_000000_remove_unused_roles', 
        (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations));
```

## Advantages of This Approach

✅ **No Laravel bootstrap** - Avoids cache/Redis issues  
✅ **No PHP dependencies** - Pure Python  
✅ **Direct database access** - Fast and reliable  
✅ **Transaction safe** - All changes in one transaction  
✅ **Detailed output** - Shows exactly what was removed  
✅ **Cross-platform** - Works on any system with Python  

## Safety Features

- ✅ Checks if role exists before attempting deletion
- ✅ Uses transactions (rolls back on error)
- ✅ Shows detailed output of what's being removed
- ✅ Safe to run multiple times (idempotent)

