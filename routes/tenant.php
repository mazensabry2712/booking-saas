<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Controllers\Tenant\AppointmentController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

// Booking Form - Public Page
Route::get('/', function () {
    return redirect()->route('booking.form');
});

Route::get('/book', function () {
    return view('customer.booking');
})->name('booking.form');

// Queue Status Page
Route::get('/queue/status', function () {
    return view('customer.queue-status');
})->name('queue.status');

// Public API Routes
Route::prefix('api')->group(function () {
    // Get staff list
    Route::get('staff', function () {
        $staffRole = \App\Models\Role::where('name', 'Staff')->first();
        if (!$staffRole) {
            return response()->json([]);
        }
        return \App\Models\User::where('role_id', $staffRole->id)
            ->select('id', 'name')
            ->get();
    });

    // Create appointment (public)
    Route::post('appointments', [AppointmentController::class, 'store']);
});
