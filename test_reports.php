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
    echo "📊 Testing Reports Page Data...\n\n";

    // General Statistics
    $stats = [
        'total_appointments' => \App\Models\Appointment::count(),
        'confirmed_appointments' => \App\Models\Appointment::where('status', 'confirmed')->count(),
        'pending_appointments' => \App\Models\Appointment::where('status', 'pending')->count(),
        'total_customers' => \App\Models\User::whereHas('role', function($q) {
            $q->where('name', 'Customer');
        })->count(),
    ];

    echo "📈 General Statistics:\n";
    echo "  Total Appointments: {$stats['total_appointments']}\n";
    echo "  Confirmed: {$stats['confirmed_appointments']}\n";
    echo "  Pending: {$stats['pending_appointments']}\n";
    echo "  Total Customers: {$stats['total_customers']}\n\n";

    // Appointments by status
    $appointmentsByStatus = \App\Models\Appointment::select('status', DB::raw('count(*) as count'))
        ->groupBy('status')
        ->get();

    echo "📋 Appointments by Status:\n";
    foreach ($appointmentsByStatus as $item) {
        echo "  {$item->status}: {$item->count}\n";
    }
    echo "\n";

    // Queue statistics
    $queueStats = [
        'waiting' => \App\Models\Queue::where('status', 'waiting')->count(),
        'serving' => \App\Models\Queue::where('status', 'serving')->count(),
        'completed' => \App\Models\Queue::where('status', 'completed')->count(),
        'priority' => \App\Models\Queue::where('priority', true)->whereIn('status', ['waiting', 'serving'])->count(),
    ];

    echo "🕐 Queue Statistics:\n";
    echo "  Waiting: {$queueStats['waiting']}\n";
    echo "  Serving: {$queueStats['serving']}\n";
    echo "  Completed: {$queueStats['completed']}\n";
    echo "  Priority: {$queueStats['priority']}\n\n";

    // Staff performance
    $staffPerformance = \App\Models\User::whereHas('role', function($q) {
            $q->whereIn('name', ['Admin Tenant', 'Staff']);
        })
        ->withCount(['staffAppointments' => function($q) {
            $q->where('status', 'confirmed');
        }])
        ->having('staff_appointments_count', '>', 0)
        ->orderBy('staff_appointments_count', 'desc')
        ->get();

    echo "👥 Staff Performance:\n";
    foreach ($staffPerformance as $staff) {
        echo "  {$staff->name}: {$staff->staff_appointments_count} appointments\n";
    }
    echo "\n";

    // Service types
    $serviceTypes = \App\Models\Appointment::whereNotNull('service_type')
        ->select('service_type', DB::raw('count(*) as count'))
        ->groupBy('service_type')
        ->orderBy('count', 'desc')
        ->get();

    echo "🛠️ Service Types:\n";
    foreach ($serviceTypes as $service) {
        echo "  {$service->service_type}: {$service->count}\n";
    }

    echo "\n✅ All data retrieved successfully!\n";
});
