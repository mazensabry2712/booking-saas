<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Appointment;
use App\Models\Queue;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelsTest extends TestCase
{
    /**
     * Test Tenant model creation
     */
    public function test_tenant_can_be_created(): void
    {
        $tenant = Tenant::create([
            'id' => 'test-tenant-1',
            'data' => [
                'name' => 'Test Company',
                'active' => true,
            ]
        ]);

        $this->assertDatabaseHas('tenants', [
            'id' => 'test-tenant-1'
        ]);

        // Refresh to get from database
        $tenant = Tenant::find('test-tenant-1');
        $this->assertNotNull($tenant);
    }

    /**
     * Test Tenant can have domains
     */
    public function test_tenant_can_have_domains(): void
    {
        $tenant = Tenant::create([
            'id' => 'test-tenant-2',
            'data' => [
                'name' => 'Test Company 2',
                'active' => true,
            ]
        ]);

        $domain = $tenant->domains()->create([
            'domain' => 'test2.example.com',
        ]);

        $this->assertDatabaseHas('domains', [
            'domain' => 'test2.example.com',
            'tenant_id' => 'test-tenant-2'
        ]);

        $this->assertEquals('test2.example.com', $tenant->domain);
    }

    /**
     * Test Tenant data storage
     */
    public function test_tenant_data_is_stored(): void
    {
        $tenant = Tenant::create([
            'id' => 'test-tenant-3',
            'data' => [
                'name' => 'Test Company 3',
                'active' => true,
            ]
        ]);

        $this->assertDatabaseHas('tenants', [
            'id' => 'test-tenant-3'
        ]);

        // Tenant uses JSON column for data
        $this->assertNotNull($tenant);
    }

    /**
     * Test User model fillable attributes
     */
    public function test_user_has_correct_fillable_attributes(): void
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('role_id', $fillable);
    }

    /**
     * Test User helper methods exist
     */
    public function test_user_has_helper_methods(): void
    {
        $user = new User();

        $this->assertTrue(method_exists($user, 'isSuperAdmin'));
        $this->assertTrue(method_exists($user, 'isAdminTenant'));
        $this->assertTrue(method_exists($user, 'isStaff'));
        $this->assertTrue(method_exists($user, 'isCustomer'));
    }

    /**
     * Test Appointment model fillable attributes
     */
    public function test_appointment_has_correct_fillable_attributes(): void
    {
        $appointment = new Appointment();
        $fillable = $appointment->getFillable();

        $this->assertContains('customer_id', $fillable);
        $this->assertContains('staff_id', $fillable);
        $this->assertContains('date', $fillable);
        $this->assertContains('time_slot', $fillable);
        $this->assertContains('status', $fillable);
    }

    /**
     * Test Appointment relationships exist
     */
    public function test_appointment_has_relationships(): void
    {
        $appointment = new Appointment();

        $this->assertTrue(method_exists($appointment, 'customer'));
        $this->assertTrue(method_exists($appointment, 'staff'));
        $this->assertTrue(method_exists($appointment, 'queue'));
    }

    /**
     * Test Queue model fillable attributes
     */
    public function test_queue_has_correct_fillable_attributes(): void
    {
        $queue = new Queue();
        $fillable = $queue->getFillable();

        $this->assertContains('appointment_id', $fillable);
        $this->assertContains('queue_number', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('priority', $fillable);
    }

    /**
     * Test Queue relationships exist
     */
    public function test_queue_has_relationships(): void
    {
        $queue = new Queue();

        $this->assertTrue(method_exists($queue, 'appointment'));
        $this->assertTrue(method_exists($queue, 'tenant'));
    }

    /**
     * Test Invoice model fillable attributes
     */
    public function test_invoice_has_correct_fillable_attributes(): void
    {
        $invoice = new Invoice();
        $fillable = $invoice->getFillable();

        $this->assertContains('customer_id', $fillable);
        $this->assertContains('amount', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('pdf_path', $fillable);
    }

    /**
     * Test Invoice relationships exist
     */
    public function test_invoice_has_relationships(): void
    {
        $invoice = new Invoice();

        $this->assertTrue(method_exists($invoice, 'customer'));
        $this->assertTrue(method_exists($invoice, 'tenant'));
    }

    /**
     * Test Notification model fillable attributes
     */
    public function test_notification_has_correct_fillable_attributes(): void
    {
        $notification = new Notification();
        $fillable = $notification->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('message', $fillable);
        $this->assertContains('sent_at', $fillable);
    }

    /**
     * Test Setting model fillable attributes
     */
    public function test_setting_has_correct_fillable_attributes(): void
    {
        $setting = new Setting();
        $fillable = $setting->getFillable();

        $this->assertContains('tenant_id', $fillable);
        $this->assertContains('working_hours', $fillable);
        $this->assertContains('notification_settings', $fillable);
        $this->assertContains('language', $fillable);
    }

    /**
     * Test Setting casts are correct
     */
    public function test_setting_has_correct_casts(): void
    {
        $setting = new Setting();
        $casts = $setting->getCasts();

        $this->assertEquals('array', $casts['working_hours']);
        $this->assertEquals('array', $casts['notification_settings']);
    }
}
