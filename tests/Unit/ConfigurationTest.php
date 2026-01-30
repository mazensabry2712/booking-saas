<?php

namespace Tests\Unit;

use Tests\TestCase;

class ConfigurationTest extends TestCase
{
    /**
     * Test app configuration is correct
     */
    public function test_app_configuration_exists(): void
    {
        $this->assertNotNull(config('app.name'));
        $this->assertNotNull(config('app.env'));
    }

    /**
     * Test database configuration is correct
     */
    public function test_database_configuration_exists(): void
    {
        $this->assertNotNull(config('database.default'));
        $this->assertIsArray(config('database.connections'));
    }

    /**
     * Test mail configuration is correct
     */
    public function test_mail_configuration_exists(): void
    {
        $this->assertNotNull(config('mail.default'));
        $this->assertIsArray(config('mail.mailers'));
    }

    /**
     * Test queue configuration is correct
     */
    public function test_queue_configuration_exists(): void
    {
        $this->assertNotNull(config('queue.default'));
        $this->assertIsArray(config('queue.connections'));
    }

    /**
     * Test tenancy configuration is correct
     */
    public function test_tenancy_configuration_exists(): void
    {
        $this->assertNotNull(config('tenancy.tenant_model'));
        $this->assertIsArray(config('tenancy.central_domains'));
        $this->assertIsArray(config('tenancy.bootstrappers'));
    }

    /**
     * Test tenancy uses custom tenant model
     */
    public function test_tenancy_uses_custom_tenant_model(): void
    {
        $this->assertEquals(
            \App\Models\Tenant::class,
            config('tenancy.tenant_model')
        );
    }

    /**
     * Test central domains include localhost
     */
    public function test_central_domains_include_localhost(): void
    {
        $centralDomains = config('tenancy.central_domains');

        $this->assertTrue(
            in_array('localhost', $centralDomains) || in_array('127.0.0.1', $centralDomains),
            'Central domains should include localhost or 127.0.0.1'
        );
    }

    /**
     * Test sanctum configuration exists
     */
    public function test_sanctum_configuration_exists(): void
    {
        $this->assertIsArray(config('sanctum.stateful'));
    }

    /**
     * Test permission configuration exists
     */
    public function test_permission_configuration_exists(): void
    {
        $this->assertNotNull(config('permission.models.permission'));
        $this->assertNotNull(config('permission.models.role'));
    }
}
