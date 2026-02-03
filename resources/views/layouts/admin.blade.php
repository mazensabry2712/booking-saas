<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Admin')) - {{ tenant()->name ?? config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-sm border-b sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-14">
                <!-- Logo & Navigation Links -->
                <div class="flex items-center gap-1">
                    <a href="/admin/dashboard" class="text-xl font-bold text-blue-600 {{ app()->getLocale() === 'ar' ? 'ml-6' : 'mr-6' }}">
                        {{ tenant()->name ?? config('app.name') }}
                    </a>

                    <div class="hidden md:flex items-center">
                        <a href="/admin/appointments" class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->is('admin/appointments*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                            {{ __('Appointments') }}
                        </a>
                        <a href="/admin/queue" class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->is('admin/queue*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                            {{ __('Queue') }}
                        </a>
                        <a href="/admin/staff" class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->is('admin/staff*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                            {{ __('Staff') }}
                        </a>
                        <a href="/admin/reports" class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->is('admin/reports*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                            {{ __('Reports') }}
                        </a>
                        <a href="/admin/settings" class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->is('admin/settings*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                            {{ __('Settings') }}
                        </a>
                        @if(auth()->user()->isAdminTenant())
                        <a href="/admin/assistants" class="px-3 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->is('admin/assistants*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                            {{ __('Assistants') }}
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Right Side: Language + Profile + Logout -->
                <div class="flex items-center gap-3">
                    <!-- Language Switcher -->
                    <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-0.5">
                        <button onclick="changeLanguage('en')"
                            class="px-2.5 py-1 text-xs font-medium rounded-md transition-all {{ app()->getLocale() === 'en' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                            EN
                        </button>
                        <button onclick="changeLanguage('ar')"
                            class="px-2.5 py-1 text-xs font-medium rounded-md transition-all {{ app()->getLocale() === 'ar' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                            عربي
                        </button>
                    </div>

                    <!-- Profile -->
                    <a href="/admin/profile" class="flex items-center gap-2 px-2 py-1 rounded-lg hover:bg-gray-50 transition-colors">
                        @if(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="w-8 h-8 rounded-full object-cover border-2 border-gray-200">
                        @else
                            <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center">
                                <span class="text-white font-bold text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                            </div>
                        @endif
                        <span class="hidden sm:inline text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                    </a>

                    <!-- Logout -->
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                            {{ __('Logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation -->
        <div class="md:hidden border-t overflow-x-auto">
            <div class="flex px-4 py-2 gap-1">
                <a href="/admin/appointments" class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap {{ request()->is('admin/appointments*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600' }}">
                    {{ __('Appointments') }}
                </a>
                <a href="/admin/queue" class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap {{ request()->is('admin/queue*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600' }}">
                    {{ __('Queue') }}
                </a>
                <a href="/admin/staff" class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap {{ request()->is('admin/staff*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600' }}">
                    {{ __('Staff') }}
                </a>
                <a href="/admin/reports" class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap {{ request()->is('admin/reports*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600' }}">
                    {{ __('Reports') }}
                </a>
                <a href="/admin/settings" class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap {{ request()->is('admin/settings*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600' }}">
                    {{ __('Settings') }}
                </a>
                @if(auth()->user()->isAdminTenant())
                <a href="/admin/assistants" class="px-3 py-1.5 text-xs font-medium rounded-lg whitespace-nowrap {{ request()->is('admin/assistants*') ? 'text-blue-600 bg-blue-50' : 'text-gray-600' }}">
                    {{ __('Assistants') }}
                </a>
                @endif
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">@yield('title')</h1>
                @hasSection('subtitle')
                <p class="text-gray-600 mt-1">@yield('subtitle')</p>
                @endif
            </div>
            <div class="flex items-center gap-3">
                @yield('header-actions')
            </div>
        </div>

        <!-- Alerts -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <!-- Page Content -->
        @yield('content')
    </main>

    <script>
        function changeLanguage(lang) {
            window.location.href = '/change-language/' + lang;
        }
    </script>
    @stack('scripts')
</body>
</html>
