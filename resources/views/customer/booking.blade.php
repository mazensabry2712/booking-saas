<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Book Appointment') }} - {{ tenant()->name }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">{{ tenant()->name }}</h1>
            <p class="text-gray-600">{{ __('Book your appointment online') }}</p>
        </div>

        <!-- Booking Form -->
        <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-xl p-8">
            <form id="bookingForm" class="space-y-6">
                @csrf

                <!-- Customer Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Full Name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="{{ __('Enter your full name') }}">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Email') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="{{ __('Enter your email') }}">
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Phone Number') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" id="phone" name="phone" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="{{ __('Enter your phone number') }}">
                </div>

                <!-- Date -->
                <div>
                    <label for="appointment_date" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Appointment Date') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="appointment_date" name="appointment_date" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        min="{{ date('Y-m-d') }}">
                </div>

                <!-- Time -->
                <div>
                    <label for="appointment_time" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Appointment Time') }} <span class="text-red-500">*</span>
                    </label>
                    <select id="appointment_time" name="appointment_time" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">{{ __('Select time') }}</option>
                        <option value="09:00">09:00 AM</option>
                        <option value="09:30">09:30 AM</option>
                        <option value="10:00">10:00 AM</option>
                        <option value="10:30">10:30 AM</option>
                        <option value="11:00">11:00 AM</option>
                        <option value="11:30">11:30 AM</option>
                        <option value="12:00">12:00 PM</option>
                        <option value="14:00">02:00 PM</option>
                        <option value="14:30">02:30 PM</option>
                        <option value="15:00">03:00 PM</option>
                        <option value="15:30">03:30 PM</option>
                        <option value="16:00">04:00 PM</option>
                        <option value="16:30">04:30 PM</option>
                        <option value="17:00">05:00 PM</option>
                    </select>
                </div>

                <!-- Staff Selection -->
                <div>
                    <label for="staff_id" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Preferred Staff') }}
                    </label>
                    <select id="staff_id" name="staff_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">{{ __('Any available staff') }}</option>
                        <!-- Will be populated dynamically -->
                    </select>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('Additional Notes') }}
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="{{ __('Any special requests or notes...') }}"></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitBtn"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-6 rounded-lg transition duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    {{ __('Book Appointment') }}
                </button>
            </form>

            <!-- Success Message -->
            <div id="successMessage" class="hidden mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-500 {{ app()->getLocale() === 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <div>
                        <p class="font-semibold text-green-800">{{ __('Appointment Booked Successfully!') }}</p>
                        <p class="text-sm text-green-700">{{ __('You will receive a confirmation email shortly.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="hidden mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-red-500 {{ app()->getLocale() === 'ar' ? 'ml-3' : 'mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <div>
                        <p class="font-semibold text-red-800">{{ __('Booking Failed') }}</p>
                        <p class="text-sm text-red-700" id="errorText"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Queue Status Link -->
        <div class="text-center mt-8">
            <a href="{{ route('queue.status') }}" class="text-blue-600 hover:text-blue-700 font-medium">
                {{ __('Check Queue Status') }} →
            </a>
        </div>
    </div>

    <script>
        // Staff data from server
        const staffData = @json(\App\Models\Role::where('name', 'Staff')->first()?->users()->select('id', 'name')->get() ?? []);

        // Load available staff
        function loadStaff() {
            try {
                const staffSelect = document.getElementById('staff_id');
                staffData.forEach(staff => {
                    const option = document.createElement('option');
                    option.value = staff.id;
                    option.textContent = staff.name;
                    staffSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading staff:', error);
            }
        }

        // Handle form submission
        document.getElementById('bookingForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const successMsg = document.getElementById('successMessage');
            const errorMsg = document.getElementById('errorMessage');

            // Reset messages
            successMsg.classList.add('hidden');
            errorMsg.classList.add('hidden');

            // Disable button
            submitBtn.disabled = true;
            submitBtn.textContent = '{{ __('Booking...') }}';

            try {
                const formData = new FormData(e.target);
                const appointmentDateTime = `${formData.get('appointment_date')} ${formData.get('appointment_time')}`;

                const response = await fetch('/api/appointments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        customer_name: formData.get('name'),
                        customer_email: formData.get('email'),
                        customer_phone: formData.get('phone'),
                        appointment_date: appointmentDateTime,
                        staff_id: formData.get('staff_id') || null,
                        notes: formData.get('notes')
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    successMsg.classList.remove('hidden');
                    e.target.reset();

                    // Scroll to success message
                    successMsg.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    errorMsg.classList.remove('hidden');
                    document.getElementById('errorText').textContent = data.message || '{{ __('An error occurred. Please try again.') }}';
                }
            } catch (error) {
                errorMsg.classList.remove('hidden');
                document.getElementById('errorText').textContent = '{{ __('An error occurred. Please try again.') }}';
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = '{{ __('Book Appointment') }}';
            }
        });

        // Load staff on page load
        loadStaff();
    </script>
</body>
</html>
