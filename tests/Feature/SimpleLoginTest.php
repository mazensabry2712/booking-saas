<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SimpleLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: صفحة اللوجن موجودة وتعمل
     */
    public function test_login_page_loads_successfully()
    {
        // Create tenant first
        $this->runCentralMigrations();
        $tenant = $this->createTenant('test-login');
        $this->initializeTenancy($tenant);

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');

        $this->endTenancy();
    }

    /**
     * Test: تسجيل دخول ناجح مع بيانات صحيحة
     */
    public function test_user_can_login_with_correct_credentials()
    {
        // Setup
        $this->runCentralMigrations();
        $tenant = $this->createTenant('test-login');
        $this->initializeTenancy($tenant);

        // Create user manually in database
        \Illuminate\Support\Facades\Schema::create('users', function ($table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

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

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => Hash::make('password123'),
        ]);

        // Test login
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'access_token',
            'token_type',
            'user',
            'tenant',
        ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals('Test User', $data['user']['name']);
        $this->assertEquals('test@test.com', $data['user']['email']);
        $this->assertNotEmpty($data['access_token']);
        $this->assertEquals('Bearer', $data['token_type']);

        $this->endTenancy();
    }

    /**
     * Test: فشل تسجيل الدخول مع كلمة مرور خاطئة
     */
    public function test_login_fails_with_wrong_password()
    {
        $this->runCentralMigrations();
        $tenant = $this->createTenant('test-login');
        $this->initializeTenancy($tenant);

        \Illuminate\Support\Facades\Schema::create('users', function ($table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => Hash::make('correctpassword'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);

        $this->endTenancy();
    }

    /**
     * Test: فشل تسجيل الدخول مع إيميل غير موجود
     */
    public function test_login_fails_with_non_existent_email()
    {
        $this->runCentralMigrations();
        $tenant = $this->createTenant('test-login');
        $this->initializeTenancy($tenant);

        \Illuminate\Support\Facades\Schema::create('users', function ($table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);

        $this->endTenancy();
    }

    /**
     * Test: Login requires email field
     */
    public function test_login_requires_email()
    {
        $this->runCentralMigrations();
        $tenant = $this->createTenant('test-login');
        $this->initializeTenancy($tenant);

        $response = $this->postJson('/api/auth/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);

        $this->endTenancy();
    }

    /**
     * Test: Login requires password field
     */
    public function test_login_requires_password()
    {
        $this->runCentralMigrations();
        $tenant = $this->createTenant('test-login');
        $this->initializeTenancy($tenant);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@test.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);

        $this->endTenancy();
    }

    /**
     * Test: Login requires valid email format
     */
    public function test_login_requires_valid_email_format()
    {
        $this->runCentralMigrations();
        $tenant = $this->createTenant('test-login');
        $this->initializeTenancy($tenant);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);

        $this->endTenancy();
    }

    /**
     * Test: Token can be used for authenticated requests
     */
    public function test_login_token_works_for_authenticated_requests()
    {
        $this->runCentralMigrations();
        $tenant = $this->createTenant('test-login');
        $this->initializeTenancy($tenant);

        \Illuminate\Support\Facades\Schema::create('users', function ($table) {
            $table->id();
            $table->string('tenant_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

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

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => Hash::make('password123'),
        ]);

        // Login
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@test.com',
            'password' => 'password123',
        ]);

        $token = $loginResponse->json('access_token');
        $this->assertNotEmpty($token);

        // Use token to access protected route
        $profileResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/auth/profile');

        $profileResponse->assertStatus(200);
        $profileResponse->assertJson([
            'success' => true,
            'data' => [
                'email' => 'test@test.com',
            ],
        ]);

        $this->endTenancy();
    }
}
