<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Run central migrations for tenants table
        $this->runCentralMigrations();

        // Create tenant
        $this->tenant = $this->createTenant('test-tenant');
        $this->initializeTenancy($this->tenant);

        // Create roles table manually for testing
        \Illuminate\Support\Facades\Schema::create('roles', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('role_user', function ($table) {
            $table->foreignId('role_id');
            $table->foreignId('user_id');
        });

        // Create users table
        \Illuminate\Support\Facades\Schema::create('users', function ($table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Create personal_access_tokens table for Sanctum
        \Illuminate\Support\Facades\Schema::create('personal_access_tokens', function ($table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        $this->endTenancy();
        parent::tearDown();
    }

    /** @test */
    public function admin_can_login_successfully()
    {
        // Create Admin role
        $adminRole = Role::create(['name' => 'Admin Tenant']);

        // Create admin user
        $admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
        ]);
        $admin->assignRole('Admin Tenant');

        // Attempt login
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'access_token',
                     'token_type',
                     'user' => [
                         'id',
                         'name',
                         'email',
                         'role',
                         'tenant_id',
                     ],
                     'tenant' => [
                         'id',
                         'name',
                     ],
                 ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('Admin Tenant', $response->json('user.role'));
        $this->assertNotEmpty($response->json('access_token'));
        $this->assertEquals('Bearer', $response->json('token_type'));
    }

    /** @test */
    public function staff_can_login_successfully()
    {
        // Create Staff role
        $staffRole = Role::create(['name' => 'Staff']);

        // Create staff user
        $staff = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Staff User',
            'email' => 'staff@test.com',
            'password' => Hash::make('password123'),
        ]);
        $staff->assignRole('Staff');

        // Attempt login
        $response = $this->postJson('/api/auth/login', [
            'email' => 'staff@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ]);

        $this->assertEquals('Staff', $response->json('user.role'));
        $this->assertNotEmpty($response->json('access_token'));
    }

    /** @test */
    public function customer_can_login_successfully()
    {
        // Create Customer role
        $customerRole = Role::create(['name' => 'Customer']);

        // Create customer user
        $customer = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Customer User',
            'email' => 'customer@test.com',
            'password' => Hash::make('password123'),
        ]);
        $customer->assignRole('Customer');

        // Attempt login
        $response = $this->postJson('/api/auth/login', [
            'email' => 'customer@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ]);

        $this->assertEquals('Customer', $response->json('user.role'));
        $this->assertNotEmpty($response->json('access_token'));
    }

    /** @test */
    public function login_fails_with_incorrect_password()
    {
        // Create user
        $adminRole = Role::create(['name' => 'Admin Tenant']);
        $admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
        ]);
        $admin->assignRole('Admin Tenant');

        // Attempt login with wrong password
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function login_fails_with_non_existent_email()
    {
        // Attempt login with non-existent email
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function login_requires_email_field()
    {
        $response = $this->postJson('/api/auth/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function login_requires_password_field()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function login_requires_valid_email_format()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid-email-format',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function user_cannot_login_to_different_tenant()
    {
        // Create first tenant and user
        $adminRole = Role::create(['name' => 'Admin Tenant']);
        $admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
        ]);
        $admin->assignRole('Admin Tenant');

        // Create second tenant
        $this->endTenancy();
        $secondTenant = $this->createTenant('second-tenant');
        $this->initializeTenancy($secondTenant);

        // Try to login as first tenant's user in second tenant context
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        // Should fail because user belongs to different tenant
        $response->assertStatus(422);
    }

    /** @test */
    public function login_page_is_accessible()
    {
        $response = $this->get('/login');

        $response->assertStatus(200)
                 ->assertViewIs('auth.login');
    }

    /** @test */
    public function successful_login_returns_user_data()
    {
        $adminRole = Role::create(['name' => 'Admin Tenant']);
        $admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin Test User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
        ]);
        $admin->assignRole('Admin Tenant');

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);

        $data = $response->json();

        $this->assertEquals('Admin Test User', $data['user']['name']);
        $this->assertEquals('admin@test.com', $data['user']['email']);
        $this->assertEquals($this->tenant->id, $data['user']['tenant_id']);
        $this->assertEquals($this->tenant->id, $data['tenant']['id']);
    }

    /** @test */
    public function login_token_can_be_used_for_authenticated_requests()
    {
        $adminRole = Role::create(['name' => 'Admin Tenant']);
        $admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
        ]);
        $admin->assignRole('Admin Tenant');

        // Login
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('access_token');

        // Use token to access protected route
        $profileResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/auth/profile');

        $profileResponse->assertStatus(200)
                       ->assertJson([
                           'success' => true,
                           'data' => [
                               'email' => 'admin@test.com',
                           ],
                       ]);
    }

    /** @test */
    public function multiple_users_can_have_different_roles()
    {
        // Create roles
        Role::create(['name' => 'Admin Tenant']);
        Role::create(['name' => 'Staff']);
        Role::create(['name' => 'Customer']);

        // Create users with different roles
        $admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('Admin Tenant');

        $staff = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Staff',
            'email' => 'staff@test.com',
            'password' => Hash::make('password'),
        ]);
        $staff->assignRole('Staff');

        $customer = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Customer',
            'email' => 'customer@test.com',
            'password' => Hash::make('password'),
        ]);
        $customer->assignRole('Customer');

        // Test each login
        $adminResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);
        $this->assertEquals('Admin Tenant', $adminResponse->json('user.role'));

        $staffResponse = $this->postJson('/api/auth/login', [
            'email' => 'staff@test.com',
            'password' => 'password',
        ]);
        $this->assertEquals('Staff', $staffResponse->json('user.role'));

        $customerResponse = $this->postJson('/api/auth/login', [
            'email' => 'customer@test.com',
            'password' => 'password',
        ]);
        $this->assertEquals('Customer', $customerResponse->json('user.role'));
    }
}
