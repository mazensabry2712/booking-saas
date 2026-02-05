<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{

    protected $fillable = [
        'customer_id',
        'staff_id',
        'service_id',
        'date',
        'time_slot',
        'status',
        'service_type',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function queue()
    {
        return $this->hasOne(Queue::class);
    }

    /**
     * Get service name (from service relation or service_type field)
     */
    public function getServiceNameAttribute()
    {
        if ($this->service) {
            return app()->getLocale() === 'ar' && $this->service->name_ar
                ? $this->service->name_ar
                : $this->service->name;
        }
        return $this->service_type;
    }
}
