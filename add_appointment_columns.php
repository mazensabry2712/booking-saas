<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

$tenant = App\Models\Tenant::first();
tenancy()->initialize($tenant);

echo "Adding columns to appointments table...\n";

Schema::table('appointments', function (Blueprint $table) {
    if (!Schema::hasColumn('appointments', 'service_type')) {
        $table->string('service_type')->nullable();
        echo "✓ Added service_type column\n";
    } else {
        echo "- service_type already exists\n";
    }

    if (!Schema::hasColumn('appointments', 'notes')) {
        $table->text('notes')->nullable();
        echo "✓ Added notes column\n";
    } else {
        echo "- notes already exists\n";
    }
});

echo "\nDone!\n";
