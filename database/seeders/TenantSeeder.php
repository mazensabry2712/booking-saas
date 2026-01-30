<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a demo tenant
        $tenant = Tenant::create([
            'id' => Str::uuid()->toString(),
        ]);

        // Set custom attributes
        $tenant->name = 'Demo Clinic';
        $tenant->active = true;
        $tenant->save();

        // Create domain
        $tenant->domains()->create([
            'domain' => 'demo.localhost',
        ]);

        $this->command->info('Demo tenant created: demo.localhost');
        $this->command->info('Tenant ID: ' . $tenant->id);

        // Run tenant database migrations and seeders
        $tenant->run(function () use ($tenant) {
            // Run migrations inside tenant context
            \Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            // Seed roles
            $roles = [
                'Admin' => ['manage_appointments', 'manage_staff', 'manage_queue', 'view_reports', 'manage_settings'],
                'Staff' => ['view_appointments', 'manage_queue'],
                'Customer' => ['book_appointment', 'view_own_appointments'],
            ];

            foreach ($roles as $name => $permissions) {
                \App\Models\Role::create([
                    'name' => $name,
                    'permissions' => json_encode($permissions),
                ]);
            }

            // Create admin user
            $adminRole = \App\Models\Role::where('name', 'Admin')->first();
            \App\Models\User::create([
                'role_id' => $adminRole->id,
                'name' => 'Admin User',
                'email' => 'admin@demo.localhost',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]);

            // Create staff user
            $staffRole = \App\Models\Role::where('name', 'Staff')->first();
            \App\Models\User::create([
                'role_id' => $staffRole->id,
                'name' => 'Staff User',
                'email' => 'staff@demo.localhost',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]);
        });

        $this->command->info('Demo users created (admin@demo.localhost / staff@demo.localhost)');
        $this->command->info('Password: password123');
    }
}

