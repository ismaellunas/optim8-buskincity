<?php

use App\Models\User;
use Modules\Space\Entities\Space;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- Debugging Permissions ---\n";

// 1. Check City Administrator Role
try {
    $cityAdminUser = User::factory()->create();
    $cityAdminUser->assignRole('city_administrator');

    echo "\n[City Admin User]\n";
    echo "User ID: " . $cityAdminUser->id . "\n";
    echo "Roles: " . implode(', ', $cityAdminUser->getRoleNames()->toArray()) . "\n";
    echo "Has 'city_administrator' role? " . ($cityAdminUser->hasRole('city_administrator') ? "YES" : "NO") . "\n";
    
    $policy = new \Modules\Space\Policies\SpacePolicy();
    $canCreatePolicy = $policy->create($cityAdminUser);
    echo "SpacePolicy::create(): " . ($canCreatePolicy ? "YES" : "NO") . "\n";

    echo "can('create', Space::class): " . ($cityAdminUser->can('create', Space::class) ? "YES" : "NO") . "\n";

    $cityAdminUser->delete();
} catch (\Exception $e) {
    echo "Error checking City Admin: " . $e->getMessage() . "\n";
}

// 2. Check Main Admin User
try {
    $adminUser = User::where('email', 'admin@' . config('constants.domain'))->first();
    if ($adminUser) {
        echo "\n[Main Admin User: " . $adminUser->email . "]\n";
        echo "Roles: " . implode(', ', $adminUser->getRoleNames()->toArray()) . "\n";
        
        $canCreatePolicy = $policy->create($adminUser);
        echo "SpacePolicy::create(): " . ($canCreatePolicy ? "YES" : "NO") . "\n";
        echo "can('create', Space::class): " . ($adminUser->can('create', Space::class) ? "YES" : "NO") . "\n";
        
        // Debug specific permissions
        echo "Permissions List count: " . $adminUser->getAllPermissions()->count() . "\n";
        echo "Can 'space.add'? " . ($adminUser->can('space.add') ? "YES" : "NO") . "\n";
        echo "Can 'space.create'? " . ($adminUser->can('space.create') ? "YES" : "NO") . "\n"; 
        
    } else {
        echo "Admin user not found.\n";
    }
} catch (\Exception $e) {
    echo "Error checking Admin: " . $e->getMessage() . "\n";
}
