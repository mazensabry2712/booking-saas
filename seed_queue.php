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
    echo "🔄 Adding test queue data...\n\n";

    // Get some appointments
    $appointments = \App\Models\Appointment::with('customer')->take(3)->get();

    if ($appointments->isEmpty()) {
        echo "❌ No appointments found. Please create appointments first.\n";
        return;
    }

    // Clear existing queue
    \App\Models\Queue::truncate();

    // Add to queue
    $queueNumber = 1;
    foreach ($appointments as $appointment) {
        $queue = \App\Models\Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => $queueNumber,
            'status' => 'waiting',
            'priority' => $queueNumber === 2, // Make second one priority
        ]);

        echo "✓ Added to queue: #{$queueNumber} - {$appointment->customer->name}\n";
        $queueNumber++;
    }

    echo "\n✅ Done! Total in queue: " . \App\Models\Queue::count() . "\n";
});
