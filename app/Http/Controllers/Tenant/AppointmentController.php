<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Queue;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AppointmentController extends Controller
{
    /**
     * Store a newly created appointment
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'customer_phone' => 'required|string|max:20',
                'appointment_date' => 'required|date',
                'staff_id' => 'nullable|exists:users,id',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Parse date and time from appointment_date
            $appointmentDateTime = \Carbon\Carbon::parse($validated['appointment_date']);
            $date = $appointmentDateTime->toDateString();
            $timeSlot = $appointmentDateTime->format('H:i');

            // Find or create customer
            $customerRole = Role::where('name', 'Customer')->first();

            $customer = User::where('email', $validated['customer_email'])->first();

            if (!$customer) {
                // Create new customer
                $customer = User::create([
                    'name' => $validated['customer_name'],
                    'email' => $validated['customer_email'],
                    'phone' => $validated['customer_phone'],
                    'password' => Hash::make(Str::random(16)),
                    'role_id' => $customerRole->id,
                ]);
            } else {
                // Update customer info if changed
                $customer->update([
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                ]);
            }

            // Create appointment
            $appointment = Appointment::create([
                'customer_id' => $customer->id,
                'staff_id' => $validated['staff_id'] ?? null,
                'date' => $date,
                'time_slot' => $timeSlot,
                'status' => 'Pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Add to queue
            $queue = Queue::create([
                'customer_id' => $customer->id,
                'appointment_id' => $appointment->id,
                'queue_number' => $this->generateQueueNumber(),
                'status' => 'waiting',
                'priority' => $customer->is_vip ? 1 : 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment booked successfully',
                'data' => [
                    'appointment' => $appointment,
                    'queue_number' => $queue->queue_number,
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while booking the appointment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique queue number
     */
    private function generateQueueNumber(): string
    {
        $today = date('Ymd');
        $count = Queue::whereDate('created_at', today())->count() + 1;
        return 'Q' . $today . '-' . sprintf('%04d', $count);
    }

    /**
     * Display appointments (for authenticated users)
     */
    public function index(Request $request)
    {
        $appointments = Appointment::with(['customer', 'staff'])
            ->orderBy('appointment_date', 'desc')
            ->paginate(20);

        return response()->json($appointments);
    }

    /**
     * Show a specific appointment
     */
    public function show($id)
    {
        $appointment = Appointment::with(['customer', 'staff'])->findOrFail($id);
        return response()->json($appointment);
    }

    /**
     * Update appointment
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        $validated = $request->validate([
            'appointment_date' => 'sometimes|date',
            'staff_id' => 'nullable|exists:users,id',
            'status' => 'sometimes|in:pending,confirmed,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $appointment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Appointment updated successfully',
            'data' => $appointment
        ]);
    }

    /**
     * Delete appointment
     */
    public function destroy($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Appointment deleted successfully'
        ]);
    }

    /**
     * Get customer's appointments
     */
    public function myAppointments(Request $request)
    {
        $user = $request->user();

        $appointments = Appointment::where('customer_id', $user->id)
            ->with('staff')
            ->orderBy('appointment_date', 'desc')
            ->get();

        return response()->json($appointments);
    }
}
