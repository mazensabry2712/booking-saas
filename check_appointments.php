<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Initialize tenancy
$tenantId = 'b7c2ee89-ce22-4287-a4a9-7308e3851a3b';
$tenant = \App\Models\Tenant::find($tenantId);

if (!$tenant) {
    echo "❌ Tenant not found!\n";
    exit(1);
}

$tenant->run(function () {
    echo "🔍 Checking appointments...\n\n";

    $appointments = \App\Models\Appointment::with(['customer', 'staff'])
        ->orderBy('date', 'desc')
        ->get();

    echo "Total appointments: " . $appointments->count() . "\n\n";

    if ($appointments->count() > 0) {
        echo "Appointments list:\n";
        echo str_repeat('-', 80) . "\n";

        foreach ($appointments as $appointment) {
            echo "ID: {$appointment->id}\n";
            echo "Customer: " . ($appointment->customer?->name ?? 'N/A') . "\n";
            echo "Date: {$appointment->date->format('Y-m-d')}\n";
            echo "Time: {$appointment->time_slot}\n";
            echo "Service: " . ($appointment->service_type ?? 'N/A') . "\n";
            echo "Status: {$appointment->status}\n";
            echo str_repeat('-', 80) . "\n";
        }
    } else {
        echo "No appointments found.\n";
    }
});
