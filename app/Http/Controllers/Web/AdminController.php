<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Role;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\WorkingDay;
use App\Models\StaffSchedule;

class AdminController extends Controller
{
    /**
     * Show login page
     */
    public function login()
    {
        return view('auth.login');
    }

    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    /**
     * Show appointments page
     */
    public function appointments(Request $request)
    {
        // Build query with filters
        $query = Appointment::with(['customer', 'staff']);

        // Date filter
        if ($request->filled('date_filter')) {
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('date', today());
                    break;
                case 'week':
                    $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('date', now()->month)->whereYear('date', now()->year);
                    break;
                case 'custom':
                    if ($request->filled('date_from')) {
                        $query->whereDate('date', '>=', $request->date_from);
                    }
                    if ($request->filled('date_to')) {
                        $query->whereDate('date', '<=', $request->date_to);
                    }
                    break;
            }
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Staff filter
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        // Service name filter (from Services table - Nutrition, beauty, etc.)
        if ($request->filled('service_name')) {
            $query->where('service_type', 'like', '%' . $request->service_name . '%');
        }

        // Service type filter (consultation, examination, follow-up, etc.)
        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        // Sorting
        $sortBy = $request->get('sort', 'date');
        $sortDir = $request->get('dir', 'desc');

        if ($sortBy === 'customer') {
            $query->join('users', 'appointments.customer_id', '=', 'users.id')
                  ->orderBy('users.name', $sortDir)
                  ->select('appointments.*');
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        // Paginate
        $appointments = $query->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'today' => Appointment::whereDate('date', today())->count(),
            'today_confirmed' => Appointment::whereDate('date', today())->where('status', 'confirmed')->count(),
            'pending' => Appointment::where('status', 'pending')->count(),
            'this_week' => Appointment::whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'cancelled_month' => Appointment::where('status', 'cancelled')
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->count(),
        ];

        // Get services from Services table (Nutrition, beauty, etc.)
        $services = Service::where('is_active', true)->get();

        // Get distinct service types from appointments (استشارة، كشف، متابعة، etc.)
        $serviceTypes = Appointment::whereNotNull('service_type')
            ->where('service_type', '!=', '')
            ->distinct()
            ->pluck('service_type');

        // Get staff for filters
        $staffRole = Role::whereIn('name', ['Staff', 'Admin Tenant'])->pluck('id');
        $staffMembers = User::whereIn('role_id', $staffRole)->get();

        return view('admin.appointments', compact('appointments', 'stats', 'services', 'serviceTypes', 'staffMembers'));
    }

    /**
     * Show queue management page
     */
    public function queue()
    {
        $queues = \App\Models\Queue::with(['appointment.customer', 'appointment.staff', 'appointment.service'])
            ->whereIn('status', ['waiting', 'serving'])
            ->orderBy('is_vip', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.queue', compact('queues'));
    }

    /**
     * Show reports page
     */
    public function reports()
    {
        // General Statistics
        $stats = [
            'total_appointments' => Appointment::count(),
            'confirmed_appointments' => Appointment::where('status', 'confirmed')->count(),
            'pending_appointments' => Appointment::where('status', 'pending')->count(),
            'total_customers' => User::whereHas('role', function($q) {
                $q->where('name', 'Customer');
            })->count(),
        ];

        // Appointments by status
        $appointmentsByStatus = Appointment::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // Queue statistics
        $queueStats = [
            'waiting' => \App\Models\Queue::where('status', 'waiting')->count(),
            'serving' => \App\Models\Queue::where('status', 'serving')->count(),
            'completed' => \App\Models\Queue::where('status', 'completed')->count(),
            'priority' => \App\Models\Queue::where('is_vip', true)->whereIn('status', ['waiting', 'serving'])->count(),
        ];

        // Staff performance
        $staffPerformance = User::whereHas('role', function($q) {
                $q->whereIn('name', ['Admin Tenant', 'Staff']);
            })
            ->withCount(['staffAppointments' => function($q) {
                $q->where('status', 'confirmed');
            }])
            ->having('staff_appointments_count', '>', 0)
            ->orderBy('staff_appointments_count', 'desc')
            ->get();

        // Service types
        $serviceTypes = Appointment::whereNotNull('service_type')
            ->select('service_type', DB::raw('count(*) as count'))
            ->groupBy('service_type')
            ->orderBy('count', 'desc')
            ->get();

        return view('admin.reports', compact('stats', 'appointmentsByStatus', 'queueStats', 'staffPerformance', 'serviceTypes'));
    }

    /**
     * Store new appointment (AJAX)
     */
    public function storeAppointment(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'customer_email' => 'nullable|email',
                'appointment_date' => 'required|date',
                'appointment_time' => 'required',
                'service_type' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ]);

            // Get or create customer
            $customerRole = Role::where('name', 'Customer')->first();

            $customer = User::firstOrCreate(
                ['email' => $validated['customer_email'] ?? $validated['customer_phone'] . '@temp.local'],
                [
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                    'password' => bcrypt('password123'),
                    'role_id' => $customerRole?->id,
                ]
            );

            // Update phone if customer exists but phone changed
            if (!$customer->phone || $customer->phone !== $validated['customer_phone']) {
                $customer->update(['phone' => $validated['customer_phone']]);
            }

            // Create appointment
            $appointment = Appointment::create([
                'customer_id' => $customer->id,
                'staff_id' => auth()->id(),
                'date' => $validated['appointment_date'],
                'time_slot' => $validated['appointment_time'],
                'status' => 'pending',
                'service_type' => $validated['service_type'],
                'notes' => $validated['notes'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة الحجز بنجاح',
                'data' => $appointment
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error storing appointment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحفظ'
            ], 500);
        }
    }

    /**
     * Get appointment details (AJAX)
     */
    public function showAppointment($id)
    {
        try {
            $appointment = Appointment::with(['customer', 'staff'])->findOrFail($id);

            $statusMap = [
                'pending' => 'قيد الانتظار',
                'confirmed' => 'مؤكد',
                'cancelled' => 'ملغي',
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $appointment->id,
                    'customer_name' => $appointment->customer?->name ?? 'غير محدد',
                    'customer_phone' => $appointment->customer?->phone ?? '-',
                    'customer_email' => $appointment->customer?->email ?? '-',
                    'date' => $appointment->date->format('Y-m-d'),
                    'time_slot' => $appointment->time_slot,
                    'service_type' => $appointment->service_type,
                    'notes' => $appointment->notes,
                    'status' => $appointment->status,
                    'status_ar' => $statusMap[$appointment->status] ?? $appointment->status,
                    'staff_name' => $appointment->staff?->name ?? '-',
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching appointment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحميل البيانات'
            ], 500);
        }
    }

    /**
     * Update appointment (AJAX)
     */
    public function updateAppointment(Request $request, $id)
    {
        try {
            $appointment = Appointment::findOrFail($id);

            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'customer_email' => 'nullable|email',
                'appointment_date' => 'required|date',
                'appointment_time' => 'required',
                'service_type' => 'nullable|string|max:255',
                'status' => 'required|in:pending,confirmed,cancelled',
                'notes' => 'nullable|string',
            ]);

            // Update customer info
            if ($appointment->customer) {
                $appointment->customer->update([
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                    'email' => $validated['customer_email'] ?? $appointment->customer->email,
                ]);
            }

            // Update appointment
            $appointment->update([
                'date' => $validated['appointment_date'],
                'time_slot' => $validated['appointment_time'],
                'service_type' => $validated['service_type'],
                'status' => $validated['status'],
                'notes' => $validated['notes'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تعديل الحجز بنجاح',
                'data' => $appointment->fresh(['customer', 'staff'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating appointment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التعديل'
            ], 500);
        }
    }

    /**
     * Quick status update (AJAX) - for changing status from dropdown
     */
    public function quickStatusUpdate(Request $request, $id)
    {
        try {
            $appointment = Appointment::findOrFail($id);

            $validated = $request->validate([
                'status' => 'required|in:pending,confirmed,cancelled,completed'
            ]);

            $appointment->update(['status' => $validated['status']]);

            return response()->json([
                'success' => true,
                'message' => app()->getLocale() === 'ar' ? 'تم تحديث الحالة بنجاح' : 'Status updated successfully',
                'data' => $appointment
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar' ? 'خطأ في البيانات' : 'Invalid data',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating appointment status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => app()->getLocale() === 'ar' ? 'حدث خطأ أثناء التحديث' : 'Error updating status'
            ], 500);
        }
    }

    /**
     * Delete appointment (AJAX)
     */
    public function destroyAppointment($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $appointment->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الحجز بنجاح'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting appointment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحذف'
            ], 500);
        }
    }

    /**
     * Export appointments to Excel
     */
    public function exportAppointmentsExcel(Request $request)
    {
        $period = $request->get('period', $request->get('date_filter', 'month'));
        $startDate = $request->get('start_date', $request->get('date_from'));
        $endDate = $request->get('end_date', $request->get('date_to'));

        $tenant = tenant();
        $fileName = 'appointments-' . $period . '-' . now()->format('Y-m-d') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AppointmentsExport($tenant, $period, $startDate, $endDate),
            $fileName
        );
    }

    /**
     * Add customer to queue (AJAX)
     */
    public function addToQueue(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'required|string|max:20',
                'customer_email' => 'nullable|email',
                'staff_id' => 'required|exists:users,id',
                'service_id' => 'required|exists:services,id',
                'is_priority' => 'nullable|boolean',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Get or create customer
            $customerRole = Role::where('name', 'Customer')->first();

            $customer = User::firstOrCreate(
                ['email' => $validated['customer_email'] ?? $validated['customer_phone'] . '@temp.local'],
                [
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                    'password' => bcrypt(\Illuminate\Support\Str::random(32)),
                    'role_id' => $customerRole?->id,
                ]
            );

            // Update name and phone if customer exists
            $customer->update([
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'],
            ]);

            // Get service info
            $service = Service::find($validated['service_id']);

            // Create appointment for today
            $appointment = Appointment::create([
                'customer_id' => $customer->id,
                'staff_id' => $validated['staff_id'],
                'service_id' => $validated['service_id'],
                'date' => now()->toDateString(),
                'time_slot' => now()->format('H:i'),
                'status' => 'pending',
                'service_type' => $service?->name,
            ]);

            // Add to queue with formatted queue number
            $queue = \App\Models\Queue::create([
                'appointment_id' => $appointment->id,
                'queue_number' => \App\Models\Queue::generateQueueNumber(),
                'status' => 'waiting',
                'is_vip' => $validated['is_priority'] ?? false,
                'notes' => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة العميل إلى قائمة الانتظار - رقم الدور: #' . $queue->queue_number,
                'data' => $queue->load(['appointment.customer', 'appointment.staff'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error adding to queue: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ'
            ], 500);
        }
    }

    /**
     * Call next in queue (AJAX)
     */
    public function callNext(Request $request)
    {
        try {
            // Get next waiting in queue (VIP first, then by ID)
            $next = \App\Models\Queue::where('status', 'waiting')
                ->orderBy('is_vip', 'desc')
                ->orderBy('id', 'asc')
                ->first();

            if (!$next) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد أحد في قائمة الانتظار'
                ]);
            }

            // Set this one as serving (allow multiple serving at same time)
            $next->update(['status' => 'serving']);

            return response()->json([
                'success' => true,
                'message' => 'الدور رقم #' . $next->queue_number . ' - ' . ($next->appointment?->customer?->name ?? 'غير محدد'),
                'data' => $next->load(['appointment.customer'])
            ]);

        } catch (\Exception $e) {
            \Log::error('Error calling next in queue: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ'
            ], 500);
        }
    }

    /**
     * Serve queue item (AJAX)
     */
    public function serveQueue($id)
    {
        try {
            $queue = \App\Models\Queue::findOrFail($id);

            // Update any currently serving to Served
            \App\Models\Queue::where('status', 'serving')->update(['status' => 'completed']);

            $queue->update(['status' => 'serving']);

            return response()->json([
                'success' => true,
                'message' => 'جاري خدمة العميل'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error serving queue: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ'
            ], 500);
        }
    }

    /**
     * Complete queue item (AJAX)
     */
    public function completeQueue($id)
    {
        try {
            $queue = \App\Models\Queue::findOrFail($id);

            $queue->update([
                'status' => 'completed'
            ]);

            // Update appointment status
            if ($queue->appointment) {
                $queue->appointment->update(['status' => 'confirmed']);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم إنهاء الخدمة بنجاح'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error completing queue: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ'
            ], 500);
        }
    }

    /**
     * Return queue item to waiting (AJAX)
     */
    public function returnToWaiting($id)
    {
        try {
            $queue = \App\Models\Queue::findOrFail($id);

            $queue->update(['status' => 'waiting']);

            return response()->json([
                'success' => true,
                'message' => 'تم إرجاع العميل لقائمة الانتظار'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error returning to waiting: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ'
            ], 500);
        }
    }

    /**
     * Set queue priority (AJAX)
     */
    public function setQueuePriority(Request $request, $id)
    {
        try {
            $queue = \App\Models\Queue::findOrFail($id);

            $validated = $request->validate([
                'priority' => 'required|boolean',
            ]);

            $queue->update(['is_vip' => $validated['priority']]);

            return response()->json([
                'success' => true,
                'message' => $validated['priority'] ? 'تم تعيين الأولوية' : 'تم إلغاء الأولوية'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error setting queue priority: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ'
            ], 500);
        }
    }

    /**
     * Get queue details (AJAX)
     */
    public function getQueue($id)
    {
        try {
            $queue = \App\Models\Queue::with(['appointment.customer', 'appointment.staff', 'appointment.service'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $queue
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم العثور على البيانات'
            ], 404);
        }
    }

    /**
     * Update queue (AJAX)
     */
    public function updateQueue(Request $request, $id)
    {
        try {
            $queue = \App\Models\Queue::with('appointment.customer')->findOrFail($id);

            // Update customer data
            if ($queue->appointment && $queue->appointment->customer) {
                $queue->appointment->customer->update([
                    'name' => $request->customer_name,
                    'phone' => $request->customer_phone,
                    'email' => $request->customer_email ?: null,
                ]);
            }

            // Update VIP status and notes
            $queue->update([
                'is_vip' => $request->is_vip ? true : false,
                'notes' => $request->notes ?: null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث البيانات بنجاح'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating queue: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ'
            ], 500);
        }
    }

    /**
     * Remove from queue (AJAX)
     */
    public function removeQueue($id)
    {
        try {
            $queue = \App\Models\Queue::findOrFail($id);
            $queue->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف العميل من القائمة'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error removing from queue: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ'
            ], 500);
        }
    }

    // ==================== SETTINGS ====================

    /**
     * Show settings page
     */
    public function settings()
    {
        $tenant = tenant();
        $settingsModel = \App\Models\Setting::where('tenant_id', $tenant->id)->first();

        $settings = $settingsModel ? $settingsModel->toArray() : [];

        return view('admin.settings', compact('settings'));
    }

    /**
     * Save business settings
     */
    public function saveSettings(Request $request)
    {
        try {
            $tenant = tenant();

            $data = $request->validate([
                'business_name' => 'nullable|string|max:255',
                'business_name_ar' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:50',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string|max:500',
                'whatsapp' => 'nullable|string|max:50',
                'facebook' => 'nullable|url|max:255',
                'instagram' => 'nullable|url|max:255',
                'twitter' => 'nullable|url|max:255',
                'tiktok' => 'nullable|url|max:255',
                'snapchat' => 'nullable|string|max:100',
            ]);

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoPath = $logo->store('logos/' . $tenant->id, 'public');
                $data['logo'] = $logoPath;
            }

            $settings = \App\Models\Setting::updateOrCreate(
                ['tenant_id' => $tenant->id],
                $data
            );

            return response()->json([
                'success' => true,
                'message' => __('Settings saved successfully!'),
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saving settings: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store new service
     */
    public function storeService(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'name_ar' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'duration' => 'required|integer|min:5|max:480',
                'price' => 'nullable|numeric|min:0',
                'is_active' => 'boolean',
            ]);

            $service = Service::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة الخدمة بنجاح',
                'data' => $service
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating service: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ'], 500);
        }
    }

    /**
     * Get service details
     */
    public function showService($id)
    {
        $service = Service::findOrFail($id);
        return response()->json(['success' => true, 'data' => $service]);
    }

    /**
     * Update service
     */
    public function updateService(Request $request, $id)
    {
        try {
            $service = Service::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'name_ar' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'duration' => 'required|integer|min:5|max:480',
                'price' => 'nullable|numeric|min:0',
                'is_active' => 'boolean',
            ]);

            $service->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'تم تعديل الخدمة بنجاح',
                'data' => $service
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating service: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ'], 500);
        }
    }

    /**
     * Delete service
     */
    public function destroyService($id)
    {
        try {
            $service = Service::findOrFail($id);
            $service->delete();

            return response()->json(['success' => true, 'message' => 'تم حذف الخدمة بنجاح']);
        } catch (\Exception $e) {
            \Log::error('Error deleting service: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ'], 500);
        }
    }

    /**
     * Store new time slot
     */
    public function storeTimeSlot(Request $request)
    {
        try {
            $validated = $request->validate([
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
            ]);

            $timeSlot = TimeSlot::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة الوقت بنجاح',
                'data' => $timeSlot
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating time slot: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ'], 500);
        }
    }

    /**
     * Toggle time slot status
     */
    public function toggleTimeSlot(Request $request, $id)
    {
        try {
            $timeSlot = TimeSlot::findOrFail($id);
            $timeSlot->update(['is_active' => $request->is_active]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Delete time slot
     */
    public function destroyTimeSlot($id)
    {
        try {
            TimeSlot::findOrFail($id)->delete();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Toggle working day status
     */
    public function toggleWorkingDay(Request $request, $id)
    {
        try {
            $workingDay = WorkingDay::findOrFail($id);
            $workingDay->update(['is_active' => $request->is_active]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Toggle staff service assignment
     */
    public function toggleStaffService(Request $request)
    {
        try {
            $user = User::findOrFail($request->staff_id);

            if ($request->attach) {
                $user->services()->syncWithoutDetaching([$request->service_id]);
            } else {
                $user->services()->detach($request->service_id);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Error toggling staff service: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Get services for dropdown (API)
     */
    public function getServices()
    {
        $services = Service::active()->get();
        return response()->json(['success' => true, 'data' => $services]);
    }

    /**
     * Get time slots for dropdown (API)
     */
    public function getTimeSlots()
    {
        $timeSlots = TimeSlot::active()->orderBy('start_time')->get();
        return response()->json(['success' => true, 'data' => $timeSlots]);
    }

    /**
     * Get working days (API)
     */
    public function getWorkingDays()
    {
        $workingDays = WorkingDay::active()->orderBy('day_of_week')->get();
        return response()->json(['success' => true, 'data' => $workingDays]);
    }

    /**
     * Get staff services (API)
     */
    public function getStaffServices($staffId)
    {
        $user = User::with('services')->findOrFail($staffId);
        return response()->json(['success' => true, 'data' => $user->services]);
    }

    // ==================== STAFF MANAGEMENT ====================

    /**
     * Show staff management page
     */
    public function staff()
    {
        $staffRole = Role::where('name', 'Staff')->first();
        $staffMembers = User::where('role_id', $staffRole?->id)
            ->with(['services', 'activeSchedules'])
            ->get();

        $services = Service::orderBy('name')->get();

        return view('admin.staff', compact('staffMembers', 'services'));
    }

    /**
     * Get staff member details (API)
     */
    public function showStaff($id)
    {
        try {
            $staff = User::with(['services', 'schedules'])->findOrFail($id);
            return response()->json(['success' => true, 'data' => $staff]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Staff not found'], 404);
        }
    }

    /**
     * Store new staff member (API)
     */
    public function storeStaff(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20',
                'password' => 'required|string|min:6',
                'specialization' => 'required|string|max:255',
                'specialization_ar' => 'nullable|string|max:255',
                'services' => 'array',
                'schedule' => 'array',
            ]);

            $staffRole = Role::where('name', 'Staff')->first();
            if (!$staffRole) {
                return response()->json(['success' => false, 'message' => 'Staff role not found'], 500);
            }

            DB::beginTransaction();

            // Create user
            $user = User::create([
                'role_id' => $staffRole->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
                'specialization' => $validated['specialization'],
                'specialization_ar' => $validated['specialization_ar'] ?? null,
            ]);

            // Attach services
            if (!empty($request->services)) {
                $user->services()->sync($request->services);
            }

            // Create schedule
            if (!empty($request->schedule)) {
                foreach ($request->schedule as $schedule) {
                    StaffSchedule::create([
                        'user_id' => $user->id,
                        'day_of_week' => $schedule['day_of_week'],
                        'start_time' => $schedule['start_time'],
                        'end_time' => $schedule['end_time'],
                        'is_active' => $schedule['is_active'] ?? true,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة الموظف بنجاح',
                'data' => $user->load(['services', 'schedules'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating staff: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ'], 500);
        }
    }

    /**
     * Update staff member (API)
     */
    public function updateStaff(Request $request, $id)
    {
        try {
            $staff = User::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'specialization' => 'nullable|string|max:255',
                'specialization_ar' => 'nullable|string|max:255',
                'services' => 'array',
                'schedule' => 'array',
            ]);

            DB::beginTransaction();

            // Update user
            $staff->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'specialization' => $validated['specialization'] ?? $staff->specialization,
                'specialization_ar' => $validated['specialization_ar'] ?? $staff->specialization_ar,
            ]);

            // Sync services
            $staff->services()->sync($request->services ?? []);

            // Update schedule - delete old and create new
            StaffSchedule::where('user_id', $staff->id)->delete();

            if (!empty($request->schedule)) {
                foreach ($request->schedule as $schedule) {
                    StaffSchedule::create([
                        'user_id' => $staff->id,
                        'day_of_week' => $schedule['day_of_week'],
                        'start_time' => $schedule['start_time'],
                        'end_time' => $schedule['end_time'],
                        'is_active' => $schedule['is_active'] ?? true,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تعديل الموظف بنجاح',
                'data' => $staff->load(['services', 'schedules'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating staff: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ'], 500);
        }
    }

    /**
     * Delete staff member (API)
     */
    public function destroyStaff($id)
    {
        try {
            $staff = User::findOrFail($id);

            // Delete schedules
            StaffSchedule::where('user_id', $staff->id)->delete();

            // Detach services
            $staff->services()->detach();

            // Delete user
            $staff->delete();

            return response()->json(['success' => true, 'message' => 'تم حذف الموظف بنجاح']);
        } catch (\Exception $e) {
            \Log::error('Error deleting staff: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'حدث خطأ'], 500);
        }
    }

    /**
     * Get staff by specialization (API for queue form)
     */
    public function getStaffBySpecialization($specialization)
    {
        try {
            $staff = User::where('specialization', $specialization)
                ->whereHas('role', function($q) {
                    $q->where('name', 'Staff');
                })
                ->get(['id', 'name', 'specialization', 'specialization_ar']);

            return response()->json(['success' => true, 'data' => $staff]);
        } catch (\Exception $e) {
            \Log::error('Error getting staff by specialization: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Get staff services as JSON (API for queue form)
     */
    public function getStaffServicesJson($id)
    {
        try {
            $staff = User::findOrFail($id);
            $services = $staff->services()->get(['services.id', 'name', 'name_ar', 'duration', 'price']);

            return response()->json(['success' => true, 'data' => $services]);
        } catch (\Exception $e) {
            \Log::error('Error getting staff services: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Get staff by service (API for booking)
     */
    public function getStaffByService($serviceId)
    {
        try {
            $staff = User::whereHas('services', function($q) use ($serviceId) {
                $q->where('services.id', $serviceId);
            })
            ->whereHas('role', function($q) {
                $q->where('name', 'Staff');
            })
            ->with(['activeSchedules'])
            ->get(['id', 'name']);

            return response()->json(['success' => true, 'data' => $staff]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }

    /**
     * Get staff schedule (API for booking)
     */
    public function getStaffSchedule($staffId)
    {
        try {
            $schedules = StaffSchedule::where('user_id', $staffId)
                ->where('is_active', true)
                ->orderBy('day_of_week')
                ->get();

            return response()->json(['success' => true, 'data' => $schedules]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error'], 500);
        }
    }
}
