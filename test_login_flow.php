<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\User;

echo "========================================\n";
echo "🔐 TESTING STAFF LOGIN FLOW\n";
echo "========================================\n\n";

$tenant = Tenant::first();
tenancy()->initialize($tenant);

$staffUser = User::where('email', 'staff@demo.localhost')->first();

// Simulate login
auth()->login($staffUser);

echo "✅ User logged in\n";
echo "   ID: " . auth()->id() . "\n";
echo "   Name: " . auth()->user()->name . "\n";
echo "   Role: " . auth()->user()->role?->name . "\n\n";

// Test accessing admin dashboard with CURL + Session
echo "--- TESTING FULL LOGIN FLOW ---\n\n";

// Step 1: Get CSRF token
echo "Step 1: Getting CSRF token...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://booking-saas.test/login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => __DIR__ . '/cookies.txt',
    CURLOPT_COOKIEFILE => __DIR__ . '/cookies.txt',
]);
$loginPage = curl_exec($ch);
curl_close($ch);

// Extract CSRF token
preg_match('/<meta name="csrf-token" content="([^"]+)"/', $loginPage, $matches);
$csrfToken = $matches[1] ?? null;

if ($csrfToken) {
    echo "✅ CSRF Token: " . substr($csrfToken, 0, 20) . "...\n\n";
} else {
    echo "❌ CSRF token not found!\n\n";
}

// Step 2: Login
echo "Step 2: Logging in...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://booking-saas.test/api/auth/login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_COOKIEJAR => __DIR__ . '/cookies.txt',
    CURLOPT_COOKIEFILE => __DIR__ . '/cookies.txt',
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-CSRF-TOKEN: ' . $csrfToken,
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'email' => 'staff@demo.localhost',
        'password' => 'password123',
    ]),
]);

$loginResponse = curl_exec($ch);
$loginStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($loginStatus === 200) {
    $loginData = json_decode($loginResponse, true);
    echo "✅ Login successful\n";
    echo "   Role: " . $loginData['user']['role'] . "\n\n";
} else {
    echo "❌ Login failed: {$loginStatus}\n";
    echo "Response: {$loginResponse}\n\n";
    exit(1);
}

// Step 3: Try to access dashboard
echo "Step 3: Accessing /admin/dashboard...\n";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'http://booking-saas.test/admin/dashboard',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_COOKIEJAR => __DIR__ . '/cookies.txt',
    CURLOPT_COOKIEFILE => __DIR__ . '/cookies.txt',
]);

$dashboardResponse = curl_exec($ch);
$dashboardStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

echo "HTTP Status: {$dashboardStatus}\n";
if ($redirectUrl) {
    echo "Redirect URL: {$redirectUrl}\n";
}

if ($dashboardStatus === 200) {
    echo "✅ Dashboard accessible!\n";
    echo "   Page contains 'dashboard': " . (stripos($dashboardResponse, 'dashboard') !== false ? 'YES' : 'NO') . "\n\n";
} else if ($dashboardStatus === 302) {
    echo "⚠️  Redirected (302)\n";
    echo "   This means user is not authenticated in session!\n\n";
} else {
    echo "❌ Dashboard not accessible\n";
    echo "   First 500 chars:\n";
    echo substr($dashboardResponse, 0, 500) . "\n\n";
}

// Clean up
@unlink(__DIR__ . '/cookies.txt');

echo "========================================\n";
echo "🔍 DIAGNOSIS:\n";
echo "========================================\n";
if ($dashboardStatus === 302 || $dashboardStatus === 401) {
    echo "❌ PROBLEM: Session is not persisting after login!\n\n";
    echo "SOLUTION:\n";
    echo "The login API sets the session but the browser might not be\n";
    echo "sending the session cookie in subsequent requests.\n\n";
    echo "Make sure SANCTUM_STATEFUL_DOMAINS is configured correctly.\n";
}
