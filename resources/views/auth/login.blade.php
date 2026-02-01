<!DOCTYPE html>
@php
    $isArabic = app()->getLocale() === 'ar';
@endphp
<html lang="{{ app()->getLocale() }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isArabic ? 'تسجيل الدخول' : 'Login' }} - {{ tenant()->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">

    <!-- Language Switcher -->
    <div class="absolute top-4 {{ $isArabic ? 'left-4' : 'right-4' }}">
        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 shadow-sm">
            <a href="{{ url('/login?lang=en') }}"
                class="px-3 py-1.5 text-sm font-medium rounded-md transition-all duration-200 {{ !$isArabic ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                EN
            </a>
            <a href="{{ url('/login?lang=ar') }}"
                class="px-3 py-1.5 text-sm font-medium rounded-md transition-all duration-200 {{ $isArabic ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                عربي
            </a>
        </div>
    </div>

    <div class="bg-white w-full max-w-lg p-10 shadow-xl rounded-2xl">

        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">{{ $isArabic ? 'تسجيل الدخول' : 'Login' }}</h1>
            <p class="text-blue-600 font-medium">{{ tenant()->name }}</p>
        </div>

        <!-- Form -->
        <form id="loginForm" class="space-y-5">
            @csrf

            <!-- Email -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">{{ $isArabic ? 'البريد الإلكتروني' : 'Email' }}</label>
                <input type="email" id="email" name="email" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                       placeholder="{{ $isArabic ? 'أدخل بريدك الإلكتروني' : 'Enter your email' }}">
            </div>

            <!-- Password -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">{{ $isArabic ? 'كلمة المرور' : 'Password' }}</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                       placeholder="{{ $isArabic ? 'أدخل كلمة المرور' : 'Enter your password' }}">
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer gap-2">
                    <input type="checkbox" id="remember" class="w-4 h-4 rounded text-blue-600">
                    <span class="text-gray-700">{{ $isArabic ? 'تذكرني' : 'Remember me' }}</span>
                </label>
                <a href="#" class="text-blue-600 hover:underline text-sm">{{ $isArabic ? 'نسيت كلمة المرور؟' : 'Forgot password?' }}</a>
            </div>

            <!-- Messages -->
            <div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg text-center">
            </div>

            <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 p-3 rounded-lg text-center">
            </div>

            <!-- Submit Button -->
            <button type="submit" id="submitBtn"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 text-xl rounded-lg transition duration-200 flex items-center justify-center gap-2">
                <span id="btnText">{{ $isArabic ? 'دخول' : 'Login' }}</span>
                <svg class="hidden animate-spin w-6 h-6" id="loadingSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </form>
    </div>

    <script>
    const isArabic = {{ $isArabic ? 'true' : 'false' }};
    const texts = {
        loggingIn: isArabic ? 'جاري الدخول...' : 'Logging in...',
        login: isArabic ? 'دخول' : 'Login',
        loginSuccess: isArabic ? 'تم تسجيل الدخول بنجاح!' : 'Login successful!',
        loginError: isArabic ? 'بيانات الدخول غير صحيحة' : 'Invalid credentials',
        errorOccurred: isArabic ? 'حدث خطأ! حاول مرة أخرى' : 'An error occurred! Please try again'
    };

    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const errorDiv = document.getElementById('errorMessage');
        const successDiv = document.getElementById('successMessage');
        const submitButton = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const loadingSpinner = document.getElementById('loadingSpinner');

        errorDiv.classList.add('hidden');
        successDiv.classList.add('hidden');

        submitButton.disabled = true;
        submitButton.classList.add('opacity-70');
        btnText.textContent = texts.loggingIn;
        loadingSpinner.classList.remove('hidden');

        const formData = {
            email: document.getElementById('email').value,
            password: document.getElementById('password').value,
        };

        try {
            const response = await fetch('{{ url("/api/auth/login") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                if (data.access_token) {
                    localStorage.setItem('auth_token', data.access_token);
                }
                if (data.user) {
                    localStorage.setItem('user', JSON.stringify(data.user));
                }

                successDiv.classList.remove('hidden');
                successDiv.textContent = '✓ ' + texts.loginSuccess;

                // Redirect immediately
                const userRole = data.user?.role;
                setTimeout(() => {
                    if (userRole === 'Admin Tenant' || userRole === 'Staff') {
                        window.location.replace('/admin/dashboard');
                    } else if (userRole === 'Customer') {
                        window.location.replace('/my-queue');
                    } else {
                        window.location.replace('/');
                    }
                }, 500);
            } else {
                errorDiv.classList.remove('hidden');
                errorDiv.textContent = '✕ ' + (data.message || data.error || texts.loginError);

                submitButton.disabled = false;
                submitButton.classList.remove('opacity-70');
                btnText.textContent = texts.login;
                loadingSpinner.classList.add('hidden');
            }
        } catch (error) {
            errorDiv.classList.remove('hidden');
            errorDiv.textContent = '✕ ' + texts.errorOccurred;

            submitButton.disabled = false;
            submitButton.classList.remove('opacity-70');
            btnText.textContent = texts.login;
            loadingSpinner.classList.add('hidden');
        }
    });
    </script>
</body>
</html>
