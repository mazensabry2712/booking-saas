<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Manage Appointments') }} - {{ tenant()->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center {{ app()->getLocale() === 'ar' ? 'space-x-6 space-x-reverse' : 'space-x-6' }}">
                    <a href="/admin/dashboard" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6 {{ app()->getLocale() === 'ar' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-xl font-bold text-gray-900">{{ tenant()->name }}</h1>
                </div>
                <div class="flex items-center {{ app()->getLocale() === 'ar' ? 'space-x-4 space-x-reverse' : 'space-x-4' }}">
                    <!-- Language Switcher -->
                    <div class="flex items-center {{ app()->getLocale() === 'ar' ? 'space-x-2 space-x-reverse' : 'space-x-2' }}">
                        <a href="{{ url('/change-language/ar') }}"
                           class="px-2 py-1 text-sm rounded {{ app()->getLocale() === 'ar' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            عربي
                        </a>
                        <a href="{{ url('/change-language/en') }}"
                           class="px-2 py-1 text-sm rounded {{ app()->getLocale() === 'en' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            EN
                        </a>
                    </div>
                    <span class="text-gray-700">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800">{{ __('Logout') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('Manage Appointments') }}</h2>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="mb-6 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('Appointments List') }}</h3>
                <button onclick="openModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    {{ __('Add New Appointment') }}
                </button>
            </div>

            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-lg p-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
                    {{ session('error') }}
                </div>
            @endif

            @if($appointments->count() > 0)
                <!-- Appointments Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">{{ __('Customer Name') }}</th>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">{{ __('Phone Number') }}</th>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">{{ __('Time') }}</th>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">{{ __('Service Type') }}</th>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($appointments as $appointment)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $appointment->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $appointment->customer?->name ?? __('Not specified') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $appointment->customer?->phone ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $appointment->date->format('Y-m-d') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $appointment->time_slot }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $appointment->service_type ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($appointment->status === 'confirmed') bg-green-100 text-green-800
                                            @elseif($appointment->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($appointment->status === 'cancelled') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            @if($appointment->status === 'confirmed') {{ __('Confirmed') }}
                                            @elseif($appointment->status === 'pending') {{ __('Pending') }}
                                            @elseif($appointment->status === 'cancelled') {{ __('Cancelled') }}
                                            @else {{ $appointment->status }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="viewAppointment({{ $appointment->id }})" class="text-blue-600 hover:text-blue-900 {{ app()->getLocale() === 'ar' ? 'ml-3' : 'mr-3' }}">{{ __('View') }}</button>
                                        <button onclick="editAppointment({{ $appointment->id }})" class="text-green-600 hover:text-green-900 {{ app()->getLocale() === 'ar' ? 'ml-3' : 'mr-3' }}">{{ __('Edit') }}</button>
                                        <button onclick="deleteAppointment({{ $appointment->id }})" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No appointments') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Start by adding your first appointment') }}</p>
                </div>
            @endif
        </div>
    </main>

    <!-- View Appointment Modal -->
    <div id="viewModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-xl font-semibold text-gray-900">{{ __('Appointment Details') }}</h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>

            <div id="appointmentDetails" class="mt-4 space-y-4">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Edit Appointment Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">{{ __('Edit Appointment') }}</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="editAppointmentForm" class="space-y-4">
                <input type="hidden" id="edit_appointment_id" name="appointment_id">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Customer Name') }}</label>
                    <input type="text" id="edit_customer_name" name="customer_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Phone Number') }}</label>
                    <input type="tel" id="edit_customer_phone" name="customer_phone" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Email') }}</label>
                    <input type="email" id="edit_customer_email" name="customer_email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Appointment Date') }}</label>
                    <input type="date" id="edit_appointment_date" name="appointment_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Appointment Time') }}</label>
                    <input type="time" id="edit_appointment_time" name="appointment_time" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Service Type') }}</label>
                    <input type="text" id="edit_service_type" name="service_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Status') }}</label>
                    <select id="edit_status" name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="pending">{{ __('Pending') }}</option>
                        <option value="confirmed">{{ __('Confirmed') }}</option>
                        <option value="cancelled">{{ __('Cancelled') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Notes') }}</label>
                    <textarea id="edit_notes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div id="editErrorMessage" class="hidden bg-red-50 border border-red-200 text-red-800 rounded-lg p-3"></div>
                <div id="editSuccessMessage" class="hidden bg-green-50 border border-green-200 text-green-800 rounded-lg p-3"></div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        {{ __('Save Changes') }}
                    </button>
                    <button type="button" onclick="closeEditModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Appointment Modal -->
    <div id="appointmentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">{{ __('Add New Appointment') }}</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="appointmentForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Customer Name') }}</label>
                    <input type="text" name="customer_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Mobile Number') }}</label>
                    <input type="tel" name="customer_phone" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Email') }}</label>
                    <input type="email" name="customer_email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Appointment Date') }}</label>
                    <input type="date" name="appointment_date" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Appointment Time') }}</label>
                    <input type="time" name="appointment_time" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Service Type') }}</label>
                    <input type="text" name="service_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Notes') }}</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div id="errorMessage" class="hidden bg-red-50 border border-red-200 text-red-800 rounded-lg p-3"></div>
                <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-800 rounded-lg p-3"></div>

                <div class="flex justify-end {{ app()->getLocale() === 'ar' ? 'space-x-3 space-x-reverse' : 'space-x-3' }} pt-4">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        {{ __('Save Appointment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Translations object for JavaScript
        const translations = {
            appointmentId: '{{ __("Appointment ID") }}',
            status: '{{ __("Status") }}',
            customerName: '{{ __("Customer Name") }}',
            phoneNumber: '{{ __("Phone Number") }}',
            date: '{{ __("Date") }}',
            time: '{{ __("Time") }}',
            serviceType: '{{ __("Service Type") }}',
            notes: '{{ __("Notes") }}',
            appointmentAddedSuccess: '{{ __("Appointment added successfully!") }}',
            errorWhileSaving: '{{ __("Error occurred while saving") }}',
            errorTryAgain: '{{ __("Error occurred! Try again") }}',
            appointmentUpdatedSuccess: '{{ __("Appointment updated successfully!") }}',
            appointmentDeletedSuccess: '{{ __("Appointment deleted successfully!") }}',
            confirmDelete: '{{ __("Are you sure you want to delete this appointment?") }}',
            errorLoadingDetails: '{{ __("Error loading details") }}',
            errorLoadingData: '{{ __("Error loading data") }}',
            confirmed: '{{ __("Confirmed") }}',
            pending: '{{ __("Pending") }}',
            cancelled: '{{ __("Cancelled") }}'
        };

        function openModal() {
            document.getElementById('appointmentModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('appointmentModal').classList.add('hidden');
            document.getElementById('appointmentForm').reset();
            document.getElementById('errorMessage').classList.add('hidden');
            document.getElementById('successMessage').classList.add('hidden');
        }

        document.getElementById('appointmentForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);

            const errorDiv = document.getElementById('errorMessage');
            const successDiv = document.getElementById('successMessage');

            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');

            try {
                const response = await fetch('/admin/api/appointments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    successDiv.textContent = '✓ ' + translations.appointmentAddedSuccess;
                    successDiv.classList.remove('hidden');

                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    errorDiv.textContent = '✕ ' + (result.message || translations.errorWhileSaving);
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = '✕ ' + translations.errorTryAgain;
                errorDiv.classList.remove('hidden');
            }
        });

        // Close modal on outside click
        document.getElementById('appointmentModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // View appointment details
        async function viewAppointment(id) {
            const modal = document.getElementById('viewModal');
            const details = document.getElementById('appointmentDetails');

            try {
                const response = await fetch(`/admin/api/appointments/${id}`);
                const result = await response.json();

                if (response.ok && result.success) {
                    const appointment = result.data;
                    const statusText = appointment.status === 'confirmed' ? translations.confirmed :
                                      appointment.status === 'pending' ? translations.pending :
                                      appointment.status === 'cancelled' ? translations.cancelled : appointment.status;
                    details.innerHTML = `
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">${translations.appointmentId}</p>
                                <p class="mt-1 text-sm text-gray-900">#${appointment.id}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">${translations.status}</p>
                                <p class="mt-1 text-sm text-gray-900">${statusText}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">${translations.customerName}</p>
                                <p class="mt-1 text-sm text-gray-900">${appointment.customer_name}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">${translations.phoneNumber}</p>
                                <p class="mt-1 text-sm text-gray-900">${appointment.customer_phone}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">${translations.date}</p>
                                <p class="mt-1 text-sm text-gray-900">${appointment.date}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">${translations.time}</p>
                                <p class="mt-1 text-sm text-gray-900">${appointment.time_slot}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-sm font-medium text-gray-500">${translations.serviceType}</p>
                                <p class="mt-1 text-sm text-gray-900">${appointment.service_type || '-'}</p>
                            </div>
                            ${appointment.notes ? `
                            <div class="col-span-2">
                                <p class="text-sm font-medium text-gray-500">${translations.notes}</p>
                                <p class="mt-1 text-sm text-gray-900">${appointment.notes}</p>
                            </div>
                            ` : ''}
                        </div>
                    `;
                    modal.classList.remove('hidden');
                } else {
                    alert(translations.errorLoadingDetails);
                }
            } catch (error) {
                alert(translations.errorTryAgain);
            }
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.add('hidden');
        }

        // Delete appointment
        async function deleteAppointment(id) {
            if (!confirm(translations.confirmDelete)) {
                return;
            }

            try {
                const response = await fetch(`/admin/api/appointments/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    alert('✓ ' + translations.appointmentDeletedSuccess);
                    window.location.reload();
                } else {
                    alert('✕ ' + (result.message || translations.errorWhileSaving));
                }
            } catch (error) {
                alert('✕ ' + translations.errorTryAgain);
            }
        }

        // Close view modal on outside click
        document.getElementById('viewModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeViewModal();
            }
        });

        // Edit appointment
        async function editAppointment(id) {
            const modal = document.getElementById('editModal');

            try {
                const response = await fetch(`/admin/api/appointments/${id}`);
                const result = await response.json();

                if (response.ok && result.success) {
                    const appointment = result.data;

                    // Fill form with current data
                    document.getElementById('edit_appointment_id').value = appointment.id;
                    document.getElementById('edit_customer_name').value = appointment.customer_name;
                    document.getElementById('edit_customer_phone').value = appointment.customer_phone || '';
                    document.getElementById('edit_customer_email').value = appointment.customer_email || '';
                    document.getElementById('edit_appointment_date').value = appointment.date;
                    document.getElementById('edit_appointment_time').value = appointment.time_slot;
                    document.getElementById('edit_service_type').value = appointment.service_type || '';
                    document.getElementById('edit_status').value = appointment.status;
                    document.getElementById('edit_notes').value = appointment.notes || '';

                    modal.classList.remove('hidden');
                } else {
                    alert(translations.errorLoadingData);
                }
            } catch (error) {
                alert(translations.errorTryAgain);
            }
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            modal.classList.add('hidden');
            document.getElementById('editAppointmentForm').reset();
            document.getElementById('editErrorMessage').classList.add('hidden');
            document.getElementById('editSuccessMessage').classList.add('hidden');
        }

        // Handle edit form submission
        document.getElementById('editAppointmentForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            const appointmentId = data.appointment_id;
            delete data.appointment_id;

            const errorDiv = document.getElementById('editErrorMessage');
            const successDiv = document.getElementById('editSuccessMessage');

            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');

            try {
                const response = await fetch(`/admin/api/appointments/${appointmentId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    successDiv.textContent = '✓ ' + translations.appointmentUpdatedSuccess;
                    successDiv.classList.remove('hidden');

                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    errorDiv.textContent = '✕ ' + (result.message || translations.errorWhileSaving);
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = '✕ ' + translations.errorTryAgain;
                errorDiv.classList.remove('hidden');
            }
        });

        // Close edit modal on outside click
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>
