<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('My Queue Status') }} - {{ tenant()->name }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .pulse-slow {
            animation: pulse-slow 2s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">{{ tenant()->name }}</h1>
            <p class="text-gray-600">{{ __('Check Your Queue Status') }}</p>
        </div>

        <!-- Queue Status Card -->
        <div class="max-w-2xl mx-auto">

            @if(request()->has('queue_number'))
                <!-- Queue Status Display -->
                <div id="queueStatusCard" class="bg-white rounded-2xl shadow-xl p-8 mb-6">
                    <div class="text-center">
                        <div class="mb-6">
                            <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-4">
                                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ __('Your Queue Number') }}</h2>
                            <div class="text-6xl font-bold text-blue-600 mb-4" id="queueNumber">
                                <span class="pulse-slow">--</span>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-6 space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">{{ __('Status') }}:</span>
                                <span id="queueStatus" class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full font-semibold">
                                    {{ __('Waiting') }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">{{ __('People Ahead') }}:</span>
                                <span id="peopleAhead" class="text-2xl font-bold text-gray-800">--</span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">{{ __('Estimated Wait Time') }}:</span>
                                <span id="estimatedTime" class="text-xl font-semibold text-gray-800">--</span>
                            </div>
                        </div>

                        <div id="currentlyServingCard" class="mt-6 p-4 bg-green-50 rounded-lg border border-green-200">
                            <p class="text-sm text-gray-600 mb-1">{{ __('Currently Serving') }}</p>
                            <p class="text-3xl font-bold text-green-600" id="currentlyServing">--</p>
                        </div>
                    </div>

                    <!-- Alert Message -->
                    <div id="alertMessage" class="hidden mt-6 p-4 rounded-lg">
                        <p class="font-semibold"></p>
                    </div>
                </div>
            @else
                <!-- Search Form -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                        {{ __('Check Your Queue Status') }}
                    </h2>

                    <form id="queueSearchForm" class="space-y-6">
                        @csrf
                        <div>
                            <label for="queue_number_input" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('Enter Your Queue Number') }}
                            </label>
                            <input type="number"
                                id="queue_number_input"
                                name="queue_number"
                                required
                                class="w-full px-4 py-3 text-center text-2xl font-bold border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="000">
                        </div>

                        <button type="submit"
                            class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition duration-200 shadow-lg hover:shadow-xl">
                            {{ __('Check Status') }}
                        </button>
                    </form>
                </div>
            @endif

            <!-- Back Button -->
            <div class="text-center mt-6">
                <a href="{{ route('customer.booking') }}"
                    class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('Back to Booking') }}
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if(request()->has('queue_number'))
                const queueNumber = {{ request('queue_number') }};
                fetchQueueStatus(queueNumber);

                // Auto-refresh every 10 seconds
                setInterval(() => fetchQueueStatus(queueNumber), 10000);
            @else
                // Handle search form
                document.getElementById('queueSearchForm')?.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const queueNumber = document.getElementById('queue_number_input').value;
                    window.location.href = `{{ route('customer.my-queue') }}?queue_number=${queueNumber}`;
                });
            @endif
        });

        function fetchQueueStatus(queueNumber) {
            fetch(`/api/queue/status/${queueNumber}`, {
                headers: {
                    'X-Tenant': '{{ tenant()->id }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateQueueDisplay(data.data);
                } else {
                    showError(data.message || '{{ __("Queue not found") }}');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('{{ __("Error fetching queue status") }}');
            });
        }

        function updateQueueDisplay(queueData) {
            // Update queue number
            document.getElementById('queueNumber').textContent = queueData.queue_number || '--';

            // Update status
            const statusEl = document.getElementById('queueStatus');
            const statusMap = {
                'Waiting': { text: '{{ __("Waiting") }}', class: 'bg-yellow-100 text-yellow-800' },
                'Serving': { text: '{{ __("Your Turn") }}', class: 'bg-green-100 text-green-800' },
                'Served': { text: '{{ __("Completed") }}', class: 'bg-gray-100 text-gray-800' },
                'Skipped': { text: '{{ __("Skipped") }}', class: 'bg-red-100 text-red-800' }
            };

            const status = statusMap[queueData.status] || statusMap['Waiting'];
            statusEl.textContent = status.text;
            statusEl.className = `px-4 py-2 rounded-full font-semibold ${status.class}`;

            // Update people ahead
            document.getElementById('peopleAhead').textContent = queueData.people_ahead || 0;

            // Update estimated time
            const estimatedMinutes = queueData.estimated_wait_time || 0;
            document.getElementById('estimatedTime').textContent =
                estimatedMinutes > 0 ? `~${estimatedMinutes} {{ __("minutes") }}` : '{{ __("Soon") }}';

            // Update currently serving
            document.getElementById('currentlyServing').textContent =
                queueData.currently_serving || '--';

            // Show alert if it's your turn
            if (queueData.status === 'Serving') {
                showAlert('{{ __("It\'s your turn! Please proceed to the counter.") }}', 'success');
                // Play notification sound (optional)
                playNotificationSound();
            } else if (queueData.people_ahead <= 1 && queueData.status === 'Waiting') {
                showAlert('{{ __("You\'re next! Please be ready.") }}', 'warning');
            }
        }

        function showAlert(message, type = 'info') {
            const alertEl = document.getElementById('alertMessage');
            const colorMap = {
                'success': 'bg-green-100 border-green-500 text-green-800',
                'warning': 'bg-yellow-100 border-yellow-500 text-yellow-800',
                'error': 'bg-red-100 border-red-500 text-red-800',
                'info': 'bg-blue-100 border-blue-500 text-blue-800'
            };

            alertEl.className = `mt-6 p-4 rounded-lg border-l-4 ${colorMap[type] || colorMap['info']}`;
            alertEl.querySelector('p').textContent = message;
            alertEl.classList.remove('hidden');
        }

        function showError(message) {
            showAlert(message, 'error');
        }

        function playNotificationSound() {
            // Optional: Add notification sound
            try {
                const audio = new Audio('/sounds/notification.mp3');
                audio.play().catch(e => console.log('Audio play failed:', e));
            } catch (e) {
                console.log('Audio not supported');
            }
        }
    </script>
</body>
</html>
