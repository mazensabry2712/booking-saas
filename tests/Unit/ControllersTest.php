<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\Auth\SuperAdminAuthController;
use App\Http\Controllers\Auth\TenantAuthController;
use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\TenantController;
use App\Http\Controllers\Tenant\QueueController;
use App\Http\Controllers\Tenant\NotificationController;
use App\Http\Controllers\Tenant\ReportController;

class ControllersTest extends TestCase
{
    /**
     * Test SuperAdminAuthController exists and has required methods
     */
    public function test_super_admin_auth_controller_exists(): void
    {
        $this->assertTrue(class_exists(SuperAdminAuthController::class));

        $reflection = new \ReflectionClass(SuperAdminAuthController::class);
        $this->assertTrue($reflection->hasMethod('login'));
        $this->assertTrue($reflection->hasMethod('profile'));
        $this->assertTrue($reflection->hasMethod('logout'));
    }

    /**
     * Test TenantAuthController exists and has required methods
     */
    public function test_tenant_auth_controller_exists(): void
    {
        $this->assertTrue(class_exists(TenantAuthController::class));

        $reflection = new \ReflectionClass(TenantAuthController::class);
        $this->assertTrue($reflection->hasMethod('login'));
        $this->assertTrue($reflection->hasMethod('register'));
        $this->assertTrue($reflection->hasMethod('profile'));
        $this->assertTrue($reflection->hasMethod('logout'));
    }

    /**
     * Test DashboardController exists and has required methods
     */
    public function test_dashboard_controller_exists(): void
    {
        $this->assertTrue(class_exists(DashboardController::class));

        $reflection = new \ReflectionClass(DashboardController::class);
        $this->assertTrue($reflection->hasMethod('index'));
        $this->assertTrue($reflection->hasMethod('tenantsOverview'));
        $this->assertTrue($reflection->hasMethod('systemStats'));
    }

    /**
     * Test TenantController exists and has required methods
     */
    public function test_tenant_controller_exists(): void
    {
        $this->assertTrue(class_exists(TenantController::class));

        $reflection = new \ReflectionClass(TenantController::class);
        $this->assertTrue($reflection->hasMethod('index'));
        $this->assertTrue($reflection->hasMethod('store'));
        $this->assertTrue($reflection->hasMethod('show'));
        $this->assertTrue($reflection->hasMethod('update'));
        $this->assertTrue($reflection->hasMethod('destroy'));
    }

    /**
     * Test QueueController exists and has required methods
     */
    public function test_queue_controller_exists(): void
    {
        $this->assertTrue(class_exists(QueueController::class));

        $reflection = new \ReflectionClass(QueueController::class);
        $this->assertTrue($reflection->hasMethod('index'));
        $this->assertTrue($reflection->hasMethod('add'));
        $this->assertTrue($reflection->hasMethod('next'));
        $this->assertTrue($reflection->hasMethod('priority'));
    }

    /**
     * Test NotificationController exists and has required methods
     */
    public function test_notification_controller_exists(): void
    {
        $this->assertTrue(class_exists(NotificationController::class));

        $reflection = new \ReflectionClass(NotificationController::class);
        $this->assertTrue($reflection->hasMethod('index'));
        $this->assertTrue($reflection->hasMethod('show'));
        $this->assertTrue($reflection->hasMethod('markAsRead'));
        $this->assertTrue($reflection->hasMethod('markAllAsRead'));
    }

    /**
     * Test ReportController exists and has required methods
     */
    public function test_report_controller_exists(): void
    {
        $this->assertTrue(class_exists(ReportController::class));

        $reflection = new \ReflectionClass(ReportController::class);
        $this->assertTrue($reflection->hasMethod('dashboard'));
        $this->assertTrue($reflection->hasMethod('exportAppointmentsPDF'));
        $this->assertTrue($reflection->hasMethod('exportAppointmentsCSV'));
    }
}
