<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\QueueController;
use App\Http\Controllers\Web\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Customer Routes (Tenant-aware)
Route::middleware(['tenant', 'tenant.locale'])->group(function () {

    // Welcome/Home page
    Route::get('/', function () {
        return redirect()->route('customer.booking');
    });

    // Login page
    Route::get('/login', [AdminController::class, 'login'])->name('login');

    // Auth API endpoints (for AJAX)
    Route::prefix('api/auth')->group(function () {
        Route::post('/login', [\App\Http\Controllers\Auth\TenantAuthController::class, 'login']);
        Route::post('/logout', [\App\Http\Controllers\Auth\TenantAuthController::class, 'logout'])->middleware('auth');
    });

    // Logout route (for forms)
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->middleware('auth')->name('logout');

    // Customer Booking
    Route::get('/book', [CustomerController::class, 'booking'])->name('customer.booking');
    Route::get('/my-queue', [CustomerController::class, 'myQueue'])->name('customer.my-queue');

    // Public Queue Dashboard
    Route::get('/queue', [QueueController::class, 'dashboard'])->name('queue.status');
});

// Admin Routes (Protected)
Route::middleware(['tenant', 'tenant.locale', 'auth', 'role:Admin Tenant|Staff'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Appointments Management
    Route::get('/appointments', [AdminController::class, 'appointments'])->name('appointments');

    // API endpoints for AJAX (inside admin context)
    Route::prefix('api')->group(function () {
        // Appointments
        Route::post('/appointments', [AdminController::class, 'storeAppointment'])->name('api.appointments.store');
        Route::get('/appointments/{id}', [AdminController::class, 'showAppointment'])->name('api.appointments.show');
        Route::put('/appointments/{id}', [AdminController::class, 'updateAppointment'])->name('api.appointments.update');
        Route::delete('/appointments/{id}', [AdminController::class, 'destroyAppointment'])->name('api.appointments.destroy');

        // Queue Management
        Route::post('/queue/add', [AdminController::class, 'addToQueue'])->name('api.queue.add');
        Route::post('/queue/call-next', [AdminController::class, 'callNext'])->name('api.queue.callNext');
        Route::post('/queue/{id}/serve', [AdminController::class, 'serveQueue'])->name('api.queue.serve');
        Route::post('/queue/{id}/complete', [AdminController::class, 'completeQueue'])->name('api.queue.complete');
        Route::post('/queue/{id}/priority', [AdminController::class, 'setQueuePriority'])->name('api.queue.priority');
        Route::delete('/queue/{id}', [AdminController::class, 'removeQueue'])->name('api.queue.remove');
    });

    // Queue Management
    Route::get('/queue', [AdminController::class, 'queue'])->name('queue');

    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
});

