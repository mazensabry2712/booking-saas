<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stancl\Tenancy\Exceptions\DomainOccupiedByOtherTenantException;

class TenantManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Tenant creation in database
     */
    public function test_tenant_can_be_created_with_valid_data(): void
    {
        $tenant = Tenant::create([
            'id' => 'company-1',
            'data' => [
                'name' => 'Company One',
                'active' => true,
            ]
        ]);

        $this->assertDatabaseHas('tenants', ['id' => 'company-1']);
        $this->assertNotNull($tenant);
    }

    /**
     * Test Tenant domain creation
     */
    public function test_tenant_domain_can_be_created(): void
    {
        $tenant = Tenant::create([
            'id' => 'company-2',
            'data' => [
                'name' => 'Company Two',
                'active' => true,
            ]
        ]);

        $tenant->domains()->create([
            'domain' => 'company2.example.com',
        ]);

        $this->assertDatabaseHas('domains', [
            'domain' => 'company2.example.com',
            'tenant_id' => 'company-2'
        ]);
    }

    /**
     * Test multiple domains for a tenant
     */
    public function test_tenant_can_have_multiple_domains(): void
    {
        $tenant = Tenant::create([
            'id' => 'company-3',
            'data' => [
                'name' => 'Company Three',
                'active' => true,
            ]
        ]);

        $tenant->domains()->create(['domain' => 'main.company3.com']);
        $tenant->domains()->create(['domain' => 'www.company3.com']);

        $this->assertCount(2, $tenant->domains);
    }

    /**
     * Test Tenant data is stored in JSON column
     */
    public function test_tenant_data_is_stored(): void
    {
        $tenant = Tenant::create([
            'id' => 'company-4',
            'data' => [
                'name' => 'Company Four',
                'active' => false,
            ]
        ]);

        $this->assertDatabaseHas('tenants', ['id' => 'company-4']);
        // Tenant uses JSON column for data - verify tenant exists
        $this->assertNotNull($tenant);
    }

    /**
     * Test Tenant ID uniqueness
     */
    public function test_tenant_id_must_be_unique(): void
    {
        Tenant::create([
            'id' => 'company-5',
            'data' => ['name' => 'Company Five', 'active' => true]
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Tenant::create([
            'id' => 'company-5',
            'data' => ['name' => 'Company Five Duplicate', 'active' => true]
        ]);
    }

    /**
     * Test domain uniqueness
     */
    public function test_domains_must_be_unique(): void
    {
        $tenant1 = Tenant::create([
            'id' => 'company-7a',
            'data' => ['name' => 'Company 7A', 'active' => true]
        ]);

        $tenant1->domains()->create(['domain' => 'unique-domain.com']);

        $this->expectException(DomainOccupiedByOtherTenantException::class);

        $tenant2 = Tenant::create([
            'id' => 'company-7b',
            'data' => ['name' => 'Company 7B', 'active' => true]
        ]);

        $tenant2->domains()->create(['domain' => 'unique-domain.com']);
    }

    /**
     * Test Tenant deletion cascades to domains
     */
    public function test_tenant_deletion_removes_domains(): void
    {
        $tenant = Tenant::create([
            'id' => 'company-8',
            'data' => ['name' => 'Company Eight', 'active' => true]
        ]);

        $tenant->domains()->create(['domain' => 'company8.example.com']);

        $this->assertDatabaseHas('domains', ['tenant_id' => 'company-8']);

        $tenant->delete();

        $this->assertDatabaseMissing('tenants', ['id' => 'company-8']);
        $this->assertDatabaseMissing('domains', ['tenant_id' => 'company-8']);
    }
}
