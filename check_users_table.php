<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenantId = 'b7c2ee89-ce22-4287-a4a9-7308e3851a3b';
$tenant = \App\Models\Tenant::find($tenantId);

if (!$tenant) {
    echo "❌ Tenant not found!\n";
    exit(1);
}

$tenant->run(function () {
    echo "Checking users table structure...\n\n";

    $columns = Schema::getColumnListing('users');

    echo "Columns in users table:\n";
    foreach ($columns as $column) {
        echo "  - $column\n";
    }

    echo "\nChecking if phone column exists: ";
    echo in_array('phone', $columns) ? "✓ YES\n" : "✗ NO\n";
});
