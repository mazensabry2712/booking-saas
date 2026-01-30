<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "\n========== 📊 BOOKING SAAS - SYSTEM TEST ==========\n\n";

// Test 1: Database Connection
echo "✅ Test 1: Database Connection\n";
try {
    DB::connection()->getPdo();
    echo "   ✓ Database connected: " . config('database.default') . "\n";
    echo "   ✓ Database name: " . config('database.connections.mysql.database') . "\n";
} catch (Exception $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Tenants
echo "\n✅ Test 2: Tenants & Domains\n";
$tenantCount = \App\Models\Tenant::count();
$domainCount = \Stancl\Tenancy\Database\Models\Domain::count();
echo "   ✓ Total Tenants: $tenantCount\n";
echo "   ✓ Total Domains: $domainCount\n";

if ($tenantCount > 0) {
    $tenant = \App\Models\Tenant::first();
    echo "   ✓ First Tenant ID: {$tenant->id}\n";
    echo "   ✓ First Tenant Name: {$tenant->name}\n";
    echo "   ✓ First Tenant Active: " . ($tenant->active ? 'Yes' : 'No') . "\n";

    $domains = $tenant->domains;
    echo "   ✓ Tenant Domains:\n";
    foreach ($domains as $domain) {
        echo "     - {$domain->domain}\n";
    }
}

// Test 3: Tenant Database Users
echo "\n✅ Test 3: Tenant Database - Users & Roles\n";
if ($tenantCount > 0) {
    $tenant = \App\Models\Tenant::first();
    $tenant->run(function () {
        $userCount = \App\Models\User::count();
        $roleCount = \App\Models\Role::count();

        echo "   ✓ Tenant Users: $userCount\n";
        echo "   ✓ Tenant Roles: $roleCount\n";

        if ($userCount > 0) {
            echo "\n   Users List:\n";
            foreach (\App\Models\User::all() as $user) {
                $roleName = \App\Models\Role::find($user->role_id)?->name ?? 'No Role';
                echo "     - {$user->name} ({$user->email}) - Role: {$roleName}\n";
            }
        }

        if ($roleCount > 0) {
            echo "\n   Roles List:\n";
            foreach (\App\Models\Role::all() as $role) {
                echo "     - {$role->name}\n";
            }
        }
    });
}

// Test 4: Appointments
echo "\n✅ Test 4: Appointments\n";
if ($tenantCount > 0) {
    $tenant = \App\Models\Tenant::first();
    $tenant->run(function () {
        $appointmentCount = \App\Models\Appointment::count();
        echo "   ✓ Total Appointments: $appointmentCount\n";
    });
}

// Test 5: Queue
echo "\n✅ Test 5: Queue System\n";
if ($tenantCount > 0) {
    $tenant = \App\Models\Tenant::first();
    $tenant->run(function () {
        $queueCount = \App\Models\Queue::count();
        echo "   ✓ Total Queue Items: $queueCount\n";
    });
}

// Test 6: Notifications
echo "\n✅ Test 6: Notifications\n";
if ($tenantCount > 0) {
    $tenant = \App\Models\Tenant::first();
    $tenant->run(function () {
        $notificationCount = \App\Models\Notification::count();
        echo "   ✓ Total Notifications: $notificationCount\n";
    });
}

// Test 7: Invoices
echo "\n✅ Test 7: Invoices\n";
if ($tenantCount > 0) {
    $tenant = \App\Models\Tenant::first();
    $tenant->run(function () {
        $invoiceCount = \App\Models\Invoice::count();
        echo "   ✓ Total Invoices: $invoiceCount\n";
    });
}

// Test 8: Routes
echo "\n✅ Test 8: Routes\n";
$routes = \Route::getRoutes();
$apiRoutes = 0;
$webRoutes = 0;
foreach ($routes as $route) {
    if (str_starts_with($route->uri(), 'api/')) {
        $apiRoutes++;
    } elseif (!str_starts_with($route->uri(), '_')) {
        $webRoutes++;
    }
}
echo "   ✓ API Routes: $apiRoutes\n";
echo "   ✓ Web Routes: $webRoutes\n";

// Test 9: Storage
echo "\n✅ Test 9: Storage\n";
$publicPath = storage_path('app/public');
$exists = is_dir($publicPath);
echo "   " . ($exists ? '✓' : '✗') . " Storage public directory exists\n";
$writable = is_writable(storage_path('app'));
echo "   " . ($writable ? '✓' : '✗') . " Storage directory writable\n";

// Test 10: Environment
echo "\n✅ Test 10: Environment\n";
echo "   ✓ App Name: " . config('app.name') . "\n";
echo "   ✓ App Environment: " . config('app.env') . "\n";
echo "   ✓ App Debug: " . (config('app.debug') ? 'Enabled' : 'Disabled') . "\n";
echo "   ✓ App URL: " . config('app.url') . "\n";

echo "\n========== 🎉 ALL TESTS COMPLETED ==========\n\n";
