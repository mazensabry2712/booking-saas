<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $businessSettings = \App\Models\Setting::where('tenant_id', tenant()->id)->first();
        $businessName = $businessSettings->business_name ?? tenant()->name ?? config('app.name');
    @endphp
    <title>{{ __('Queue Dashboard') }} - {{ $businessName }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes pulse-grow {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }
        .pulse-grow {
            animation: pulse-grow 2s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-100 min-h-screen">
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-8">
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <!-- Language Switcher -->
            <div class="flex justify-end mb-4">
                <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 shadow-sm">
                    <button onclick="changeLanguage('en')"
                        class="px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-medium rounded-md transition-all duration-200 {{ app()->getLocale() === 'en' ? 'bg-purple-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        EN
                    </button>
                    <button onclick="changeLanguage('ar')"
                        class="px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-medium rounded-md transition-all duration-200 {{ app()->getLocale() === 'ar' ? 'bg-purple-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                        عربي
                    </button>
                </div>
            </div>

            <div class="text-center">
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-800 mb-1 sm:mb-2">{{ __('Queue Dashboard') }}</h1>
                <p class="text-sm sm:text-base text-gray-600">{{ $businessName }}</p>
                <p class="text-xs sm:text-sm text-gray-500 mt-1 sm:mt-2">{{ __('Updates automatically every 10 seconds') }}</p>
            </div>
        </div>

        <!-- Now Serving -->
        <div class="max-w-4xl mx-auto mb-6 sm:mb-8">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl sm:rounded-2xl shadow-xl sm:shadow-2xl p-4 sm:p-6 md:p-8 text-white text-center">
                <h2 class="text-lg sm:text-xl md:text-2xl font-semibold mb-2 sm:mb-4">{{ __('NOW SERVING') }}</h2>
                <div id="currentServing" class="text-5xl sm:text-6xl md:text-7xl font-bold mb-2 sm:mb-4 pulse-grow">
                    ---
                </div>
                <p id="currentName" class="text-base sm:text-lg md:text-xl opacity-90">{{ __('Please wait...') }}</p>
            </div>
        </div>

        <!-- Next in Queue -->
        <div class="max-w-4xl mx-auto mb-6 sm:mb-8">
            <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg p-4 sm:p-6">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3 sm:mb-4 text-center">{{ __('Coming Up Next') }}</h3>
                <div id="nextQueue" class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-4">
                    <!-- Will be populated by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Waiting Queue -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 sm:gap-0 mb-4 sm:mb-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800">{{ __('Waiting Queue') }}</h3>
                    <span id="totalWaiting" class="px-3 sm:px-4 py-1 sm:py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                        0 {{ __('waiting') }}
                    </span>
                </div>

                <div id="waitingQueue" class="space-y-2 sm:space-y-3">
                    <!-- Will be populated by JavaScript -->
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="text-center py-8 sm:py-12 text-gray-400">
                    <svg class="w-12 h-12 sm:w-16 sm:h-16 mx-auto mb-3 sm:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-base sm:text-lg">{{ __('No one in queue') }}</p>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="max-w-4xl mx-auto mt-6 sm:mt-8 grid grid-cols-3 gap-2 sm:gap-4 md:gap-6">
            <div class="bg-white rounded-lg shadow p-2 sm:p-4 md:p-6 text-center">
                <div class="text-xl sm:text-2xl md:text-3xl font-bold text-green-600" id="servedToday">0</div>
                <p class="text-[10px] sm:text-xs md:text-sm text-gray-600 mt-1 sm:mt-2 leading-tight">{{ __('Served Today') }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-2 sm:p-4 md:p-6 text-center">
                <div class="text-xl sm:text-2xl md:text-3xl font-bold text-blue-600" id="avgWaitTime">0</div>
                <p class="text-[10px] sm:text-xs md:text-sm text-gray-600 mt-1 sm:mt-2 leading-tight">{{ __('Avg Wait (min)') }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-2 sm:p-4 md:p-6 text-center">
                <div class="text-xl sm:text-2xl md:text-3xl font-bold text-purple-600" id="vipQueue">0</div>
                <p class="text-[10px] sm:text-xs md:text-sm text-gray-600 mt-1 sm:mt-2 leading-tight">{{ __('VIP in Queue') }}</p>
            </div>
        </div>

        <!-- Book Appointment Link -->
        <div class="text-center mt-6 sm:mt-8">
            <a href="{{ route('customer.booking') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 sm:py-3 px-6 sm:px-8 text-sm sm:text-base rounded-lg transition duration-200 transform hover:scale-105">
                {{ __('Book New Appointment') }}
            </a>
        </div>
    </div>

    <script>
        let pollingInterval;

        async function loadQueueData() {
            try {
                const response = await fetch('/api/queue', {
                    headers: {
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();

                if (data.success) {
                    updateCurrentServing(data.data.current);
                    updateWaitingQueue(data.data.queues);
                    updateStats(data.data);
                }
            } catch (error) {
                console.error('Error loading queue:', error);
            }
        }

        function updateCurrentServing(current) {
            const servingNumber = document.getElementById('currentServing');
            const servingName = document.getElementById('currentName');

            if (current) {
                servingNumber.textContent = current.queue_number.toString().padStart(3, '0');
                servingName.textContent = current.appointment?.customer?.name || '{{ __('Customer') }}';
            } else {
                servingNumber.textContent = '---';
                servingName.textContent = '{{ __('No one is being served') }}';
            }
        }

        function updateWaitingQueue(queues) {
            const waitingList = queues.filter(q => q.status === 'Waiting');
            const nextQueue = waitingList.slice(0, 4);
            const restQueue = waitingList.slice(4);

            // Update next 4
            let nextHTML = '';
            nextQueue.forEach(queue => {
                nextHTML += `
                    <div class="text-center p-2 sm:p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg ${queue.priority > 0 ? 'border-2 border-yellow-400' : ''}">
                        <div class="text-2xl sm:text-3xl font-bold text-blue-600">${String(queue.queue_number).padStart(3, '0')}</div>
                        ${queue.priority > 0 ? '<div class="text-xs text-yellow-600 font-semibold mt-1">VIP</div>' : ''}
                        <div class="text-xs text-gray-600 mt-1 sm:mt-2">${queue.estimated_wait_time} {{ __('min') }}</div>
                    </div>
                `;
            });
            document.getElementById('nextQueue').innerHTML = nextHTML || '<div class="col-span-full text-center text-gray-400 py-4">{{ __('No upcoming') }}</div>';

            // Update waiting list
            let waitingHTML = '';
            restQueue.forEach((queue, index) => {
                waitingHTML += `
                    <div class="flex items-center justify-between p-3 sm:p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div class="flex items-center gap-2 sm:gap-4 {{ app()->getLocale() === 'ar' ? 'flex-row-reverse' : '' }}">
                            <div class="text-xl sm:text-2xl font-bold text-gray-700">${String(queue.queue_number).padStart(3, '0')}</div>
                            ${queue.priority > 0 ? '<span class="px-1.5 sm:px-2 py-0.5 sm:py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded">VIP</span>' : ''}
                        </div>
                        <div class="text-xs sm:text-sm text-gray-600 text-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}">
                            <span class="font-medium">${index + 5}</span> {{ __('in line') }}
                            <span class="hidden sm:inline">•</span>
                            <br class="sm:hidden">
                            <span class="font-medium">${queue.estimated_wait_time}</span> {{ __('min') }}
                        </div>
                    </div>
                `;
            });

            const waitingQueueElement = document.getElementById('waitingQueue');
            const emptyState = document.getElementById('emptyState');

            if (waitingHTML) {
                waitingQueueElement.innerHTML = waitingHTML;
                waitingQueueElement.classList.remove('hidden');
                emptyState.classList.add('hidden');
            } else {
                waitingQueueElement.classList.add('hidden');
                emptyState.classList.remove('hidden');
            }

            document.getElementById('totalWaiting').textContent = `${waitingList.length} {{ __('waiting') }}`;
        }

        function updateStats(data) {
            // These would come from API in real implementation
            document.getElementById('servedToday').textContent = data.queues.filter(q => q.status === 'Served').length;
            document.getElementById('avgWaitTime').textContent = Math.round(data.queues.reduce((sum, q) => sum + (q.estimated_wait_time || 0), 0) / data.queues.length || 0);
            document.getElementById('vipQueue').textContent = data.queues.filter(q => q.priority > 0 && q.status === 'Waiting').length;
        }

        // Start polling
        function startPolling() {
            loadQueueData(); // Load immediately
            pollingInterval = setInterval(loadQueueData, 10000); // Then every 10 seconds
        }

        // Stop polling when page is hidden
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                clearInterval(pollingInterval);
            } else {
                startPolling();
            }
        });

        // Start on load
        window.addEventListener('DOMContentLoaded', startPolling);

        // Cleanup on unload
        window.addEventListener('beforeunload', () => {
            clearInterval(pollingInterval);
        });

        // Change Language Function
        function changeLanguage(lang) {
            window.location.href = '/change-language/' + lang;
        }
    </script>
</body>
</html>
