<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Appointment;
use App\Models\User;
use App\Models\Role;

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
    public function appointments()
    {
        $appointments = Appointment::with(['customer', 'staff'])
            ->orderBy('date', 'desc')
            ->orderBy('time_slot', 'desc')
            ->get();

        return view('admin.appointments', compact('appointments'));
    }

    /**
     * Show queue management page
     */
    public function queue()
    {
        $queues = \App\Models\Queue::with(['appointment.customer', 'appointment.staff'])
            ->whereIn('status', ['waiting', 'serving'])
            ->orderBy('priority', 'desc')
            ->orderBy('queue_number', 'asc')
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
            'priority' => \App\Models\Queue::where('priority', true)->whereIn('status', ['waiting', 'serving'])->count(),
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
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحفظ: ' . $e->getMessage()
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
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
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
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء التعديل: ' . $e->getMessage()
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
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()
            ], 500);
        }
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
                'service_type' => 'nullable|string|max:255',
                'is_priority' => 'nullable|boolean',
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

            // Update phone if customer exists
            if (!$customer->phone || $customer->phone !== $validated['customer_phone']) {
                $customer->update(['phone' => $validated['customer_phone']]);
            }

            // Create appointment for today
            $appointment = Appointment::create([
                'customer_id' => $customer->id,
                'staff_id' => auth()->id(),
                'date' => now()->toDateString(),
                'time_slot' => now()->format('H:i'),
                'status' => 'pending',
                'service_type' => $validated['service_type'],
            ]);

            // Get next queue number
            $lastQueue = \App\Models\Queue::whereDate('created_at', now()->toDateString())
                ->orderBy('queue_number', 'desc')
                ->first();

            $queueNumber = $lastQueue ? $lastQueue->queue_number + 1 : 1;

            // Add to queue
            $queue = \App\Models\Queue::create([
                'appointment_id' => $appointment->id,
                'queue_number' => $queueNumber,
                'status' => 'waiting',
                'priority' => $validated['is_priority'] ?? false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة العميل إلى قائمة الانتظار - رقم الدور: ' . $queueNumber,
                'data' => $queue->load(['appointment.customer'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Call next in queue (AJAX)
     */
    public function callNext(Request $request)
    {
        try {
            // Get next waiting in queue (priority first)
            $next = \App\Models\Queue::where('status', 'waiting')
                ->orderBy('priority', 'desc')
                ->orderBy('queue_number', 'asc')
                ->first();

            if (!$next) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد أحد في قائمة الانتظار'
                ]);
            }

            // Update any currently serving to waiting
            \App\Models\Queue::where('status', 'serving')->update(['status' => 'waiting']);

            // Set this one as serving
            $next->update(['status' => 'serving']);

            return response()->json([
                'success' => true,
                'message' => 'الدور رقم ' . $next->queue_number . ' - ' . ($next->appointment?->customer?->name ?? 'غير محدد'),
                'data' => $next->load(['appointment.customer'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
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

            // Update any currently serving to waiting
            \App\Models\Queue::where('status', 'serving')->update(['status' => 'waiting']);

            $queue->update(['status' => 'serving']);

            return response()->json([
                'success' => true,
                'message' => 'جاري خدمة العميل'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
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
                'status' => 'completed',
                'served_at' => now()
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
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
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

            $queue->update(['priority' => $validated['priority']]);

            return response()->json([
                'success' => true,
                'message' => $validated['priority'] ? 'تم تعيين الأولوية' : 'تم إلغاء الأولوية'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
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
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }
}
