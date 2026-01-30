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
    echo "🔄 Adding phone numbers to existing customers...\n\n";

    $appointments = \App\Models\Appointment::with('customer')->get();

    foreach ($appointments as $appointment) {
        if ($appointment->customer && !$appointment->customer->phone) {
            $phone = '05' . rand(10000000, 99999999);
            $appointment->customer->update(['phone' => $phone]);
            echo "✓ Updated {$appointment->customer->name} with phone: {$phone}\n";
        }
    }

    echo "\n✅ Done!\n";
});
