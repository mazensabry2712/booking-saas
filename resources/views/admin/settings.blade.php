<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Settings') }} - {{ tenant()->name }}</title>
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
            <h2 class="text-2xl font-bold text-gray-900">{{ __('Settings') }}</h2>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex {{ app()->getLocale() === 'ar' ? 'space-x-8 space-x-reverse' : 'space-x-8' }}">
                <button onclick="showTab('services')" id="tab-services" class="tab-btn border-b-2 border-blue-500 py-4 px-1 text-sm font-medium text-blue-600">
                    {{ __('Services') }}
                </button>
                <button onclick="showTab('timeslots')" id="tab-timeslots" class="tab-btn border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    {{ __('Time Slots') }}
                </button>
                <button onclick="showTab('workingdays')" id="tab-workingdays" class="tab-btn border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    {{ __('Working Days') }}
                </button>
                <button onclick="showTab('staffservices')" id="tab-staffservices" class="tab-btn border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    {{ __('Staff Services') }}
                </button>
            </nav>
        </div>

        <!-- Services Tab -->
        <div id="content-services" class="tab-content">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="mb-6 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Services List') }}</h3>
                    <button onclick="openServiceModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        {{ __('Add New Service') }}
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">{{ __('Name (Arabic)') }}</th>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">{{ __('Duration') }}</th>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">{{ __('Price') }}</th>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="servicesTableBody" class="bg-white divide-y divide-gray-200">
                            @forelse($services as $service)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $service->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $service->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $service->name_ar ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $service->duration }} {{ __('min') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $service->price ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $service->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $service->is_active ? __('Active') : __('Inactive') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="editService({{ $service->id }})" class="text-blue-600 hover:text-blue-900 {{ app()->getLocale() === 'ar' ? 'ml-3' : 'mr-3' }}">{{ __('Edit') }}</button>
                                    <button onclick="deleteService({{ $service->id }})" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">{{ __('No services found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Time Slots Tab -->
        <div id="content-timeslots" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="mb-6 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Time Slots List') }}</h3>
                    <button onclick="openTimeSlotModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        {{ __('Add New Time Slot') }}
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">{{ __('Start Time') }}</th>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">{{ __('End Time') }}</th>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                                <th class="px-6 py-3 {{ app()->getLocale() === 'ar' ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($timeSlots as $slot)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $slot->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ date('h:i A', strtotime($slot->start_time)) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ date('h:i A', strtotime($slot->end_time)) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $slot->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $slot->is_active ? __('Active') : __('Inactive') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="toggleTimeSlot({{ $slot->id }}, {{ $slot->is_active ? 'false' : 'true' }})" class="text-blue-600 hover:text-blue-900 {{ app()->getLocale() === 'ar' ? 'ml-3' : 'mr-3' }}">
                                        {{ $slot->is_active ? __('Deactivate') : __('Activate') }}
                                    </button>
                                    <button onclick="deleteTimeSlot({{ $slot->id }})" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">{{ __('No time slots found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Working Days Tab -->
        <div id="content-workingdays" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Working Days') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ __('Select the days your business is open') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($workingDays as $day)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">{{ app()->getLocale() === 'ar' ? $day->day_name_ar : $day->day_name }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer" {{ $day->is_active ? 'checked' : '' }}
                                   onchange="toggleWorkingDay({{ $day->id }}, this.checked)">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] {{ app()->getLocale() === 'ar' ? 'after:right-[2px] peer-checked:after:-translate-x-full' : 'after:left-[2px]' }} after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Staff Services Tab -->
        <div id="content-staffservices" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Assign Services to Staff') }}</h3>
                    <p class="text-sm text-gray-500 mt-1">{{ __('Select which services each staff member can provide') }}</p>
                </div>

                @forelse($staffMembers as $staff)
                <div class="mb-6 p-4 border border-gray-200 rounded-lg">
                    <h4 class="font-medium text-gray-900 mb-3">{{ $staff->name }}</h4>
                    <div class="flex flex-wrap gap-3">
                        @foreach($services as $service)
                        <label class="inline-flex items-center">
                            <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600 rounded"
                                   {{ $staff->services->contains($service->id) ? 'checked' : '' }}
                                   onchange="toggleStaffService({{ $staff->id }}, {{ $service->id }}, this.checked)">
                            <span class="ml-2 text-gray-700">{{ app()->getLocale() === 'ar' && $service->name_ar ? $service->name_ar : $service->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @empty
                <p class="text-gray-500 text-center py-4">{{ __('No staff members found') }}</p>
                @endforelse
            </div>
        </div>
    </main>

    <!-- Service Modal -->
    <div id="serviceModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 id="serviceModalTitle" class="text-xl font-bold text-gray-900">{{ __('Add New Service') }}</h3>
                <button onclick="closeServiceModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="serviceForm" class="space-y-4">
                <input type="hidden" id="service_id" name="id">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Service Name (English)') }}</label>
                    <input type="text" id="service_name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Service Name (Arabic)') }}</label>
                    <input type="text" id="service_name_ar" name="name_ar" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Duration (minutes)') }}</label>
                    <input type="number" id="service_duration" name="duration" value="30" min="5" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Price') }}</label>
                    <input type="number" id="service_price" name="price" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Description') }}</label>
                    <textarea id="service_description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="service_is_active" name="is_active" checked class="h-4 w-4 text-blue-600 rounded">
                    <label class="ml-2 text-sm text-gray-700">{{ __('Active') }}</label>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeServiceModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Time Slot Modal -->
    <div id="timeSlotModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">{{ __('Add New Time Slot') }}</h3>
                <button onclick="closeTimeSlotModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="timeSlotForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Start Time') }}</label>
                    <input type="time" id="slot_start_time" name="start_time" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('End Time') }}</label>
                    <input type="time" id="slot_end_time" name="end_time" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        {{ __('Save') }}
                    </button>
                    <button type="button" onclick="closeTimeSlotModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Tab switching
        function showTab(tab) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.tab-btn').forEach(el => {
                el.classList.remove('border-blue-500', 'text-blue-600');
                el.classList.add('border-transparent', 'text-gray-500');
            });

            document.getElementById('content-' + tab).classList.remove('hidden');
            document.getElementById('tab-' + tab).classList.remove('border-transparent', 'text-gray-500');
            document.getElementById('tab-' + tab).classList.add('border-blue-500', 'text-blue-600');
        }

        // Service Modal
        function openServiceModal() {
            document.getElementById('serviceModal').classList.remove('hidden');
            document.getElementById('serviceModalTitle').textContent = '{{ __("Add New Service") }}';
            document.getElementById('serviceForm').reset();
            document.getElementById('service_id').value = '';
            document.getElementById('service_is_active').checked = true;
        }

        function closeServiceModal() {
            document.getElementById('serviceModal').classList.add('hidden');
        }

        async function editService(id) {
            try {
                const response = await fetch(`/admin/api/settings/services/${id}`);
                const result = await response.json();

                if (result.success) {
                    const service = result.data;
                    document.getElementById('service_id').value = service.id;
                    document.getElementById('service_name').value = service.name;
                    document.getElementById('service_name_ar').value = service.name_ar || '';
                    document.getElementById('service_duration').value = service.duration;
                    document.getElementById('service_price').value = service.price || '';
                    document.getElementById('service_description').value = service.description || '';
                    document.getElementById('service_is_active').checked = service.is_active;
                    document.getElementById('serviceModalTitle').textContent = '{{ __("Edit Service") }}';
                    document.getElementById('serviceModal').classList.remove('hidden');
                }
            } catch (error) {
                alert('{{ __("Error loading data") }}');
            }
        }

        document.getElementById('serviceForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            data.is_active = document.getElementById('service_is_active').checked;

            const url = data.id ? `/admin/api/settings/services/${data.id}` : '/admin/api/settings/services';
            const method = data.id ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || '{{ __("Error occurred") }}');
                }
            } catch (error) {
                alert('{{ __("Error occurred") }}');
            }
        });

        async function deleteService(id) {
            if (!confirm('{{ __("Are you sure you want to delete this service?") }}')) return;

            try {
                const response = await fetch(`/admin/api/settings/services/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || '{{ __("Error occurred") }}');
                }
            } catch (error) {
                alert('{{ __("Error occurred") }}');
            }
        }

        // Time Slot Modal
        function openTimeSlotModal() {
            document.getElementById('timeSlotModal').classList.remove('hidden');
            document.getElementById('timeSlotForm').reset();
        }

        function closeTimeSlotModal() {
            document.getElementById('timeSlotModal').classList.add('hidden');
        }

        document.getElementById('timeSlotForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('/admin/api/settings/timeslots', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || '{{ __("Error occurred") }}');
                }
            } catch (error) {
                alert('{{ __("Error occurred") }}');
            }
        });

        async function toggleTimeSlot(id, isActive) {
            try {
                const response = await fetch(`/admin/api/settings/timeslots/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ is_active: isActive })
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                }
            } catch (error) {
                alert('{{ __("Error occurred") }}');
            }
        }

        async function deleteTimeSlot(id) {
            if (!confirm('{{ __("Are you sure you want to delete this time slot?") }}')) return;

            try {
                const response = await fetch(`/admin/api/settings/timeslots/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                }
            } catch (error) {
                alert('{{ __("Error occurred") }}');
            }
        }

        // Working Days
        async function toggleWorkingDay(id, isActive) {
            try {
                const response = await fetch(`/admin/api/settings/workingdays/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ is_active: isActive })
                });

                const result = await response.json();
                if (!result.success) {
                    alert('{{ __("Error occurred") }}');
                    window.location.reload();
                }
            } catch (error) {
                alert('{{ __("Error occurred") }}');
            }
        }

        // Staff Services
        async function toggleStaffService(staffId, serviceId, isChecked) {
            try {
                const response = await fetch('/admin/api/settings/staff-services', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        staff_id: staffId,
                        service_id: serviceId,
                        attach: isChecked
                    })
                });

                const result = await response.json();
                if (!result.success) {
                    alert('{{ __("Error occurred") }}');
                    window.location.reload();
                }
            } catch (error) {
                alert('{{ __("Error occurred") }}');
            }
        }
    </script>
</body>
</html>
