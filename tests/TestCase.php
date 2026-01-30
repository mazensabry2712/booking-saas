<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use App\Models\Tenant;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected ?Tenant $tenant = null;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Run central migrations
        $this->runCentralMigrations();
    }

    /**
     * Run central database migrations
     */
    protected function runCentralMigrations(): void
    {
        // Create tenants table if not exists
        if (!Schema::hasTable('tenants')) {
            Schema::create('tenants', function ($table) {
                $table->string('id')->primary();
                $table->timestamps();
                $table->json('data')->nullable();
            });
        }

        // Create domains table if not exists
        if (!Schema::hasTable('domains')) {
            Schema::create('domains', function ($table) {
                $table->increments('id');
                $table->string('domain', 255)->unique();
                $table->string('tenant_id');
                $table->timestamps();
                $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
            });
        }

        // Create cache table if not exists
        if (!Schema::hasTable('cache')) {
            Schema::create('cache', function ($table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration');
            });
        }

        // Create cache_locks table if not exists
        if (!Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function ($table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->integer('expiration');
            });
        }

        // Create jobs table if not exists
        if (!Schema::hasTable('jobs')) {
            Schema::create('jobs', function ($table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }
    }

    /**
     * Create a tenant for testing
     */
    protected function createTenant(string $id = 'test-tenant', string $domain = 'test.localhost'): Tenant
    {
        $this->tenant = Tenant::create([
            'id' => $id,
            'data' => [
                'name' => 'Test Tenant',
                'active' => true,
            ]
        ]);

        $this->tenant->domains()->create([
            'domain' => $domain,
        ]);

        return $this->tenant;
    }

    /**
     * Initialize tenancy context for testing
     */
    protected function initializeTenancy(?Tenant $tenant = null): void
    {
        $tenant = $tenant ?? $this->tenant;

        if ($tenant) {
            tenancy()->initialize($tenant);
        }
    }

    /**
     * End tenancy context
     */
    protected function endTenancy(): void
    {
        tenancy()->end();
    }
}
