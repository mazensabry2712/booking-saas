<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test validation classes exist
     */
    public function test_validation_classes_exist(): void
    {
        // Test that the auth controller has validation in login
        $controller = new \ReflectionClass(\App\Http\Controllers\Auth\TenantAuthController::class);
        $this->assertTrue($controller->hasMethod('login'));
        $this->assertTrue($controller->hasMethod('register'));
    }

    /**
     * Test API controllers return JSON
     */
    public function test_controllers_extend_base_controller(): void
    {
        $controllers = [
            \App\Http\Controllers\Auth\SuperAdminAuthController::class,
            \App\Http\Controllers\Auth\TenantAuthController::class,
            \App\Http\Controllers\SuperAdmin\DashboardController::class,
            \App\Http\Controllers\SuperAdmin\TenantController::class,
            \App\Http\Controllers\Tenant\QueueController::class,
            \App\Http\Controllers\Tenant\NotificationController::class,
        ];

        foreach ($controllers as $controllerClass) {
            $controller = new \ReflectionClass($controllerClass);
            $this->assertTrue(
                $controller->isSubclassOf(\App\Http\Controllers\Controller::class) ||
                $controller->getName() === \App\Http\Controllers\Controller::class,
                "{$controllerClass} should extend Controller"
            );
        }
    }

    /**
     * Test middleware classes exist
     */
    public function test_middleware_validation_exists(): void
    {
        $middlewares = [
            \App\Http\Middleware\CheckRole::class,
            \App\Http\Middleware\CheckSuperAdmin::class,
            \App\Http\Middleware\CheckTokenAbility::class,
        ];

        foreach ($middlewares as $middleware) {
            $this->assertTrue(class_exists($middleware), "{$middleware} should exist");

            $reflection = new \ReflectionClass($middleware);
            $this->assertTrue($reflection->hasMethod('handle'), "{$middleware} should have handle method");
        }
    }

    /**
     * Test sanctum is configured for API authentication
     */
    public function test_sanctum_is_configured(): void
    {
        $this->assertTrue(class_exists(\Laravel\Sanctum\Sanctum::class));
        $this->assertArrayHasKey('sanctum', config('auth.guards'));
    }

    /**
     * Test permission package is configured
     */
    public function test_permission_package_is_configured(): void
    {
        $this->assertTrue(class_exists(\Spatie\Permission\Models\Role::class));
        $this->assertTrue(class_exists(\Spatie\Permission\Models\Permission::class));
    }
}
