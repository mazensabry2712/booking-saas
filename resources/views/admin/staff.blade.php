<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Manage Staff') }} - {{ tenant()->name }}</title>
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
            <h2 class="text-2xl font-bold text-gray-900">{{ __('Manage Staff') }}</h2>
            <p class="text-gray-600 mt-1">{{ __('Add and manage staff members with their services and schedules') }}</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Add New Staff Button -->
        <div class="mb-6">
            <button onclick="openAddModal()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                {{ __('Add New Staff Member') }}
            </button>
        </div>

        <!-- Staff List -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('Staff Members') }}</h3>
            </div>

            @if($staffMembers->count() > 0)
                <div class="divide-y">
                    @foreach($staffMembers as $staff)
                        <div class="p-6 hover:bg-gray-50">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                            <span class="text-blue-600 font-bold text-lg">{{ substr($staff->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $staff->name }}</h4>
                                            <p class="text-sm text-gray-500">{{ $staff->email }}</p>
                                        </div>
                                    </div>

                                    <!-- Services -->
                                    <div class="mb-3">
                                        <span class="text-sm font-medium text-gray-700">{{ __('Services') }}:</span>
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            @forelse($staff->services as $service)
                                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                                    {{ app()->getLocale() === 'ar' && $service->name_ar ? $service->name_ar : $service->name }}
                                                </span>
                                            @empty
                                                <span class="text-gray-400 text-sm">{{ __('No services assigned') }}</span>
                                            @endforelse
                                        </div>
                                    </div>

                                    <!-- Schedule -->
                                    <div>
                                        <span class="text-sm font-medium text-gray-700">{{ __('Working Schedule') }}:</span>
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            @forelse($staff->activeSchedules as $schedule)
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                                                    {{ app()->getLocale() === 'ar' ? $schedule->day_name_ar : $schedule->day_name }}
                                                    ({{ $schedule->formatted_start_time }} - {{ $schedule->formatted_end_time }})
                                                </span>
                                            @empty
                                                <span class="text-gray-400 text-sm">{{ __('No schedule set') }}</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <button onclick="editStaff({{ $staff->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="{{ __('Edit') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="deleteStaff({{ $staff->id }})" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="{{ __('Delete') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No staff members') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Start by adding your first staff member') }}</p>
                </div>
            @endif
        </div>

        <!-- Services Management Section -->
        <div class="mt-8 bg-white rounded-lg shadow">
            <div class="p-6 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('Services') }}</h3>
                <button onclick="openServiceModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm">
                    {{ __('Add Service') }}
                </button>
            </div>

            <div class="p-6">
                @if($services->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($services as $service)
                            <div class="border rounded-lg p-4 {{ $service->is_active ? 'bg-white' : 'bg-gray-100' }}">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ $service->name }}</h4>
                                        @if($service->name_ar)
                                            <p class="text-sm text-gray-500">{{ $service->name_ar }}</p>
                                        @endif
                                    </div>
                                    <div class="flex gap-1">
                                        <button onclick="editService({{ $service->id }})" class="p-1 text-blue-600 hover:bg-blue-50 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button onclick="deleteService({{ $service->id }})" class="p-1 text-red-600 hover:bg-red-50 rounded">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">{{ __('No services yet. Add your first service.') }}</p>
                @endif
            </div>
        </div>
    </main>

    <!-- Add/Edit Staff Modal -->
    <div id="staffModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-lg bg-white max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4 sticky top-0 bg-white pb-4 border-b">
                <h3 class="text-xl font-bold text-gray-900" id="staffModalTitle">{{ __('Add New Staff Member') }}</h3>
                <button onclick="closeStaffModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="staffForm" class="space-y-6">
                @csrf
                <input type="hidden" id="staff_id" name="staff_id">

                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Full Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" id="staff_name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Email') }} <span class="text-red-500">*</span></label>
                        <input type="email" id="staff_email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Phone Number') }}</label>
                        <input type="tel" id="staff_phone" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div id="passwordField">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Password') }} <span class="text-red-500">*</span></label>
                        <input type="password" id="staff_password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Services Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Services') }}</label>
                    <div class="border rounded-lg p-4 max-h-40 overflow-y-auto">
                        @forelse($services as $service)
                            <label class="flex items-center gap-2 py-1 cursor-pointer hover:bg-gray-50 px-2 rounded">
                                <input type="checkbox" name="services[]" value="{{ $service->id }}" class="service-checkbox rounded text-blue-600">
                                <span>{{ app()->getLocale() === 'ar' && $service->name_ar ? $service->name_ar : $service->name }}</span>
                            </label>
                        @empty
                            <p class="text-gray-400 text-sm">{{ __('No services available. Please add services first.') }}</p>
                        @endforelse
                    </div>
                </div>

                <!-- Working Schedule -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Working Schedule') }}</label>
                    <div class="border rounded-lg overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }} text-sm font-medium text-gray-700">{{ __('Day') }}</th>
                                    <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">{{ __('Working') }}</th>
                                    <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">{{ __('Start Time') }}</th>
                                    <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">{{ __('End Time') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @php
                                    $days = [
                                        0 => ['en' => 'Sunday', 'ar' => 'الأحد'],
                                        1 => ['en' => 'Monday', 'ar' => 'الإثنين'],
                                        2 => ['en' => 'Tuesday', 'ar' => 'الثلاثاء'],
                                        3 => ['en' => 'Wednesday', 'ar' => 'الأربعاء'],
                                        4 => ['en' => 'Thursday', 'ar' => 'الخميس'],
                                        5 => ['en' => 'Friday', 'ar' => 'الجمعة'],
                                        6 => ['en' => 'Saturday', 'ar' => 'السبت'],
                                    ];
                                @endphp
                                @foreach($days as $dayNum => $dayNames)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2">
                                            <span class="font-medium">{{ app()->getLocale() === 'ar' ? $dayNames['ar'] : $dayNames['en'] }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <input type="checkbox" name="schedule[{{ $dayNum }}][active]" value="1" class="day-checkbox rounded text-blue-600" data-day="{{ $dayNum }}">
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <input type="time" name="schedule[{{ $dayNum }}][start]" id="start_{{ $dayNum }}" value="09:00" class="px-2 py-1 border rounded text-sm" disabled>
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <input type="time" name="schedule[{{ $dayNum }}][end]" id="end_{{ $dayNum }}" value="17:00" class="px-2 py-1 border rounded text-sm" disabled>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="staffErrorMessage" class="hidden bg-red-50 border border-red-200 text-red-800 rounded-lg p-3"></div>
                <div id="staffSuccessMessage" class="hidden bg-green-50 border border-green-200 text-green-800 rounded-lg p-3"></div>

                <div class="flex gap-3 pt-4 border-t">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeStaffModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Service Modal -->
    <div id="serviceModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900" id="serviceModalTitle">{{ __('Add Service') }}</h3>
                <button onclick="closeServiceModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="serviceForm" class="space-y-4">
                @csrf
                <input type="hidden" id="service_id" name="service_id">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Service Name (English)') }} <span class="text-red-500">*</span></label>
                    <input type="text" id="service_name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Service Name (Arabic)') }}</label>
                    <input type="text" id="service_name_ar" name="name_ar" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" dir="rtl">
                </div>

                <div id="serviceErrorMessage" class="hidden bg-red-50 border border-red-200 text-red-800 rounded-lg p-3"></div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeServiceModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // ========== Day checkbox toggle ==========
        document.querySelectorAll('.day-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const day = this.dataset.day;
                const startInput = document.getElementById(`start_${day}`);
                const endInput = document.getElementById(`end_${day}`);

                if (this.checked) {
                    startInput.disabled = false;
                    endInput.disabled = false;
                } else {
                    startInput.disabled = true;
                    endInput.disabled = true;
                }
            });
        });

        // ========== Staff Modal Functions ==========
        function openAddModal() {
            document.getElementById('staffModalTitle').textContent = '{{ __('Add New Staff Member') }}';
            document.getElementById('staffForm').reset();
            document.getElementById('staff_id').value = '';
            document.getElementById('passwordField').style.display = 'block';
            document.getElementById('staff_password').required = true;

            // Reset checkboxes and time inputs
            document.querySelectorAll('.service-checkbox').forEach(cb => cb.checked = false);
            document.querySelectorAll('.day-checkbox').forEach(cb => {
                cb.checked = false;
                const day = cb.dataset.day;
                document.getElementById(`start_${day}`).disabled = true;
                document.getElementById(`end_${day}`).disabled = true;
            });

            document.getElementById('staffModal').classList.remove('hidden');
        }

        function closeStaffModal() {
            document.getElementById('staffModal').classList.add('hidden');
            document.getElementById('staffForm').reset();
            document.getElementById('staffErrorMessage').classList.add('hidden');
            document.getElementById('staffSuccessMessage').classList.add('hidden');
        }

        async function editStaff(id) {
            try {
                const response = await fetch(`/admin/api/staff/${id}`);
                const result = await response.json();

                if (result.success) {
                    const staff = result.data;

                    document.getElementById('staffModalTitle').textContent = '{{ __('Edit Staff Member') }}';
                    document.getElementById('staff_id').value = staff.id;
                    document.getElementById('staff_name').value = staff.name;
                    document.getElementById('staff_email').value = staff.email;
                    document.getElementById('staff_phone').value = staff.phone || '';
                    document.getElementById('passwordField').style.display = 'none';
                    document.getElementById('staff_password').required = false;

                    // Set services
                    document.querySelectorAll('.service-checkbox').forEach(cb => {
                        cb.checked = staff.services.some(s => s.id == cb.value);
                    });

                    // Reset all days first
                    document.querySelectorAll('.day-checkbox').forEach(cb => {
                        cb.checked = false;
                        const day = cb.dataset.day;
                        document.getElementById(`start_${day}`).disabled = true;
                        document.getElementById(`end_${day}`).disabled = true;
                        document.getElementById(`start_${day}`).value = '09:00';
                        document.getElementById(`end_${day}`).value = '17:00';
                    });

                    // Set schedules
                    staff.schedules.forEach(schedule => {
                        const checkbox = document.querySelector(`.day-checkbox[data-day="${schedule.day_of_week}"]`);
                        if (checkbox) {
                            checkbox.checked = schedule.is_active;
                            document.getElementById(`start_${schedule.day_of_week}`).value = schedule.start_time;
                            document.getElementById(`end_${schedule.day_of_week}`).value = schedule.end_time;
                            document.getElementById(`start_${schedule.day_of_week}`).disabled = !schedule.is_active;
                            document.getElementById(`end_${schedule.day_of_week}`).disabled = !schedule.is_active;
                        }
                    });

                    document.getElementById('staffModal').classList.remove('hidden');
                }
            } catch (error) {
                alert('{{ __('Error loading data') }}');
            }
        }

        // Staff Form Submit
        document.getElementById('staffForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const errorDiv = document.getElementById('staffErrorMessage');
            const successDiv = document.getElementById('staffSuccessMessage');
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');

            const staffId = document.getElementById('staff_id').value;
            const isEdit = staffId !== '';

            // Collect form data
            const formData = {
                name: document.getElementById('staff_name').value,
                email: document.getElementById('staff_email').value,
                phone: document.getElementById('staff_phone').value,
                services: [],
                schedule: []
            };

            if (!isEdit) {
                formData.password = document.getElementById('staff_password').value;
            }

            // Get selected services
            document.querySelectorAll('.service-checkbox:checked').forEach(cb => {
                formData.services.push(parseInt(cb.value));
            });

            // Get schedule
            document.querySelectorAll('.day-checkbox').forEach(cb => {
                const day = parseInt(cb.dataset.day);
                if (cb.checked) {
                    formData.schedule.push({
                        day_of_week: day,
                        start_time: document.getElementById(`start_${day}`).value,
                        end_time: document.getElementById(`end_${day}`).value,
                        is_active: true
                    });
                }
            });

            try {
                const url = isEdit ? `/admin/api/staff/${staffId}` : '/admin/api/staff';
                const method = isEdit ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (result.success) {
                    successDiv.textContent = '✓ {{ __('Saved successfully') }}';
                    successDiv.classList.remove('hidden');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    errorDiv.textContent = result.message || '{{ __('Error occurred') }}';
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = '{{ __('Error occurred') }}';
                errorDiv.classList.remove('hidden');
            }
        });

        async function deleteStaff(id) {
            if (!confirm('{{ __('Are you sure you want to delete this staff member?') }}')) return;

            try {
                const response = await fetch(`/admin/api/staff/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || '{{ __('Error occurred') }}');
                }
            } catch (error) {
                alert('{{ __('Error occurred') }}');
            }
        }

        // ========== Service Modal Functions ==========
        function openServiceModal() {
            document.getElementById('serviceModalTitle').textContent = '{{ __('Add Service') }}';
            document.getElementById('serviceForm').reset();
            document.getElementById('service_id').value = '';
            document.getElementById('serviceModal').classList.remove('hidden');
        }

        function closeServiceModal() {
            document.getElementById('serviceModal').classList.add('hidden');
            document.getElementById('serviceForm').reset();
            document.getElementById('serviceErrorMessage').classList.add('hidden');
        }

        async function editService(id) {
            try {
                const response = await fetch(`/admin/api/settings/services/${id}`);
                const result = await response.json();

                if (result.success) {
                    document.getElementById('serviceModalTitle').textContent = '{{ __('Edit Service') }}';
                    document.getElementById('service_id').value = result.data.id;
                    document.getElementById('service_name').value = result.data.name;
                    document.getElementById('service_name_ar').value = result.data.name_ar || '';
                    document.getElementById('serviceModal').classList.remove('hidden');
                }
            } catch (error) {
                alert('{{ __('Error loading data') }}');
            }
        }

        document.getElementById('serviceForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const errorDiv = document.getElementById('serviceErrorMessage');
            errorDiv.classList.add('hidden');

            const serviceId = document.getElementById('service_id').value;
            const isEdit = serviceId !== '';

            const formData = {
                name: document.getElementById('service_name').value,
                name_ar: document.getElementById('service_name_ar').value,
                is_active: true
            };

            try {
                const url = isEdit ? `/admin/api/settings/services/${serviceId}` : '/admin/api/settings/services';
                const method = isEdit ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (result.success) {
                    window.location.reload();
                } else {
                    errorDiv.textContent = result.message || '{{ __('Error occurred') }}';
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = '{{ __('Error occurred') }}';
                errorDiv.classList.remove('hidden');
            }
        });

        async function deleteService(id) {
            if (!confirm('{{ __('Are you sure you want to delete this service?') }}')) return;

            try {
                const response = await fetch(`/admin/api/settings/services/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || '{{ __('Error occurred') }}');
                }
            } catch (error) {
                alert('{{ __('Error occurred') }}');
            }
        }
    </script>
</body>
</html>
