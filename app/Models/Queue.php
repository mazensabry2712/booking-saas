<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{

    protected $fillable = [
        'appointment_id',
        'queue_number',
        'status',
        'priority',
        'estimated_wait_time',
        'served_at',
    ];

    protected $casts = [
        'served_at' => 'datetime',
        'priority' => 'boolean',
    ];

    // Relationships
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
