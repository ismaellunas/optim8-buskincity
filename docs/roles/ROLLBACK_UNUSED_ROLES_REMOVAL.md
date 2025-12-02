# Rollback Documentation - Unused Roles Removal

This document provides instructions for rolling back the migration that removed unused roles from the database.

## Migration Details

**Migration File:** `database/migrations/2024_12_19_000000_remove_unused_roles.php`

**Roles Removed:**
- SEO Manager
- City Organizer
- Product Manager
- Tester
- City-specific manager

## Quick Rollback

To rollback the migration and restore the removed roles:

```bash
php artisan migrate:rollback --step=1
```

This will execute the `down()` method of the migration, which will:
1. Re-create all five roles in the `roles` table
2. Set their `guard_name` to `'web'`
3. **Note:** The roles will be recreated **without any permissions** assigned

## Important Considerations

### ⚠️ Permissions Will Not Be Restored

The rollback migration recreates the roles but **does not restore their original permissions**. This is because:

- The original permissions assigned to these roles were not documented
- These roles were identified as "unused" and had no specific code logic tied to them
- Restoring roles without knowing their permissions is safer than guessing

### What Gets Restored

✅ **Restored:**
- Role names in the `roles` table
- Basic role structure with `guard_name = 'web'`

❌ **Not Restored:**
- Role-permission associations (`role_has_permissions` table)
- User-role associations (`model_has_roles` table)
- Any custom role metadata or configurations

## Manual Restoration (If Needed)

If you need to fully restore a role with its permissions and user assignments, you'll need to do it manually:

### Step 1: Verify Role Exists

After rollback, verify the role exists:

```bash
php artisan tinker
```

```php
use App\Models\Role;

// Check if role exists
Role::where('name', 'SEO Manager')->first();
```

### Step 2: Assign Permissions (If Known)

If you know what permissions the role should have, assign them:

```php
use App\Models\Role;
use App\Models\Permission;

$role = Role::where('name', 'SEO Manager')->first();
$permissions = Permission::whereIn('name', [
    'permission.name.1',
    'permission.name.2',
    // ... add known permissions
])->get();

$role->syncPermissions($permissions);
```

### Step 3: Reassign Users (If Needed)

If you need to reassign users to the role:

```php
use App\Models\User;
use App\Models\Role;

$role = Role::where('name', 'SEO Manager')->first();
$user = User::find($userId);

$user->assignRole($role);
```

## Verification Steps

After rolling back, verify the restoration:

1. **Check Database:**
   ```bash
   php artisan tinker
   ```
   ```php
   use App\Models\Role;
   
   $roles = ['SEO Manager', 'City Organizer', 'Product Manager', 'Tester', 'City-specific manager'];
   foreach ($roles as $roleName) {
       $role = Role::where('name', $roleName)->first();
       echo $roleName . ': ' . ($role ? 'EXISTS' : 'MISSING') . "\n";
   }
   ```

2. **Check Admin UI:**
   - Login as Administrator
   - Navigate to **Users > Edit** (or any user edit page)
   - Open the **Role** dropdown
   - Verify the rolled-back roles appear in the list
   - **Note:** They will appear but may not have any permissions assigned

## Alternative: Selective Rollback

If you only need to restore specific roles, you can manually create them:

```php
use App\Models\Role;

// Create a specific role
Role::factory()->create([
    'name' => 'SEO Manager',
    'guard_name' => 'web',
]);
```

## Troubleshooting

### Issue: Role Already Exists Error

If you get an error that the role already exists during rollback:
- The migration's `down()` method checks for existence before creating
- This should not happen, but if it does, the role already exists and no action is needed

### Issue: Users Cannot Access Features After Rollback

If users are reassigned to rolled-back roles but cannot access features:
- **Expected behavior:** The roles are recreated without permissions
- **Solution:** Manually assign the appropriate permissions to the role (see Step 2 above)

### Issue: Migration Rollback Fails

If the rollback fails:
1. Check database connection
2. Verify the migration file exists
3. Check Laravel logs: `storage/logs/laravel.log`
4. Try rolling back with more verbose output: `php artisan migrate:rollback --step=1 -v`

## Related Documentation

- [Role Creation and RBAC Guide](ROLE_CREATION_AND_RBAC_GUIDE.md)
- [New Role Implementation Checklist](NEW_ROLE_IMPLEMENTATION_CHECKLIST.md)
- [Implementation Plan](../implementation_plan_clean_roles.md)

## Summary

| Action | Command | Result |
|--------|---------|--------|
| Rollback migration | `php artisan migrate:rollback --step=1` | Roles recreated without permissions |
| Verify roles exist | See Verification Steps above | Confirms restoration |
| Assign permissions | Manual (see Step 2) | Restores role functionality |
| Reassign users | Manual (see Step 3) | Restores user access |

---

**Last Updated:** 2024-12-19  
**Migration:** `2024_12_19_000000_remove_unused_roles.php`

