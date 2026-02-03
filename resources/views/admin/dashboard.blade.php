@extends('layouts.admin')

@section('title', __('Dashboard'))
@section('subtitle', __('Welcome back! Here\'s what\'s happening today.'))

@section('content')
    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">{{ __('Total Appointments') }}</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_appointments'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 font-medium">+12%</span>
                <span class="text-gray-500 {{ app()->getLocale() === 'ar' ? 'mr-2' : 'ml-2' }}">{{ __('from last week') }}</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">{{ __('Confirmed') }}</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['confirmed'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-green-600 font-medium">{{ $stats['confirmed'] ?? 0 }}</span>
                <span class="text-gray-500 {{ app()->getLocale() === 'ar' ? 'mr-2' : 'ml-2' }}">{{ __('ready for today') }}</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">{{ __('In Queue') }}</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $stats['queue'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-yellow-600 font-medium">{{ $stats['queue'] ?? 0 }}</span>
                <span class="text-gray-500 {{ app()->getLocale() === 'ar' ? 'mr-2' : 'ml-2' }}">{{ __('waiting now') }}</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">{{ __('Customers') }}</p>
                    <p class="text-3xl font-bold text-purple-600 mt-1">{{ $stats['customers'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center text-sm">
                <span class="text-purple-600 font-medium">+5</span>
                <span class="text-gray-500 {{ app()->getLocale() === 'ar' ? 'mr-2' : 'ml-2' }}">{{ __('new this week') }}</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Quick Actions') }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <a href="/admin/appointments" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-blue-200 transition-all group">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-blue-200 transition-colors">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900">{{ __('Appointments') }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ __('Manage bookings') }}</p>
            </a>

            <a href="/admin/queue" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-yellow-200 transition-all group">
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-yellow-200 transition-colors">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900">{{ __('Queue') }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ __('Track waiting') }}</p>
            </a>

            <a href="/admin/staff" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-indigo-200 transition-all group">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-indigo-200 transition-colors">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900">{{ __('Staff') }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ __('Manage team') }}</p>
            </a>

            <a href="/admin/reports" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-green-200 transition-all group">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-green-200 transition-colors">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900">{{ __('Reports') }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ __('View stats') }}</p>
            </a>

            <a href="/admin/settings" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-gray-300 transition-all group">
                <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-gray-200 transition-colors">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900">{{ __('Settings') }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ __('Configure') }}</p>
            </a>

            @if(auth()->user()->isAdminTenant())
            <a href="/admin/assistants" class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md hover:border-purple-200 transition-all group">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-3 group-hover:bg-purple-200 transition-colors">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900">{{ __('Assistants') }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ __('Manage team') }}</p>
            </a>
            @endif
        </div>
    </div>

    <!-- Recent Activity & Quick Links -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Today's Appointments -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-5 border-b flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">{{ __('Today\'s Appointments') }}</h3>
                <a href="/admin/appointments" class="text-sm text-blue-600 hover:text-blue-800">{{ __('View all') }} →</a>
            </div>
            <div class="p-5">
                @if(isset($todayAppointments) && count($todayAppointments) > 0)
                    <div class="space-y-3">
                        @foreach($todayAppointments->take(5) as $appointment)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-bold">{{ substr($appointment->customer_name, 0, 1) }}</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $appointment->customer_name }}</p>
                                <p class="text-xs text-gray-500">{{ $appointment->service?->name ?? 'N/A' }}</p>
                            </div>
                            <span class="text-sm text-gray-600">{{ $appointment->time_slot ?? 'N/A' }}</span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-gray-500">{{ __('No appointments for today') }}</p>
                        <a href="/admin/appointments" class="text-blue-600 text-sm hover:underline mt-2 inline-block">{{ __('Create new appointment') }}</a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Current Queue -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="p-5 border-b flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">{{ __('Current Queue') }}</h3>
                <a href="/admin/queue" class="text-sm text-blue-600 hover:text-blue-800">{{ __('Manage') }} →</a>
            </div>
            <div class="p-5">
                @if(isset($currentQueue) && count($currentQueue) > 0)
                    <div class="space-y-3">
                        @foreach($currentQueue->take(5) as $queue)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                <span class="text-yellow-600 font-bold">#{{ $queue->queue_number }}</span>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ $queue->customer_name }}</p>
                                <p class="text-xs text-gray-500">{{ $queue->status }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-gray-500">{{ __('No one in queue') }}</p>
                        <a href="/admin/queue" class="text-blue-600 text-sm hover:underline mt-2 inline-block">{{ __('Add to queue') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
