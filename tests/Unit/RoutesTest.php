<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;

class RoutesTest extends TestCase
{
    /**
     * Test routes are loaded
     */
    public function test_routes_are_loaded(): void
    {
        $routes = Route::getRoutes();
        $this->assertGreaterThan(0, count($routes));
    }

    /**
     * Test web routes exist
     */
    public function test_web_routes_exist(): void
    {
        $routes = collect(Route::getRoutes())->map(function ($route) {
            return $route->uri();
        });

        $this->assertTrue(
            $routes->contains('/') || $routes->contains(''),
            'Home route should exist'
        );

        $this->assertTrue(
            $routes->contains('login'),
            'Login route should exist'
        );
    }

    /**
     * Test API routes file exists and has content
     */
    public function test_api_routes_file_exists(): void
    {
        // The API routes file should exist
        $this->assertFileExists(base_path('routes/api.php'));

        // Check the file has content
        $content = file_get_contents(base_path('routes/api.php'));
        $this->assertStringContainsString('Route::', $content);
        $this->assertStringContainsString('super-admin', $content);
    }

    /**
     * Helper to check if a specific route exists
     */
    private function routeExists(string $method, string $uri): bool
    {
        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            if (in_array($method, $route->methods()) && $route->uri() === $uri) {
                return true;
            }
        }

        return false;
    }
}
