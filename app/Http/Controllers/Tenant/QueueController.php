<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Appointment;
use App\Models\User;
use App\Jobs\SendQueueNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueueController extends Controller
{
    /**
     * Display a listing of queues
     */
    public function index(Request $request)
    {
        $tenant = tenant();

        $status = $request->input('status', 'Waiting');
        $date = $request->input('date', now()->toDateString());

        $queues = Queue::where('tenant_id', $tenant->id)
            ->whereDate('created_at', $date)
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->with(['appointment.customer', 'appointment.staff'])
            ->orderBy('priority', 'desc')
            ->orderBy('queue_number', 'asc')
            ->get();

        // Calculate estimated wait time for each queue
        $queues = $queues->map(function ($queue) {
            $queue->estimated_wait_time = $this->calculateEstimatedWaitTime($queue);
            return $queue;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $queues->count(),
                'waiting' => $queues->where('status', 'Waiting')->count(),
                'current' => $queues->where('status', 'Serving')->first(),
                'queues' => $queues,
            ]
        ]);
    }

    /**
     * Add appointment to queue
     */
    public function add(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
        ]);

        $tenant = tenant();
        $appointment = Appointment::where('tenant_id', $tenant->id)
            ->findOrFail($request->appointment_id);

        // Check if appointment already in queue
        $existingQueue = Queue::where('appointment_id', $appointment->id)->first();
        if ($existingQueue) {
            return response()->json([
                'error' => 'Appointment already in queue',
                'message' => 'This appointment is already added to the queue',
                'data' => $existingQueue
            ], 400);
        }

        // Get the last queue number for today
        $lastQueue = Queue::where('tenant_id', $tenant->id)
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('queue_number', 'desc')
            ->first();

        $queueNumber = $lastQueue ? $lastQueue->queue_number + 1 : 1;

        // Check if customer is VIP
        $customer = User::find($appointment->customer_id);
        $isVip = $customer->is_vip ?? false;
        $priority = $isVip ? 1 : 0;

        // Create queue entry
        $queue = Queue::create([
            'tenant_id' => $tenant->id,
            'appointment_id' => $appointment->id,
            'queue_number' => $queueNumber,
            'status' => 'Waiting',
            'priority' => $priority,
            'estimated_wait_time' => $this->calculateEstimatedWaitTime(null, $priority),
        ]);

        // Update estimated wait time for all waiting queues
        $this->updateAllEstimatedWaitTimes();

        return response()->json([
            'success' => true,
            'message' => 'Added to queue successfully',
            'data' => $queue->load(['appointment.customer', 'appointment.staff'])
        ], 201);
    }

    /**
     * Call next in queue
     */
    public function next(Request $request)
    {
        $tenant = tenant();

        // Mark current serving as served
        Queue::where('tenant_id', $tenant->id)
            ->where('status', 'Serving')
            ->update(['status' => 'Served']);

        // Get next queue entry (VIP priority first, then by queue number)
        $nextQueue = Queue::where('tenant_id', $tenant->id)
            ->where('status', 'Waiting')
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('priority', 'desc')
            ->orderBy('queue_number', 'asc')
            ->first();

        if (!$nextQueue) {
            return response()->json([
                'success' => false,
                'message' => 'No waiting customers in queue'
            ], 404);
        }

        // Update status to serving
        $nextQueue->update([
            'status' => 'Serving',
            'served_at' => now(),
        ]);

        // Update appointment status to Confirmed
        $nextQueue->appointment->update(['status' => 'Confirmed']);

        // Send notification to the customer
        $customer = $nextQueue->appointment->customer;
        $locale = $tenant->settings->language ?? 'en';
        SendQueueNotification::dispatch($nextQueue, $customer, 'next', $locale);

        // Check if there's someone ready next (only 1 person ahead)
        $readyQueue = Queue::where('tenant_id', $tenant->id)
            ->where('status', 'Waiting')
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('priority', 'desc')
            ->orderBy('queue_number', 'asc')
            ->first();

        if ($readyQueue) {
            $readyCustomer = $readyQueue->appointment->customer;
            SendQueueNotification::dispatch($readyQueue, $readyCustomer, 'ready', $locale);
        }

        // Update estimated wait time for remaining queues
        $this->updateAllEstimatedWaitTimes();

        return response()->json([
            'success' => true,
            'message' => 'Next customer called',
            'data' => $nextQueue->load(['appointment.customer', 'appointment.staff'])
        ]);
    }

    /**
     * Set or update priority (VIP)
     */
    public function priority(Request $request)
    {
        $request->validate([
            'queue_id' => 'required|exists:queues,id',
            'priority' => 'required|integer|min:0|max:10',
        ]);

        $tenant = tenant();
        $queue = Queue::where('tenant_id', $tenant->id)
            ->findOrFail($request->queue_id);

        if ($queue->status !== 'Waiting') {
            return response()->json([
                'error' => 'Invalid operation',
                'message' => 'Can only change priority for waiting queues'
            ], 400);
        }

        $queue->update(['priority' => $request->priority]);

        // Update estimated wait time for all queues
        $this->updateAllEstimatedWaitTimes();

        return response()->json([
            'success' => true,
            'message' => 'Priority updated successfully',
            'data' => $queue->load(['appointment.customer', 'appointment.staff'])
        ]);
    }

    /**
     * Skip queue entry
     */
    public function skip(Request $request, $id)
    {
        $tenant = tenant();
        $queue = Queue::where('tenant_id', $tenant->id)->findOrFail($id);

        if ($queue->status !== 'Waiting') {
            return response()->json([
                'error' => 'Invalid operation',
                'message' => 'Can only skip waiting queues'
            ], 400);
        }

        $queue->update(['status' => 'Skipped']);

        // Send notification to the customer
        $customer = $queue->appointment->customer;
        $locale = $tenant->settings->language ?? 'en';
        SendQueueNotification::dispatch($queue, $customer, 'skipped', $locale);

        // Update estimated wait time for remaining queues
        $this->updateAllEstimatedWaitTimes();

        return response()->json([
            'success' => true,
            'message' => 'Queue entry skipped',
            'data' => $queue
        ]);
    }

    /**
     * Get queue by status
     */
    public function byStatus($status)
    {
        $tenant = tenant();

        $queues = Queue::where('tenant_id', $tenant->id)
            ->where('status', $status)
            ->whereDate('created_at', now()->toDateString())
            ->with(['appointment.customer', 'appointment.staff'])
            ->orderBy('priority', 'desc')
            ->orderBy('queue_number', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $queues
        ]);
    }

    /**
     * Get customer's queue status
     */
    public function myQueue(Request $request)
    {
        $user = $request->user();
        $tenant = tenant();

        $queue = Queue::where('tenant_id', $tenant->id)
            ->whereHas('appointment', function ($query) use ($user) {
                $query->where('customer_id', $user->id);
            })
            ->where('status', 'Waiting')
            ->whereDate('created_at', now()->toDateString())
            ->with(['appointment'])
            ->first();

        if (!$queue) {
            return response()->json([
                'success' => false,
                'message' => 'You are not in the queue today'
            ], 404);
        }

        // Calculate position in queue
        $position = Queue::where('tenant_id', $tenant->id)
            ->where('status', 'Waiting')
            ->whereDate('created_at', now()->toDateString())
            ->where(function ($query) use ($queue) {
                $query->where('priority', '>', $queue->priority)
                    ->orWhere(function ($q) use ($queue) {
                        $q->where('priority', $queue->priority)
                            ->where('queue_number', '<', $queue->queue_number);
                    });
            })
            ->count() + 1;

        $queue->position = $position;
        $queue->estimated_wait_time = $this->calculateEstimatedWaitTime($queue);

        return response()->json([
            'success' => true,
            'data' => [
                'queue' => $queue,
                'position' => $position,
                'estimated_wait_time' => $queue->estimated_wait_time,
                'is_vip' => $queue->priority > 0,
            ]
        ]);
    }

    /**
     * Calculate estimated wait time
     */
    private function calculateEstimatedWaitTime($queue = null, $priority = 0)
    {
        $tenant = tenant();

        // Average service time per customer (in minutes)
        $avgServiceTime = 15;

        if ($queue) {
            // Count queues ahead with higher priority
            $queuesAhead = Queue::where('tenant_id', $tenant->id)
                ->where('status', 'Waiting')
                ->whereDate('created_at', now()->toDateString())
                ->where(function ($q) use ($queue) {
                    $q->where('priority', '>', $queue->priority)
                        ->orWhere(function ($query) use ($queue) {
                            $query->where('priority', $queue->priority)
                                ->where('queue_number', '<', $queue->queue_number);
                        });
                })
                ->count();
        } else {
            // For new queue, count all with higher or equal priority
            $queuesAhead = Queue::where('tenant_id', $tenant->id)
                ->where('status', 'Waiting')
                ->whereDate('created_at', now()->toDateString())
                ->where('priority', '>=', $priority)
                ->count();
        }

        // Calculate estimated time
        $estimatedMinutes = $queuesAhead * $avgServiceTime;

        return $estimatedMinutes;
    }

    /**
     * Update estimated wait time for all waiting queues
     */
    private function updateAllEstimatedWaitTimes()
    {
        $tenant = tenant();

        $waitingQueues = Queue::where('tenant_id', $tenant->id)
            ->where('status', 'Waiting')
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('priority', 'desc')
            ->orderBy('queue_number', 'asc')
            ->get();

        foreach ($waitingQueues as $queue) {
            $queue->update([
                'estimated_wait_time' => $this->calculateEstimatedWaitTime($queue)
            ]);
        }
    }

    /**
     * Get queue status by queue number (for public display)
     */
    public function getQueueStatus($queueNumber)
    {
        $tenant = tenant();

        $queue = Queue::where('tenant_id', $tenant->id)
            ->where('queue_number', $queueNumber)
            ->whereDate('created_at', now()->toDateString())
            ->with(['appointment.customer'])
            ->first();

        if (!$queue) {
            return response()->json([
                'success' => false,
                'message' => 'Queue not found for today'
            ], 404);
        }

        // Count people ahead
        $peopleAhead = Queue::where('tenant_id', $tenant->id)
            ->where('status', 'Waiting')
            ->whereDate('created_at', now()->toDateString())
            ->where(function ($q) use ($queue) {
                $q->where('priority', '>', $queue->priority)
                    ->orWhere(function ($query) use ($queue) {
                        $query->where('priority', $queue->priority)
                            ->where('queue_number', '<', $queue->queue_number);
                    });
            })
            ->count();

        // Get currently serving
        $currentlyServing = Queue::where('tenant_id', $tenant->id)
            ->where('status', 'Serving')
            ->whereDate('created_at', now()->toDateString())
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'queue_number' => $queue->queue_number,
                'status' => $queue->status,
                'priority' => $queue->priority,
                'people_ahead' => $peopleAhead,
                'estimated_wait_time' => $this->calculateEstimatedWaitTime($queue),
                'currently_serving' => $currentlyServing ? $currentlyServing->queue_number : null,
                'customer_name' => $queue->appointment->customer->name ?? 'N/A',
            ]
        ]);
    }
}
