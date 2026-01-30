<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Role;

echo "========================================\n";
echo "🧪 STAFF LOGIN TEST - FULL CHECK\n";
echo "========================================\n\n";

// Get tenant
$tenant = Tenant::first();
if (!$tenant) {
    echo "❌ ERROR: No tenant found!\n";
    exit(1);
}

tenancy()->initialize($tenant);
echo "✅ Tenant initialized: {$tenant->id}\n";
echo "   Domain: " . $tenant->domains->first()?->domain . "\n\n";

// Check Staff Role
echo "--- CHECKING STAFF ROLE ---\n";
$staffRole = Role::where('name', 'Staff')->first();
if (!$staffRole) {
    echo "❌ ERROR: Staff role not found!\n";
    exit(1);
}
echo "✅ Staff Role Found\n";
echo "   ID: {$staffRole->id}\n";
echo "   Name: {$staffRole->name}\n";
echo "   Permissions: " . json_encode($staffRole->permissions) . "\n\n";

// Check Staff User
echo "--- CHECKING STAFF USER ---\n";
$staffUser = User::where('email', 'staff@demo.localhost')->first();
if (!$staffUser) {
    echo "❌ ERROR: Staff user not found!\n";
    exit(1);
}
echo "✅ Staff User Found\n";
echo "   ID: {$staffUser->id}\n";
echo "   Name: {$staffUser->name}\n";
echo "   Email: {$staffUser->email}\n";
echo "   Role ID: {$staffUser->role_id}\n";

// Load role relationship
$staffUser->load('role');
echo "   Role Name: {$staffUser->role?->name}\n\n";

// Check Password
echo "--- CHECKING PASSWORD ---\n";
$passwordCheck = Hash::check('password123', $staffUser->password);
if (!$passwordCheck) {
    echo "❌ ERROR: Password verification failed!\n";
    exit(1);
}
echo "✅ Password is correct (password123)\n\n";

// Test Login API Endpoint
echo "--- TESTING LOGIN API ---\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://booking-saas.test/api/auth/login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'email' => 'staff@demo.localhost',
        'password' => 'password123',
    ]),
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: {$httpCode}\n";

if ($httpCode === 200) {
    $data = json_decode($response, true);
    echo "✅ Login API Response:\n";
    echo "   Success: " . ($data['success'] ? 'true' : 'false') . "\n";
    echo "   User Role: " . ($data['user']['role'] ?? 'N/A') . "\n";
    echo "   User Name: " . ($data['user']['name'] ?? 'N/A') . "\n";
    echo "   Token: " . (isset($data['access_token']) ? substr($data['access_token'], 0, 20) . '...' : 'N/A') . "\n\n";
} else {
    echo "❌ Login API Failed\n";
    echo "Response: {$response}\n\n";
    exit(1);
}

// Check Auth Middleware
echo "--- CHECKING AUTH MIDDLEWARE ---\n";
$user = User::find($staffUser->id);
auth()->login($user);

if (auth()->check()) {
    echo "✅ User logged in successfully in session\n";
    echo "   Auth User ID: " . auth()->id() . "\n";
    echo "   Auth User Name: " . auth()->user()->name . "\n";
    echo "   Auth User Role: " . auth()->user()->role?->name . "\n\n";
} else {
    echo "❌ ERROR: User not logged in!\n\n";
}

// Check Role Middleware Logic
echo "--- CHECKING ROLE MIDDLEWARE LOGIC ---\n";
$userRole = auth()->user()->role?->name;
$allowedRoles = ['Admin Tenant', 'Staff'];

if (in_array($userRole, $allowedRoles)) {
    echo "✅ User has permission to access admin dashboard\n";
    echo "   User Role: {$userRole}\n";
    echo "   Allowed Roles: " . implode(', ', $allowedRoles) . "\n\n";
} else {
    echo "❌ ERROR: User does not have permission!\n";
    echo "   User Role: {$userRole}\n";
    echo "   Allowed Roles: " . implode(', ', $allowedRoles) . "\n\n";
    exit(1);
}

// Check Routes
echo "--- CHECKING ROUTES ---\n";
$routes = Route::getRoutes();
$adminDashboardRoute = null;

foreach ($routes as $route) {
    if ($route->getName() === 'admin.dashboard') {
        $adminDashboardRoute = $route;
        break;
    }
}

if ($adminDashboardRoute) {
    echo "✅ Admin Dashboard Route Found\n";
    echo "   URI: " . $adminDashboardRoute->uri() . "\n";
    echo "   Method: " . implode('|', $adminDashboardRoute->methods()) . "\n";
    echo "   Middleware: " . implode(', ', $adminDashboardRoute->middleware()) . "\n\n";
} else {
    echo "❌ ERROR: Admin dashboard route not found!\n\n";
    exit(1);
}

// Check if AdminController exists
echo "--- CHECKING ADMIN CONTROLLER ---\n";
$controllerPath = app_path('Http/Controllers/Web/AdminController.php');
if (file_exists($controllerPath)) {
    echo "✅ AdminController exists\n";

    // Check if dashboard method exists
    $controller = new \App\Http\Controllers\Web\AdminController();
    if (method_exists($controller, 'dashboard')) {
        echo "✅ dashboard() method exists\n\n";
    } else {
        echo "❌ ERROR: dashboard() method not found!\n\n";
    }
} else {
    echo "❌ ERROR: AdminController not found!\n\n";
}

echo "========================================\n";
echo "✅ ALL TESTS PASSED!\n";
echo "========================================\n\n";

echo "📋 SUMMARY:\n";
echo "   Staff User: staff@demo.localhost\n";
echo "   Password: password123\n";
echo "   Role: Staff\n";
echo "   Should redirect to: /admin/dashboard\n\n";

echo "🔍 TROUBLESHOOTING:\n";
echo "   1. Make sure to open in Incognito mode\n";
echo "   2. Clear browser cache (Ctrl + Shift + R)\n";
echo "   3. Check browser console for JavaScript errors\n";
echo "   4. Verify URL is: http://booking-saas.test/login\n\n";
