<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckSuperAdmin;
use App\Http\Middleware\CheckTokenAbility;
use App\Http\Middleware\InitializeTenancyByDomain;
use App\Http\Middleware\InitializeTenancyByToken;
use App\Http\Middleware\SetTenantLocale;

class MiddlewareTest extends TestCase
{
    /**
     * Test CheckRole middleware exists
     */
    public function test_check_role_middleware_exists(): void
    {
        $this->assertTrue(class_exists(CheckRole::class));
    }

    /**
     * Test CheckSuperAdmin middleware exists
     */
    public function test_check_super_admin_middleware_exists(): void
    {
        $this->assertTrue(class_exists(CheckSuperAdmin::class));
    }

    /**
     * Test CheckTokenAbility middleware exists
     */
    public function test_check_token_ability_middleware_exists(): void
    {
        $this->assertTrue(class_exists(CheckTokenAbility::class));
    }

    /**
     * Test InitializeTenancyByDomain middleware exists
     */
    public function test_initialize_tenancy_by_domain_middleware_exists(): void
    {
        $this->assertTrue(class_exists(InitializeTenancyByDomain::class));
    }

    /**
     * Test InitializeTenancyByToken middleware exists
     */
    public function test_initialize_tenancy_by_token_middleware_exists(): void
    {
        $this->assertTrue(class_exists(InitializeTenancyByToken::class));
    }

    /**
     * Test SetTenantLocale middleware exists
     */
    public function test_set_tenant_locale_middleware_exists(): void
    {
        $this->assertTrue(class_exists(SetTenantLocale::class));
    }

    /**
     * Test middlewares have handle method
     */
    public function test_middlewares_have_handle_method(): void
    {
        $middlewares = [
            CheckRole::class,
            CheckSuperAdmin::class,
            CheckTokenAbility::class,
            InitializeTenancyByDomain::class,
            InitializeTenancyByToken::class,
            SetTenantLocale::class,
        ];

        foreach ($middlewares as $middleware) {
            $reflection = new \ReflectionClass($middleware);
            $this->assertTrue(
                $reflection->hasMethod('handle'),
                "{$middleware} should have handle method"
            );
        }
    }
}
