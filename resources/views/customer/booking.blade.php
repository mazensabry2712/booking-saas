<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Book Appointment') }} - {{ tenant()->name }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-8">
        <!-- Header with Language Switcher -->
        <div class="mb-6 sm:mb-8">
            <!-- Language Switcher -->
            <div class="flex justify-end mb-4">
                <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 shadow-sm">
                    <button onclick="changeLanguage('en')"
                        class="px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-medium rounded-md transition-all duration-200 {{ app()->getLocale() === 'en' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        EN
                    </button>
                    <button onclick="changeLanguage('ar')"
                        class="px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-medium rounded-md transition-all duration-200 {{ app()->getLocale() === 'ar' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        عربي
                    </button>
                </div>
            </div>

            <div class="text-center">
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-800 mb-1 sm:mb-2">{{ tenant()->name }}</h1>
                <p class="text-sm sm:text-base text-gray-600">{{ __('Book your appointment online') }}</p>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="max-w-2xl mx-auto bg-white rounded-xl sm:rounded-2xl shadow-lg sm:shadow-xl p-4 sm:p-6 md:p-8">
            <form id="bookingForm" class="space-y-4 sm:space-y-6">
                @csrf

                <!-- Customer Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                        {{ __('Full Name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" required
                        class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="{{ __('Enter your full name') }}">
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                        {{ __('Email') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" required
                        class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="{{ __('Enter your email') }}">
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                        {{ __('Phone Number') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" id="phone" name="phone" required
                        class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="{{ __('Enter your phone number') }}">
                </div>

                <!-- Step 1: Service Type -->
                <div>
                    <label for="service_id" class="block text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                        {{ __('Service Type') }} <span class="text-red-500">*</span>
                    </label>
                    <select id="service_id" name="service_id" required
                        class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">{{ __('Select Service Type') }}</option>
                        <!-- Will be populated dynamically -->
                    </select>
                </div>

                <!-- Step 2: Staff (appears after selecting service) -->
                <div id="staffSection" class="hidden">
                    <label for="staff_id" class="block text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                        {{ __('Select Staff') }} <span class="text-red-500">*</span>
                    </label>
                    <select id="staff_id" name="staff_id" required
                        class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">{{ __('Select Staff Member') }}</option>
                        <!-- Will be populated dynamically based on service -->
                    </select>
                </div>

                <!-- Step 3: Date (appears after selecting staff) -->
                <div id="dateSection" class="hidden">
                    <label for="appointment_date" class="block text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                        {{ __('Appointment Date') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="appointment_date" name="appointment_date" required
                        class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        min="{{ date('Y-m-d') }}">
                    <p id="availableDays" class="mt-1 text-sm text-gray-500"></p>
                </div>

                <!-- Step 4: Time (appears after selecting date) -->
                <div id="timeSection" class="hidden">
                    <label for="appointment_time" class="block text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                        {{ __('Appointment Time') }} <span class="text-red-500">*</span>
                    </label>
                    <select id="appointment_time" name="appointment_time" required
                        class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">{{ __('Select time') }}</option>
                        <!-- Will be populated dynamically based on staff schedule -->
                    </select>
                </div>

                <!-- Notes -->
                <div id="notesSection" class="hidden">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">
                        {{ __('Additional Notes') }}
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                        class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="{{ __('Any special requests or notes...') }}"></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitBtn"
                    class="hidden w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 sm:py-4 px-4 sm:px-6 text-sm sm:text-base rounded-lg transition duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    {{ __('Book Appointment') }}
                </button>
            </form>

            <!-- Success Message -->
            <div id="successMessage" class="hidden mt-4 sm:mt-6 p-3 sm:p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-500 flex-shrink-0 {{ app()->getLocale() === 'ar' ? 'ml-2 sm:ml-3' : 'mr-2 sm:mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <div>
                        <p class="font-semibold text-green-800 text-sm sm:text-base">{{ __('Appointment Booked Successfully!') }}</p>
                        <p class="text-xs sm:text-sm text-green-700">{{ __('You will receive a confirmation email shortly.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div id="errorMessage" class="hidden mt-4 sm:mt-6 p-3 sm:p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-500 flex-shrink-0 {{ app()->getLocale() === 'ar' ? 'ml-2 sm:ml-3' : 'mr-2 sm:mr-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <div>
                        <p class="font-semibold text-red-800 text-sm sm:text-base">{{ __('Booking Failed') }}</p>
                        <p class="text-xs sm:text-sm text-red-700" id="errorText"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Queue Status Link -->
        <div class="text-center mt-6 sm:mt-8">
            <a href="{{ route('queue.status') }}" class="text-sm sm:text-base text-blue-600 hover:text-blue-700 font-medium">
                {{ __('Check Queue Status') }} →
            </a>
        </div>
    </div>

    <script>
        const currentLang = '{{ app()->getLocale() }}';
        let staffSchedules = [];

        // Load services on page load
        async function loadServices() {
            try {
                const response = await fetch('/api/booking/services');
                const data = await response.json();

                if (data.success && data.data.length > 0) {
                    const serviceSelect = document.getElementById('service_id');
                    serviceSelect.innerHTML = '<option value="">{{ __('Select Service Type') }}</option>';

                    data.data.forEach(service => {
                        const option = document.createElement('option');
                        option.value = service.id;
                        option.textContent = currentLang === 'ar' && service.name_ar ? service.name_ar : service.name;
                        if (service.duration) {
                            option.textContent += ` (${service.duration} {{ __('min') }})`;
                        }
                        serviceSelect.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading services:', error);
            }
        }

        // When service is selected, load staff
        document.getElementById('service_id').addEventListener('change', async function() {
            const serviceId = this.value;

            // Hide subsequent sections
            document.getElementById('staffSection').classList.add('hidden');
            document.getElementById('dateSection').classList.add('hidden');
            document.getElementById('timeSection').classList.add('hidden');
            document.getElementById('notesSection').classList.add('hidden');
            document.getElementById('submitBtn').classList.add('hidden');

            if (!serviceId) return;

            try {
                const response = await fetch(`/api/booking/staff/by-service/${serviceId}`);
                const data = await response.json();

                if (data.success && data.data.length > 0) {
                    const staffSelect = document.getElementById('staff_id');
                    staffSelect.innerHTML = '<option value="">{{ __('Select Staff Member') }}</option>';

                    data.data.forEach(staff => {
                        const option = document.createElement('option');
                        option.value = staff.id;
                        option.textContent = staff.name;
                        staffSelect.appendChild(option);
                    });

                    document.getElementById('staffSection').classList.remove('hidden');
                } else {
                    alert('{{ __('No staff available for this service') }}');
                }
            } catch (error) {
                console.error('Error loading staff:', error);
            }
        });

        // When staff is selected, load their schedule
        document.getElementById('staff_id').addEventListener('change', async function() {
            const staffId = this.value;

            // Hide subsequent sections
            document.getElementById('dateSection').classList.add('hidden');
            document.getElementById('timeSection').classList.add('hidden');
            document.getElementById('notesSection').classList.add('hidden');
            document.getElementById('submitBtn').classList.add('hidden');

            if (!staffId) return;

            try {
                const response = await fetch(`/api/booking/staff/${staffId}/schedule`);
                const data = await response.json();

                if (data.success && data.data.length > 0) {
                    staffSchedules = data.data;

                    // Show available days hint
                    const dayNames = {
                        0: currentLang === 'ar' ? 'الأحد' : 'Sunday',
                        1: currentLang === 'ar' ? 'الإثنين' : 'Monday',
                        2: currentLang === 'ar' ? 'الثلاثاء' : 'Tuesday',
                        3: currentLang === 'ar' ? 'الأربعاء' : 'Wednesday',
                        4: currentLang === 'ar' ? 'الخميس' : 'Thursday',
                        5: currentLang === 'ar' ? 'الجمعة' : 'Friday',
                        6: currentLang === 'ar' ? 'السبت' : 'Saturday'
                    };

                    const availableDaysText = staffSchedules.map(s => dayNames[s.day_of_week]).join(', ');
                    document.getElementById('availableDays').textContent =
                        '{{ __('Available days') }}: ' + availableDaysText;

                    document.getElementById('dateSection').classList.remove('hidden');
                    document.getElementById('appointment_date').value = '';
                } else {
                    alert('{{ __('This staff member has no available schedule') }}');
                }
            } catch (error) {
                console.error('Error loading schedule:', error);
            }
        });

        // When date is selected, show available times
        document.getElementById('appointment_date').addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const dayOfWeek = selectedDate.getDay();

            // Find schedule for this day
            const schedule = staffSchedules.find(s => s.day_of_week === dayOfWeek);

            if (!schedule) {
                this.setCustomValidity('{{ __('This staff member is not available on this day') }}');
                this.reportValidity();
                document.getElementById('timeSection').classList.add('hidden');
                document.getElementById('notesSection').classList.add('hidden');
                document.getElementById('submitBtn').classList.add('hidden');
                return;
            }

            this.setCustomValidity('');

            // Populate time dropdown
            const timeSelect = document.getElementById('appointment_time');
            timeSelect.innerHTML = '<option value="">{{ __('Select time') }}</option>';

            // Generate time slots based on schedule
            const startTime = schedule.start_time;
            const endTime = schedule.end_time;

            let current = new Date(`2000-01-01 ${startTime}`);
            const end = new Date(`2000-01-01 ${endTime}`);

            while (current < end) {
                const timeValue = current.toTimeString().substring(0, 5);
                const timeDisplay = current.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });

                const option = document.createElement('option');
                option.value = timeValue;
                option.textContent = timeDisplay;
                timeSelect.appendChild(option);

                // Add 30 minutes
                current.setMinutes(current.getMinutes() + 30);
            }

            document.getElementById('timeSection').classList.remove('hidden');
        });

        // When time is selected, show notes and submit button
        document.getElementById('appointment_time').addEventListener('change', function() {
            if (this.value) {
                document.getElementById('notesSection').classList.remove('hidden');
                document.getElementById('submitBtn').classList.remove('hidden');
            } else {
                document.getElementById('notesSection').classList.add('hidden');
                document.getElementById('submitBtn').classList.add('hidden');
            }
        });

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
                        staff_id: formData.get('staff_id'),
                        service_id: formData.get('service_id'),
                        notes: formData.get('notes')
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    successMsg.classList.remove('hidden');
                    e.target.reset();

                    // Hide all sections
                    document.getElementById('staffSection').classList.add('hidden');
                    document.getElementById('dateSection').classList.add('hidden');
                    document.getElementById('timeSection').classList.add('hidden');
                    document.getElementById('notesSection').classList.add('hidden');
                    document.getElementById('submitBtn').classList.add('hidden');

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

        // Load services on page load
        loadServices();

        // Change Language Function
        function changeLanguage(lang) {
            window.location.href = '/change-language/' + lang;
        }
    </script>
</body>
</html>
