<?php

use App\Models\City;
use App\Models\User;
use Modules\Space\Entities\Space;
use Modules\Space\Entities\SpaceEvent;
use Spatie\Permission\Models\Role;

// 1. Verify Cities
$cityCount = City::count();
echo "✅ Cities count: " . $cityCount . "\n";

$london = City::where('name', 'London')->where('country_code', 'GB')->first();
if ($london) {
    echo "✅ Found London, UK (ID: {$london->id})\n";
} else {
    echo "❌ London not found\n";
}

// 2. Verify Role
$role = Role::where('name', 'city_administrator')->first();
if ($role) {
    echo "✅ City Administrator role exists\n";
    echo "   Permissions: " . implode(', ', $role->permissions->pluck('name')->toArray()) . "\n";
} else {
    echo "❌ City Administrator role missing\n";
}

// 3. Create Test User and Assign City
$user = User::factory()->create();
$user->assignRole('city_administrator');
$user->adminCities()->attach($london->id);

echo "✅ Created test user (ID: {$user->id}) and assigned as admin for London\n";

if ($user->isCityAdmin($london->id)) {
    echo "✅ isCityAdmin() check passed for London\n";
} else {
    echo "❌ isCityAdmin() check failed\n";
}

// 4. Test Policy Logic
$eventInLondon = new SpaceEvent();
$eventInLondon->city_id = $london->id;
$eventInLondon->author_id = $user->id; // Just to have an author

$paris = City::where('name', 'Paris')->where('country_code', 'FR')->first();
$eventInParis = new SpaceEvent();
$eventInParis->city_id = $paris->id;

echo "\nTesting Authorization:\n";

// Manually checking the policy logic since we are in a script, not a request
$policy = new \App\Policies\SpaceEventPolicy();

$canViewLondon = $policy->view($user, $eventInLondon);
echo "   Can view event in London? " . ($canViewLondon ? "✅ YES" : "❌ NO") . "\n";

$canViewParis = $policy->view($user, $eventInParis);
echo "   Can view event in Paris? " . (!$canViewParis ? "✅ NO (Correct)" : "❌ YES (Incorrect)") . "\n";

// Cleanup
$user->delete();
echo "\n✅ Test user deleted\n";
