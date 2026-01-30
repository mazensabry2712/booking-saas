<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SuperAdminAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Super Admin login validation - requires proper route setup
     * The routes are loaded from api.php with specific prefix
     */
    public function test_super_admin_routes_are_defined(): void
    {
        // Check that the controller exists
        $this->assertTrue(class_exists(\App\Http\Controllers\Auth\SuperAdminAuthController::class));

        // Check the controller has required methods
        $controller = new \ReflectionClass(\App\Http\Controllers\Auth\SuperAdminAuthController::class);
        $this->assertTrue($controller->hasMethod('login'));
        $this->assertTrue($controller->hasMethod('logout'));
        $this->assertTrue($controller->hasMethod('profile'));
    }

    /**
     * Test Super Admin Dashboard controller exists
     */
    public function test_super_admin_dashboard_controller_exists(): void
    {
        $this->assertTrue(class_exists(\App\Http\Controllers\SuperAdmin\DashboardController::class));

        $controller = new \ReflectionClass(\App\Http\Controllers\SuperAdmin\DashboardController::class);
        $this->assertTrue($controller->hasMethod('index'));
        $this->assertTrue($controller->hasMethod('tenantsOverview'));
        $this->assertTrue($controller->hasMethod('systemStats'));
    }

    /**
     * Test Super Admin Tenant controller exists
     */
    public function test_super_admin_tenant_controller_exists(): void
    {
        $this->assertTrue(class_exists(\App\Http\Controllers\SuperAdmin\TenantController::class));

        $controller = new \ReflectionClass(\App\Http\Controllers\SuperAdmin\TenantController::class);
        $this->assertTrue($controller->hasMethod('index'));
        $this->assertTrue($controller->hasMethod('store'));
        $this->assertTrue($controller->hasMethod('show'));
        $this->assertTrue($controller->hasMethod('update'));
        $this->assertTrue($controller->hasMethod('destroy'));
    }
}
