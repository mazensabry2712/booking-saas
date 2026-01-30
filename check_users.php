<?php

use App\Models\User;
use App\Models\Tenant;

// Initialize tenant
$tenant = Tenant::first();
tenancy()->initialize($tenant);

// Check users
echo "Users in tenant: " . User::count() . "\n\n";

if (User::count() > 0) {
    foreach (User::all() as $user) {
        echo "Email: " . $user->email . " | Name: " . $user->name . "\n";
    }
} else {
    echo "No users found!\n";
}
