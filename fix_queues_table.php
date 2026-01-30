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
    echo "Adding missing columns to queues table...\n\n";

    $columns = Schema::getColumnListing('queues');

    // Add priority column (using is_vip if exists)
    if (!in_array('priority', $columns)) {
        if (in_array('is_vip', $columns)) {
            echo "✓ Renaming is_vip to priority\n";
            Schema::table('queues', function ($table) {
                $table->renameColumn('is_vip', 'priority');
            });
        } else {
            echo "✓ Adding priority column\n";
            Schema::table('queues', function ($table) {
                $table->boolean('priority')->default(false)->after('status');
            });
        }
    } else {
        echo "- priority already exists\n";
    }

    // Add estimated_wait_time
    if (!in_array('estimated_wait_time', $columns)) {
        echo "✓ Adding estimated_wait_time column\n";
        Schema::table('queues', function ($table) {
            $table->integer('estimated_wait_time')->nullable()->after('priority');
        });
    } else {
        echo "- estimated_wait_time already exists\n";
    }

    // Add served_at
    if (!in_array('served_at', $columns)) {
        echo "✓ Adding served_at column\n";
        Schema::table('queues', function ($table) {
            $table->timestamp('served_at')->nullable()->after('estimated_wait_time');
        });
    } else {
        echo "- served_at already exists\n";
    }

    echo "\n✅ Done!\n";
});
