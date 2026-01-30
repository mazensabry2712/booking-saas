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
    echo "Checking status column in queues table...\n\n";

    $result = DB::select("SHOW COLUMNS FROM queues LIKE 'status'");

    if (!empty($result)) {
        $column = $result[0];
        echo "Column Type: {$column->Type}\n";
        echo "Null: {$column->Null}\n";
        echo "Default: {$column->Default}\n\n";

        // Fix the status column to allow all needed values
        echo "Fixing status column to allow: waiting, serving, completed, cancelled\n\n";

        DB::statement("ALTER TABLE queues MODIFY COLUMN status ENUM('waiting', 'serving', 'completed', 'cancelled') NOT NULL DEFAULT 'waiting'");

        echo "✅ Status column fixed!\n";
    } else {
        echo "❌ Status column not found!\n";
    }
});
