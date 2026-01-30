<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get tenant
$tenant = App\Models\Tenant::first();
tenancy()->initialize($tenant);

echo "Tenant: " . $tenant->id . "\n\n";

// Get all roles
echo "=== Roles ===\n";
$roles = App\Models\Role::all();
foreach ($roles as $role) {
    echo "ID: {$role->id} | Name: {$role->name}\n";
}

echo "\n=== Users ===\n";
$users = App\Models\User::with('role')->get();
foreach ($users as $user) {
    echo "ID: {$user->id} | Email: {$user->email} | Name: {$user->name} | Role: {$user->role?->name} | Role ID: {$user->role_id}\n";
}

echo "\n=== Testing Staff Login ===\n";
$staff = App\Models\User::where('email', 'staff@demo.localhost')->first();
if ($staff) {
    $passwordCheck = Hash::check('password123', $staff->password);
    echo "Staff found: {$staff->email}\n";
    echo "Password check: " . ($passwordCheck ? 'PASS' : 'FAIL') . "\n";
    echo "Role: {$staff->role?->name}\n";
} else {
    echo "Staff user NOT FOUND!\n";
}
