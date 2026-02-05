<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{

    protected $fillable = [
        'appointment_id',
        'queue_number',
        'status',
        'is_vip',
        'counter_number',
    ];

    protected $casts = [
        'is_vip' => 'boolean',
    ];

    /**
     * Generate next queue number for today
     * Format: Simple sequential number (1, 2, 3...)
     */
    public static function generateQueueNumber(): string
    {
        // Get the last queue number for today
        $lastQueue = self::whereDate('created_at', now()->toDateString())
            ->orderByRaw('CAST(queue_number AS UNSIGNED) DESC')
            ->first();

        $nextNumber = $lastQueue ? ((int) $lastQueue->queue_number + 1) : 1;

        return (string) $nextNumber;
    }

    // Relationships
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    // Get customer through appointment
    public function customer()
    {
        return $this->hasOneThrough(
            User::class,
            Appointment::class,
            'id', // Foreign key on appointments table
            'id', // Foreign key on users table
            'appointment_id', // Local key on queues table
            'customer_id' // Local key on appointments table
        );
    }
}
