<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Custom attributes stored in 'data' JSON column
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
        ];
    }

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get tenant name
     */
    public function getNameAttribute()
    {
        $data = $this->data ?? [];
        return $data['name'] ?? 'Tenant';
    }

    /**
     * Set tenant name
     */
    public function setNameAttribute($value)
    {
        $data = $this->data ?? [];
        $data['name'] = $value;
        $this->attributes['data'] = json_encode($data);
    }

    /**
     * Get tenant active status
     */
    public function getActiveAttribute()
    {
        $data = $this->data ?? [];
        return $data['active'] ?? true;
    }

    /**
     * Set tenant active status
     */
    public function setActiveAttribute($value)
    {
        $data = $this->data ?? [];
        $data['active'] = (bool) $value;
        $this->attributes['data'] = json_encode($data);
    }

    /**
     * Get the primary domain
     */
    public function getDomainAttribute()
    {
        return $this->domains()->first()?->domain ?? 'unknown';
    }

    // Relationships
    public function settings()
    {
        return $this->hasOne(Setting::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
