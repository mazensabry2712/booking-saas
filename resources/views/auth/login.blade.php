<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - {{ tenant()->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white w-full max-w-lg p-10 shadow-xl rounded-2xl">

        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">تسجيل الدخول</h1>
            <p class="text-gray-500">{{ tenant()->name }}</p>
        </div>

        <!-- Form -->
        <form id="loginForm" class="space-y-5">
            @csrf

            <!-- Email -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">البريد الإلكتروني</label>
                <input type="email" id="email" name="email" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                       placeholder="admin@demo.localhost">
            </div>

            <!-- Password -->
            <div>
                <label class="block text-gray-700 font-medium mb-2">كلمة المرور</label>
                <input type="password" id="password" name="password" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                       placeholder="password123">
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="remember" class="w-4 h-4 ml-2">
                    <span class="text-gray-700">تذكرني</span>
                </label>
                <a href="#" class="text-blue-600 hover:underline">نسيت كلمة المرور؟</a>
            </div>

            <!-- Messages -->
            <div id="errorMessage" class="hidden bg-red-100 border-2 border-red-400 text-red-700 p-3 text-center">
            </div>

            <div id="successMessage" class="hidden bg-green-100 border-2 border-green-400 text-green-700 p-3 text-center">
            </div>

            <!-- Submit Button -->
            <button type="submit" id="submitBtn"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 text-xl rounded-lg transition duration-200">
                <span id="btnText">دخول</span>
                <svg class="hidden animate-spin inline-block w-6 h-6 mr-2" id="loadingSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>
        </form>

        <!-- Demo Info -->
        <div class="mt-8 pt-6 border-t-2 border-gray-200">
            <p class="text-center text-gray-600 font-bold mb-3">حسابات تجريبية</p>
            <div class="space-y-2 text-sm">
                <div class="bg-blue-50 p-3 border border-blue-200 rounded-lg">
                    <span class="font-bold">Admin:</span> admin@demo.localhost
                </div>
                <div class="bg-indigo-50 p-3 border border-indigo-200 rounded-lg">
                    <span class="font-bold">Staff:</span> staff@demo.localhost
                </div>
                <div class="bg-gray-50 p-3 border border-gray-200 rounded-lg text-center">
                    <span class="font-bold">Password:</span> password123
                </div>
            </div>
        </div>
    </div>

    <script>
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
        btnText.textContent = 'جاري الدخول...';
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
                successDiv.textContent = '✓ تم تسجيل الدخول بنجاح!';

                setTimeout(() => {
                    const userRole = data.user?.role;
                    if (userRole === 'Admin Tenant' || userRole === 'Staff') {
                        window.location.href = '/admin/dashboard';
                    } else if (userRole === 'Customer') {
                        window.location.href = '/my-queue';
                    } else {
                        window.location.href = '/';
                    }
                }, 1000);
            } else {
                errorDiv.classList.remove('hidden');
                errorDiv.textContent = '✕ ' + (data.message || data.error || 'خطأ في البيانات');

                submitButton.disabled = false;
                submitButton.classList.remove('opacity-70');
                btnText.textContent = 'دخول';
                loadingSpinner.classList.add('hidden');
            }
        } catch (error) {
            errorDiv.classList.remove('hidden');
            errorDiv.textContent = '✕ حدث خطأ! حاول مرة أخرى';

            submitButton.disabled = false;
            submitButton.classList.remove('opacity-70');
            btnText.textContent = 'دخول';
            loadingSpinner.classList.add('hidden');
        }
    });
    </script>
</body>
</html>
