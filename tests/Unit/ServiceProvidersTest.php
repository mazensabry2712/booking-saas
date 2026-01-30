<?php

namespace Tests\Unit;

use Tests\TestCase;

class ServiceProvidersTest extends TestCase
{
    /**
     * Test AppServiceProvider exists
     */
    public function test_app_service_provider_exists(): void
    {
        $this->assertTrue(class_exists(\App\Providers\AppServiceProvider::class));
    }

    /**
     * Test TenancyServiceProvider exists
     */
    public function test_tenancy_service_provider_exists(): void
    {
        $this->assertTrue(class_exists(\App\Providers\TenancyServiceProvider::class));
    }

    /**
     * Test providers are properly registered
     */
    public function test_providers_are_registered(): void
    {
        $providers = config('app.providers', []);

        // Check in bootstrap/providers.php
        $bootProviders = require base_path('bootstrap/providers.php');

        $this->assertContains(
            \App\Providers\AppServiceProvider::class,
            $bootProviders,
            'AppServiceProvider should be registered'
        );
    }

    /**
     * Test service container bindings
     */
    public function test_service_container_works(): void
    {
        $this->assertInstanceOf(\Illuminate\Http\Request::class, request());
        $this->assertInstanceOf(\Illuminate\Config\Repository::class, app('config'));
    }
}
