<?php

use App\Models\User;
use Modules\Space\Entities\Space;
use Spatie\Permission\Models\Role;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create Test User
$user = User::factory()->create();
$user->assignRole('city_administrator');

echo "Debugging Authorization for User ID: " . $user->id . "\n";
echo "Roles: " . implode(', ', $user->getRoleNames()->toArray()) . "\n";

// Test can('create', Space::class)
$canCreate = $user->can('create', Space::class);
echo "Can create Space? " . ($canCreate ? "✅ YES" : "❌ NO") . "\n";

// Test policy directly
$policy = new \Modules\Space\Policies\SpacePolicy();
$policyCreate = $policy->create($user);
echo "Policy::create() result: " . ($policyCreate ? "✅ YES" : "❌ NO") . "\n";

if (!$canCreate && $policyCreate) {
    echo "⚠️ Mismatch detected. Gate might not be using the correct policy or something else is interfering.\n";
}

// Cleanup
$user->delete();
echo "Cleanup done.\n";
