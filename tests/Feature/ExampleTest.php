<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the application base structure is correct
     */
    public function test_the_application_is_configured(): void
    {
        // Test that key config values are set
        $this->assertNotNull(config('app.name'));
        $this->assertNotNull(config('tenancy.tenant_model'));
        $this->assertEquals(\App\Models\Tenant::class, config('tenancy.tenant_model'));
    }

    /**
     * Test that multi-tenant middleware exists
     */
    public function test_tenant_middleware_exists(): void
    {
        $this->assertTrue(class_exists(\App\Http\Middleware\InitializeTenancyByDomain::class));
        $this->assertTrue(class_exists(\App\Http\Middleware\InitializeTenancyByToken::class));
    }

    /**
     * Test central domain configuration
     */
    public function test_central_domains_are_configured(): void
    {
        $centralDomains = config('tenancy.central_domains');
        $this->assertIsArray($centralDomains);
        $this->assertNotEmpty($centralDomains);
    }
}
