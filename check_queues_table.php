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
    echo "Checking queues table structure...\n\n";

    $columns = Schema::getColumnListing('queues');

    echo "Columns in queues table:\n";
    foreach ($columns as $column) {
        echo "  - $column\n";
    }

    $requiredColumns = ['id', 'appointment_id', 'queue_number', 'status', 'priority', 'estimated_wait_time', 'served_at', 'created_at', 'updated_at'];

    echo "\nChecking required columns:\n";
    foreach ($requiredColumns as $col) {
        $exists = in_array($col, $columns);
        echo "  " . ($exists ? "✓" : "✗") . " $col\n";
    }
});
