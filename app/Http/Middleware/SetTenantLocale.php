<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetTenantLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user has selected a language in session
        if (session()->has('locale')) {
            $locale = session('locale');
            App::setLocale($locale);
        } else {
            // Fall back to tenant settings or default
            try {
                $tenant = tenant();

                if ($tenant && isset($tenant->settings) && isset($tenant->settings->language)) {
                    App::setLocale($tenant->settings->language);
                } else {
                    App::setLocale(config('app.locale', 'en'));
                }
            } catch (\Exception $e) {
                App::setLocale(config('app.locale', 'en'));
            }
        }

        return $next($request);
    }
}
