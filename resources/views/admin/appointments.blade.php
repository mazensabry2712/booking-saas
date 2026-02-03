<!DOCTYPE html>
@php
    $isArabic = app()->getLocale() === 'ar';
@endphp
<html lang="{{ app()->getLocale() }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $isArabic ? 'إدارة المواعيد' : 'Manage Appointments' }} - {{ tenant()->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .status-badge { @apply px-2 py-1 text-xs font-semibold rounded-full cursor-pointer transition-all; }
        .status-pending { @apply bg-yellow-100 text-yellow-800 hover:bg-yellow-200; }
        .status-confirmed { @apply bg-green-100 text-green-800 hover:bg-green-200; }
        .status-cancelled { @apply bg-red-100 text-red-800 hover:bg-red-200; }
        .status-completed { @apply bg-blue-100 text-blue-800 hover:bg-blue-200; }
        .stat-card { @apply bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow; }
        .filter-input { @apply px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent; }
    </style>
</head>
<body class="bg-gray-50">
    @include('partials.admin-nav')

    <!-- Page Header -->
    <header class="bg-white border-b">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $isArabic ? 'إدارة المواعيد' : 'Manage Appointments' }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ $isArabic ? 'عرض وإدارة جميع المواعيد' : 'View and manage all appointments' }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.reports') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        {{ $isArabic ? 'التقارير' : 'Reports' }}
                    </a>
                    <button onclick="openAddModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        {{ $isArabic ? 'موعد جديد' : 'New Appointment' }}
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <!-- Today's Appointments -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['today'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">{{ $isArabic ? 'مواعيد اليوم' : "Today's" }}</p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <span class="text-xs text-green-600 font-medium">{{ $stats['today_confirmed'] ?? 0 }} {{ $isArabic ? 'مؤكد' : 'confirmed' }}</span>
                </div>
            </div>

            <!-- Pending -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">{{ $isArabic ? 'قيد الانتظار' : 'Pending' }}</p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <span class="text-xs text-gray-500">{{ $isArabic ? 'يحتاج تأكيد' : 'need confirmation' }}</span>
                </div>
            </div>

            <!-- This Week -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['this_week'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">{{ $isArabic ? 'هذا الأسبوع' : 'This Week' }}</p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <span class="text-xs text-gray-500">{{ $isArabic ? 'موعد' : 'appointments' }}</span>
                </div>
            </div>

            <!-- Cancelled -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-600">{{ $stats['cancelled_month'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">{{ $isArabic ? 'ملغي' : 'Cancelled' }}</p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <span class="text-xs text-gray-500">{{ $isArabic ? 'هذا الشهر' : 'this month' }}</span>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <form method="GET" action="{{ route('admin.appointments') }}" id="filterForm">
                <!-- Row 1: Search, Period, Status -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ $isArabic ? 'بحث' : 'Search' }}</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="{{ $isArabic ? 'اسم أو هاتف...' : 'Name or phone...' }}"
                                   class="filter-input w-full {{ $isArabic ? 'pr-10' : 'pl-10' }}">
                            <svg class="w-4 h-4 text-gray-400 absolute top-1/2 -translate-y-1/2 {{ $isArabic ? 'right-3' : 'left-3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ $isArabic ? 'الفترة' : 'Period' }}</label>
                        <select name="date_filter" class="filter-input w-full" onchange="toggleCustomDate(this)">
                            <option value="">{{ $isArabic ? 'الكل' : 'All' }}</option>
                            <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>{{ $isArabic ? 'اليوم' : 'Today' }}</option>
                            <option value="week" {{ request('date_filter') === 'week' ? 'selected' : '' }}>{{ $isArabic ? 'هذا الأسبوع' : 'This Week' }}</option>
                            <option value="month" {{ request('date_filter') === 'month' ? 'selected' : '' }}>{{ $isArabic ? 'هذا الشهر' : 'This Month' }}</option>
                            <option value="custom" {{ request('date_filter') === 'custom' ? 'selected' : '' }}>{{ $isArabic ? 'تحديد' : 'Custom' }}</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ $isArabic ? 'الحالة' : 'Status' }}</label>
                        <select name="status" class="filter-input w-full">
                            <option value="all">{{ $isArabic ? 'الكل' : 'All' }}</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ $isArabic ? 'قيد الانتظار' : 'Pending' }}</option>
                            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>{{ $isArabic ? 'مؤكد' : 'Confirmed' }}</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ $isArabic ? 'مكتمل' : 'Completed' }}</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ $isArabic ? 'ملغي' : 'Cancelled' }}</option>
                        </select>
                    </div>

                    <!-- Staff Filter -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ $isArabic ? 'الموظف' : 'Staff' }}</label>
                        <select name="staff_id" id="staff_filter" class="filter-input w-full" onchange="loadStaffServices(this.value)">
                            <option value="">{{ $isArabic ? 'الكل' : 'All' }}</option>
                            @foreach($staffMembers ?? [] as $staff)
                                <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Row 2: Service, Service Type, Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Service Filter (from Services table) -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ $isArabic ? 'الخدمة' : 'Service' }}</label>
                        <select name="service_name" id="service_filter" class="filter-input w-full">
                            <option value="">{{ $isArabic ? 'الكل' : 'All' }}</option>
                            @foreach($services ?? [] as $service)
                                <option value="{{ $service->name }}" {{ request('service_name') == $service->name ? 'selected' : '' }}>
                                    {{ $isArabic && $service->name_ar ? $service->name_ar : $service->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Service Type Filter (consultation, examination, etc.) -->
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ $isArabic ? 'نوع الخدمة' : 'Service Type' }}</label>
                        <select name="service_type" class="filter-input w-full">
                            <option value="">{{ $isArabic ? 'الكل' : 'All' }}</option>
                            @foreach($serviceTypes ?? [] as $type)
                                <option value="{{ $type }}" {{ request('service_type') == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Empty space for alignment -->
                    <div></div>

                    <!-- Actions -->
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                            {{ $isArabic ? 'بحث' : 'Filter' }}
                        </button>
                        <a href="{{ route('admin.appointments') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                            {{ $isArabic ? 'مسح' : 'Clear' }}
                        </a>
                    </div>
                </div>

                <!-- Custom Date Range (hidden by default) -->
                <div id="customDateRange" class="mt-4 grid grid-cols-2 gap-4 {{ request('date_filter') === 'custom' ? '' : 'hidden' }}">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ $isArabic ? 'من' : 'From' }}</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="filter-input w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ $isArabic ? 'إلى' : 'To' }}</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="filter-input w-full">
                    </div>
                </div>
            </form>
        </div>

        <!-- Appointments Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">
                    {{ $isArabic ? 'قائمة المواعيد' : 'Appointments List' }}
                    <span class="text-sm font-normal text-gray-500">({{ $appointments->total() }})</span>
                </h3>
                <div class="flex gap-2">
                    <button onclick="printAppointments()" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        {{ $isArabic ? 'طباعة' : 'Print' }}
                    </button>
                    <button onclick="exportExcel()" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs bg-green-50 text-green-600 rounded-lg hover:bg-green-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Excel
                    </button>
                </div>
            </div>

            @if($appointments->count() > 0)
                <div class="overflow-x-auto">
                    <table id="appointmentsTable" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 {{ $isArabic ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                <th class="px-6 py-3 {{ $isArabic ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'customer', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-700">
                                        {{ $isArabic ? 'العميل' : 'Customer' }}
                                        @if(request('sort') === 'customer')
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="{{ request('dir') === 'asc' ? 'M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z' : 'M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' }}" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3 {{ $isArabic ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $isArabic ? 'الهاتف' : 'Phone' }}</th>
                                <th class="px-6 py-3 {{ $isArabic ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'date', 'dir' => request('dir') === 'asc' ? 'desc' : 'asc']) }}" class="flex items-center gap-1 hover:text-gray-700">
                                        {{ $isArabic ? 'التاريخ' : 'Date' }}
                                        @if(request('sort', 'date') === 'date')
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="{{ request('dir', 'desc') === 'asc' ? 'M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z' : 'M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' }}" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                    </a>
                                </th>
                                <th class="px-6 py-3 {{ $isArabic ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $isArabic ? 'الوقت' : 'Time' }}</th>
                                <th class="px-6 py-3 {{ $isArabic ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $isArabic ? 'الخدمة' : 'Service' }}</th>
                                <th class="px-6 py-3 {{ $isArabic ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $isArabic ? 'الحالة' : 'Status' }}</th>
                                <th class="px-6 py-3 {{ $isArabic ? 'text-right' : 'text-left' }} text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $isArabic ? 'الإجراءات' : 'Actions' }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($appointments as $appointment)
                                <tr class="hover:bg-gray-50 transition-colors" id="row-{{ $appointment->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $appointment->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                <span class="text-blue-600 font-medium">{{ mb_substr($appointment->customer?->name ?? '?', 0, 1) }}</span>
                                            </div>
                                            <div class="{{ $isArabic ? 'mr-3' : 'ml-3' }}">
                                                <div class="text-sm font-medium text-gray-900">{{ $appointment->customer?->name ?? ($isArabic ? 'غير محدد' : 'N/A') }}</div>
                                                <div class="text-xs text-gray-500">{{ $appointment->customer?->email ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <a href="tel:{{ $appointment->customer?->phone }}" class="hover:text-blue-600">{{ $appointment->customer?->phone ?? '-' }}</a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $appointment->date->format('Y-m-d') }}</div>
                                        <div class="text-xs text-gray-500">{{ $appointment->date->translatedFormat($isArabic ? 'l' : 'D') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $appointment->time_slot }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $appointment->service_type ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="relative inline-block" x-data="{ open: false }">
                                            <button onclick="toggleStatusDropdown({{ $appointment->id }})"
                                                    class="status-badge status-{{ $appointment->status }}"
                                                    id="status-btn-{{ $appointment->id }}">
                                                @if($appointment->status === 'pending')
                                                    {{ $isArabic ? 'قيد الانتظار' : 'Pending' }}
                                                @elseif($appointment->status === 'confirmed')
                                                    {{ $isArabic ? 'مؤكد' : 'Confirmed' }}
                                                @elseif($appointment->status === 'completed')
                                                    {{ $isArabic ? 'مكتمل' : 'Completed' }}
                                                @elseif($appointment->status === 'cancelled')
                                                    {{ $isArabic ? 'ملغي' : 'Cancelled' }}
                                                @else
                                                    {{ $appointment->status }}
                                                @endif
                                                <svg class="w-3 h-3 inline {{ $isArabic ? 'mr-1' : 'ml-1' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            </button>
                                            <div id="status-dropdown-{{ $appointment->id }}" class="hidden absolute z-10 {{ $isArabic ? 'right-0' : 'left-0' }} mt-1 w-32 bg-white rounded-lg shadow-lg border border-gray-200 py-1">
                                                <button onclick="updateStatus({{ $appointment->id }}, 'pending')" class="w-full text-{{ $isArabic ? 'right' : 'left' }} px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-50">
                                                    {{ $isArabic ? 'قيد الانتظار' : 'Pending' }}
                                                </button>
                                                <button onclick="updateStatus({{ $appointment->id }}, 'confirmed')" class="w-full text-{{ $isArabic ? 'right' : 'left' }} px-4 py-2 text-sm text-green-700 hover:bg-green-50">
                                                    {{ $isArabic ? 'مؤكد' : 'Confirmed' }}
                                                </button>
                                                <button onclick="updateStatus({{ $appointment->id }}, 'completed')" class="w-full text-{{ $isArabic ? 'right' : 'left' }} px-4 py-2 text-sm text-blue-700 hover:bg-blue-50">
                                                    {{ $isArabic ? 'مكتمل' : 'Completed' }}
                                                </button>
                                                <button onclick="updateStatus({{ $appointment->id }}, 'cancelled')" class="w-full text-{{ $isArabic ? 'right' : 'left' }} px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                                    {{ $isArabic ? 'ملغي' : 'Cancelled' }}
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center gap-2">
                                            <button onclick="viewAppointment({{ $appointment->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="{{ $isArabic ? 'عرض' : 'View' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="editAppointment({{ $appointment->id }})" class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg" title="{{ $isArabic ? 'تعديل' : 'Edit' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="deleteAppointment({{ $appointment->id }})" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="{{ $isArabic ? 'حذف' : 'Delete' }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $appointments->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ $isArabic ? 'لا توجد مواعيد' : 'No appointments' }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $isArabic ? 'ابدأ بإضافة موعد جديد' : 'Start by adding a new appointment' }}</p>
                    <button onclick="openAddModal()" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        {{ $isArabic ? 'إضافة موعد' : 'Add Appointment' }}
                    </button>
                </div>
            @endif
        </div>
    </main>

    <!-- View Modal -->
    <div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
        <div class="flex items-center justify-center min-h-full p-4">
        <div class="relative bg-white rounded-xl shadow-xl w-11/12 max-w-lg mx-4">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">{{ $isArabic ? 'تفاصيل الموعد' : 'Appointment Details' }}</h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="appointmentDetails" class="p-4">
                <!-- Details loaded via JS -->
            </div>
        </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
        <div class="flex items-center justify-center min-h-full p-4">
        <div class="relative bg-white rounded-xl shadow-xl w-11/12 max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">{{ $isArabic ? 'تعديل الموعد' : 'Edit Appointment' }}</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="editForm" class="p-4 space-y-4">
                <input type="hidden" id="edit_id">

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isArabic ? 'اسم العميل' : 'Customer Name' }} *</label>
                        <input type="text" id="edit_name" required class="filter-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isArabic ? 'الهاتف' : 'Phone' }} *</label>
                        <input type="tel" id="edit_phone" required class="filter-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isArabic ? 'البريد' : 'Email' }}</label>
                        <input type="email" id="edit_email" class="filter-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isArabic ? 'التاريخ' : 'Date' }} *</label>
                        <input type="date" id="edit_date" required class="filter-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isArabic ? 'الوقت' : 'Time' }} *</label>
                        <input type="time" id="edit_time" required class="filter-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isArabic ? 'الخدمة' : 'Service' }}</label>
                        <select id="edit_service" class="filter-input w-full">
                            <option value="">{{ $isArabic ? 'اختر' : 'Select' }}</option>
                            @foreach($services ?? [] as $service)
                                <option value="{{ $service->name }}">{{ $isArabic && $service->name_ar ? $service->name_ar : $service->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isArabic ? 'الحالة' : 'Status' }} *</label>
                        <select id="edit_status" required class="filter-input w-full">
                            <option value="pending">{{ $isArabic ? 'قيد الانتظار' : 'Pending' }}</option>
                            <option value="confirmed">{{ $isArabic ? 'مؤكد' : 'Confirmed' }}</option>
                            <option value="completed">{{ $isArabic ? 'مكتمل' : 'Completed' }}</option>
                            <option value="cancelled">{{ $isArabic ? 'ملغي' : 'Cancelled' }}</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isArabic ? 'ملاحظات' : 'Notes' }}</label>
                        <textarea id="edit_notes" rows="3" class="filter-input w-full"></textarea>
                    </div>
                </div>

                <div id="editMessage" class="hidden p-3 rounded-lg text-sm"></div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        {{ $isArabic ? 'حفظ التعديلات' : 'Save Changes' }}
                    </button>
                    <button type="button" onclick="closeEditModal()" class="flex-1 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">
                        {{ $isArabic ? 'إلغاء' : 'Cancel' }}
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
        <div class="flex items-center justify-center min-h-full p-4">
        <div class="relative bg-white rounded-xl shadow-xl w-11/12 max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">{{ $isArabic ? 'موعد جديد' : 'New Appointment' }}</h3>
                <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="addForm" class="p-4 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isArabic ? 'اسم العميل' : 'Customer Name' }} *</label>
                        <input type="text" id="add_name" required class="filter-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isArabic ? 'الهاتف' : 'Phone' }} *</label>
                        <input type="tel" id="add_phone" required class="filter-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isArabic ? 'البريد' : 'Email' }}</label>
                        <input type="email" id="add_email" class="filter-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isArabic ? 'التاريخ' : 'Date' }} *</label>
                        <input type="date" id="add_date" required class="filter-input w-full" min="{{ date('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isArabic ? 'الوقت' : 'Time' }} *</label>
                        <select id="add_time" required class="filter-input w-full">
                            <option value="">{{ $isArabic ? 'اختر الوقت' : 'Select Time' }}</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isArabic ? 'الخدمة' : 'Service' }}</label>
                        <select id="add_service" class="filter-input w-full">
                            <option value="">{{ $isArabic ? 'اختر الخدمة' : 'Select Service' }}</option>
                            @foreach($services ?? [] as $service)
                                <option value="{{ $service->name }}">{{ $isArabic && $service->name_ar ? $service->name_ar : $service->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $isArabic ? 'ملاحظات' : 'Notes' }}</label>
                        <textarea id="add_notes" rows="3" class="filter-input w-full"></textarea>
                    </div>
                </div>

                <div id="addMessage" class="hidden p-3 rounded-lg text-sm"></div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                        {{ $isArabic ? 'حفظ الموعد' : 'Save Appointment' }}
                    </button>
                    <button type="button" onclick="closeAddModal()" class="flex-1 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">
                        {{ $isArabic ? 'إلغاء' : 'Cancel' }}
                    </button>
                </div>
            </form>
        </div>
        </div>
    </div>

    <script>
        const isArabic = {{ $isArabic ? 'true' : 'false' }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // All services data for filtering
        const allServices = @json($services ?? []);

        const texts = {
            confirmDelete: isArabic ? 'هل أنت متأكد من حذف هذا الموعد؟' : 'Are you sure you want to delete this appointment?',
            deleted: isArabic ? 'تم الحذف بنجاح' : 'Deleted successfully',
            saved: isArabic ? 'تم الحفظ بنجاح' : 'Saved successfully',
            error: isArabic ? 'حدث خطأ' : 'An error occurred',
            statusUpdated: isArabic ? 'تم تحديث الحالة' : 'Status updated',
            loading: isArabic ? 'جاري التحميل...' : 'Loading...',
            pending: isArabic ? 'قيد الانتظار' : 'Pending',
            confirmed: isArabic ? 'مؤكد' : 'Confirmed',
            completed: isArabic ? 'مكتمل' : 'Completed',
            cancelled: isArabic ? 'ملغي' : 'Cancelled',
            all: isArabic ? 'الكل' : 'All'
        };

        // Load staff services when staff is selected
        async function loadStaffServices(staffId) {
            const serviceFilter = document.getElementById('service_filter');

            if (!staffId) {
                // Reset to all services
                serviceFilter.innerHTML = `<option value="">${texts.all}</option>`;
                allServices.forEach(service => {
                    const name = isArabic && service.name_ar ? service.name_ar : service.name;
                    serviceFilter.innerHTML += `<option value="${service.name}">${name}</option>`;
                });
                return;
            }

            try {
                const response = await fetch(`/api/booking/staff/${staffId}/services`);
                const result = await response.json();

                if (result.success) {
                    serviceFilter.innerHTML = `<option value="">${texts.all}</option>`;
                    result.data.forEach(service => {
                        const name = isArabic && service.name_ar ? service.name_ar : service.name;
                        serviceFilter.innerHTML += `<option value="${service.name}">${name}</option>`;
                    });
                }
            } catch (error) {
                console.error('Error loading staff services:', error);
            }
        }

        // Toggle custom date range
        function toggleCustomDate(select) {
            const customRange = document.getElementById('customDateRange');
            if (select.value === 'custom') {
                customRange.classList.remove('hidden');
            } else {
                customRange.classList.add('hidden');
            }
        }

        // Status dropdown
        let activeDropdown = null;

        function toggleStatusDropdown(id) {
            const dropdown = document.getElementById(`status-dropdown-${id}`);

            // Close any open dropdown
            if (activeDropdown && activeDropdown !== dropdown) {
                activeDropdown.classList.add('hidden');
            }

            dropdown.classList.toggle('hidden');
            activeDropdown = dropdown.classList.contains('hidden') ? null : dropdown;
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (activeDropdown && !e.target.closest('[id^="status-btn-"]') && !e.target.closest('[id^="status-dropdown-"]')) {
                activeDropdown.classList.add('hidden');
                activeDropdown = null;
            }
        });

        // Quick status update
        async function updateStatus(id, status) {
            try {
                const response = await fetch(`/admin/api/appointments/${id}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ status })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Update button visually
                    const btn = document.getElementById(`status-btn-${id}`);
                    btn.className = `status-badge status-${status}`;
                    btn.innerHTML = `${texts[status]} <svg class="w-3 h-3 inline ${isArabic ? 'mr-1' : 'ml-1'}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>`;

                    // Close dropdown
                    document.getElementById(`status-dropdown-${id}`).classList.add('hidden');
                    activeDropdown = null;

                    // Show toast
                    showToast(texts.statusUpdated, 'success');
                } else {
                    showToast(result.message || texts.error, 'error');
                }
            } catch (error) {
                showToast(texts.error, 'error');
            }
        }

        // View appointment
        async function viewAppointment(id) {
            const modal = document.getElementById('viewModal');
            const details = document.getElementById('appointmentDetails');

            details.innerHTML = `<div class="text-center py-8 text-gray-500">${texts.loading}</div>`;
            modal.style.display = 'block';

            try {
                const response = await fetch(`/admin/api/appointments/${id}`);
                const result = await response.json();

                if (response.ok && result.success) {
                    const a = result.data;
                    details.innerHTML = `
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2 bg-gray-50 rounded-lg p-3">
                                <p class="text-xs text-gray-500 mb-1">${isArabic ? 'العميل' : 'Customer'}</p>
                                <p class="font-semibold text-gray-900">${a.customer_name}</p>
                                <p class="text-sm text-gray-600">${a.customer_phone}</p>
                                <p class="text-sm text-gray-500">${a.customer_email || '-'}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">${isArabic ? 'التاريخ' : 'Date'}</p>
                                <p class="font-medium text-gray-900">${a.date}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">${isArabic ? 'الوقت' : 'Time'}</p>
                                <p class="font-medium text-gray-900">${a.time_slot}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">${isArabic ? 'الخدمة' : 'Service'}</p>
                                <p class="font-medium text-gray-900">${a.service_type || '-'}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">${isArabic ? 'الحالة' : 'Status'}</p>
                                <span class="status-badge status-${a.status}">${texts[a.status] || a.status}</span>
                            </div>
                            ${a.notes ? `
                            <div class="col-span-2">
                                <p class="text-xs text-gray-500 mb-1">${isArabic ? 'ملاحظات' : 'Notes'}</p>
                                <p class="text-gray-700">${a.notes}</p>
                            </div>
                            ` : ''}
                        </div>
                        <div class="mt-4 pt-4 border-t flex gap-2">
                            <button onclick="closeViewModal(); editAppointment(${a.id});" class="flex-1 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                ${isArabic ? 'تعديل' : 'Edit'}
                            </button>
                            <button onclick="closeViewModal();" class="flex-1 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                                ${isArabic ? 'إغلاق' : 'Close'}
                            </button>
                        </div>
                    `;
                }
            } catch (error) {
                details.innerHTML = `<div class="text-center py-8 text-red-500">${texts.error}</div>`;
            }
        }

        function closeViewModal() {
            document.getElementById('viewModal').style.display = 'none';
        }

        // Edit appointment
        async function editAppointment(id) {
            try {
                const response = await fetch(`/admin/api/appointments/${id}`);
                const result = await response.json();

                if (response.ok && result.success) {
                    const a = result.data;
                    document.getElementById('edit_id').value = a.id;
                    document.getElementById('edit_name').value = a.customer_name;
                    document.getElementById('edit_phone').value = a.customer_phone || '';
                    document.getElementById('edit_email').value = a.customer_email || '';
                    document.getElementById('edit_date').value = a.date;
                    document.getElementById('edit_time').value = a.time_slot;
                    document.getElementById('edit_service').value = a.service_type || '';
                    document.getElementById('edit_status').value = a.status;
                    document.getElementById('edit_notes').value = a.notes || '';

                    document.getElementById('editModal').style.display = 'block';
                }
            } catch (error) {
                showToast(texts.error, 'error');
            }
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            document.getElementById('editForm').reset();
            document.getElementById('editMessage').classList.add('hidden');
        }

        // Handle edit form
        document.getElementById('editForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const id = document.getElementById('edit_id').value;
            const messageDiv = document.getElementById('editMessage');

            const data = {
                customer_name: document.getElementById('edit_name').value,
                customer_phone: document.getElementById('edit_phone').value,
                customer_email: document.getElementById('edit_email').value,
                appointment_date: document.getElementById('edit_date').value,
                appointment_time: document.getElementById('edit_time').value,
                service_type: document.getElementById('edit_service').value,
                status: document.getElementById('edit_status').value,
                notes: document.getElementById('edit_notes').value
            };

            try {
                const response = await fetch(`/admin/api/appointments/${id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    messageDiv.className = 'p-3 rounded-lg text-sm bg-green-50 text-green-700';
                    messageDiv.textContent = '✓ ' + texts.saved;
                    messageDiv.classList.remove('hidden');

                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    messageDiv.className = 'p-3 rounded-lg text-sm bg-red-50 text-red-700';
                    messageDiv.textContent = '✕ ' + (result.message || texts.error);
                    messageDiv.classList.remove('hidden');
                }
            } catch (error) {
                messageDiv.className = 'p-3 rounded-lg text-sm bg-red-50 text-red-700';
                messageDiv.textContent = '✕ ' + texts.error;
                messageDiv.classList.remove('hidden');
            }
        });

        // Add modal
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
            loadTimeSlots();
        }

        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
            document.getElementById('addForm').reset();
            document.getElementById('addMessage').classList.add('hidden');
        }

        // Load time slots
        async function loadTimeSlots() {
            try {
                const response = await fetch('/api/booking/timeslots');
                const result = await response.json();

                if (result.success) {
                    const select = document.getElementById('add_time');
                    select.innerHTML = `<option value="">${isArabic ? 'اختر الوقت' : 'Select Time'}</option>`;

                    result.data.forEach(slot => {
                        const option = document.createElement('option');
                        option.value = slot.start_time;
                        option.textContent = `${slot.formatted_start_time} - ${slot.formatted_end_time}`;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error loading time slots:', error);
            }
        }

        // Handle add form
        document.getElementById('addForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const messageDiv = document.getElementById('addMessage');

            const data = {
                customer_name: document.getElementById('add_name').value,
                customer_phone: document.getElementById('add_phone').value,
                customer_email: document.getElementById('add_email').value,
                appointment_date: document.getElementById('add_date').value,
                appointment_time: document.getElementById('add_time').value,
                service_type: document.getElementById('add_service').value,
                notes: document.getElementById('add_notes').value
            };

            try {
                const response = await fetch('/admin/api/appointments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    messageDiv.className = 'p-3 rounded-lg text-sm bg-green-50 text-green-700';
                    messageDiv.textContent = '✓ ' + texts.saved;
                    messageDiv.classList.remove('hidden');

                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    messageDiv.className = 'p-3 rounded-lg text-sm bg-red-50 text-red-700';
                    messageDiv.textContent = '✕ ' + (result.message || texts.error);
                    messageDiv.classList.remove('hidden');
                }
            } catch (error) {
                messageDiv.className = 'p-3 rounded-lg text-sm bg-red-50 text-red-700';
                messageDiv.textContent = '✕ ' + texts.error;
                messageDiv.classList.remove('hidden');
            }
        });

        // Delete appointment
        async function deleteAppointment(id) {
            if (!confirm(texts.confirmDelete)) return;

            try {
                const response = await fetch(`/admin/api/appointments/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    document.getElementById(`row-${id}`).remove();
                    showToast(texts.deleted, 'success');
                } else {
                    showToast(result.message || texts.error, 'error');
                }
            } catch (error) {
                showToast(texts.error, 'error');
            }
        }

        // Print appointments
        function printAppointments() {
            const printContent = document.getElementById('appointmentsTable');
            const printWindow = window.open('', '_blank');

            printWindow.document.write(`
                <!DOCTYPE html>
                <html dir="${isArabic ? 'rtl' : 'ltr'}">
                <head>
                    <meta charset="UTF-8">
                    <title>${isArabic ? 'طباعة المواعيد' : 'Print Appointments'}</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        h1 { text-align: center; margin-bottom: 20px; font-size: 24px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                        th, td { border: 1px solid #ddd; padding: 10px; text-align: ${isArabic ? 'right' : 'left'}; font-size: 12px; }
                        th { background-color: #f3f4f6; font-weight: bold; }
                        tr:nth-child(even) { background-color: #f9fafb; }
                        .status-pending { color: #92400e; background: #fef3c7; padding: 2px 8px; border-radius: 12px; }
                        .status-confirmed { color: #166534; background: #dcfce7; padding: 2px 8px; border-radius: 12px; }
                        .status-cancelled { color: #991b1b; background: #fee2e2; padding: 2px 8px; border-radius: 12px; }
                        .status-completed { color: #1e40af; background: #dbeafe; padding: 2px 8px; border-radius: 12px; }
                        .print-header { text-align: center; margin-bottom: 30px; }
                        .print-date { color: #666; font-size: 12px; }
                        @media print {
                            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                        }
                    </style>
                </head>
                <body>
                    <div class="print-header">
                        <h1>{{ tenant()->name }}</h1>
                        <p>${isArabic ? 'قائمة المواعيد' : 'Appointments List'}</p>
                        <p class="print-date">${isArabic ? 'تاريخ الطباعة:' : 'Print Date:'} ${new Date().toLocaleDateString('${isArabic ? 'ar-EG' : 'en-US'}')}</p>
                    </div>
                    ${printContent.outerHTML}
                </body>
                </html>
            `);

            printWindow.document.close();
            printWindow.onload = function() {
                printWindow.print();
            };
        }

        // Export to Excel
        function exportExcel() {
            const params = new URLSearchParams(window.location.search);
            // Add date filter if set
            const dateFilter = document.querySelector('select[name="date_filter"]')?.value;
            if (dateFilter) {
                params.set('period', dateFilter);
            }
            window.location.href = `/admin/api/appointments/export-excel?${params}`;
        }

        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 ${isArabic ? 'left-4' : 'right-4'} px-4 py-3 rounded-lg shadow-lg text-white text-sm z-50 ${type === 'success' ? 'bg-green-600' : 'bg-red-600'}`;
            toast.textContent = message;
            document.body.appendChild(toast);

            setTimeout(() => toast.remove(), 3000);
        }

        // Close modals on outside click
        ['viewModal', 'editModal', 'addModal'].forEach(modalId => {
            document.getElementById(modalId).addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
