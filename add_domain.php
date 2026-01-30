<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tenant = App\Models\Tenant::first();

if ($tenant) {
    // Check if domain already exists
    $exists = $tenant->domains()->where('domain', 'booking-saas.test')->exists();

    if (!$exists) {
        $tenant->domains()->create(['domain' => 'booking-saas.test']);
        echo "✅ Domain 'booking-saas.test' added successfully!\n";
    } else {
        echo "ℹ️  Domain 'booking-saas.test' already exists.\n";
    }

    echo "\nTenant Domains:\n";
    foreach ($tenant->domains as $domain) {
        echo "  - {$domain->domain}\n";
    }
} else {
    echo "❌ No tenant found. Please run: php artisan migrate:fresh --seed\n";
}
