<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{

    protected $fillable = [
        'tenant_id',
        'appointment_id',
        'queue_number',
        'status',
        'priority',
        'estimated_wait_time',
        'served_at',
    ];

    protected $casts = [
        'served_at' => 'datetime',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
