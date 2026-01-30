<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'phone',
        'password',
        'is_vip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'customer_id');
    }

    public function staffAppointments()
    {
        return $this->hasMany(Appointment::class, 'staff_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'customer_id');
    }

    // Helper methods
    public function isSuperAdmin(): bool
    {
        return $this->role && $this->role->name === 'Super Admin';
    }

    public function isAdminTenant(): bool
    {
        return $this->role && $this->role->name === 'Admin Tenant';
    }

    public function isStaff(): bool
    {
        return $this->role && $this->role->name === 'Staff';
    }

    public function isCustomer(): bool
    {
        return $this->role && $this->role->name === 'Customer';
    }

    public function getRoleName(): ?string
    {
        return $this->role?->name;
    }
}
