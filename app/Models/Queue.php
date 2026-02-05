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
