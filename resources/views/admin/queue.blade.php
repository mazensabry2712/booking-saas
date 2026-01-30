<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>إدارة قائمة الانتظار - {{ tenant()->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-6 space-x-reverse">
                    <a href="/admin/dashboard" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-xl font-bold text-gray-900">{{ tenant()->name }}</h1>
                </div>
                <div class="flex items-center space-x-4 space-x-reverse">
                    <span class="text-gray-700">{{ auth()->user()->name }}</span>
                    <span class="text-sm text-gray-500">Staff Member</span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-800">تسجيل خروج</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900">إدارة قائمة الانتظار</h2>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="mb-6 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">قائمة الانتظار الحالية</h3>
                <div class="space-x-2 space-x-reverse">
                    <button onclick="callNext()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        استدعاء التالي
                    </button>
                    <button onclick="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        إضافة عميل
                    </button>
                </div>
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

            @if($queues->count() > 0)
                <!-- Queue Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم الدور</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">اسم العميل</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم الهاتف</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">نوع الخدمة</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الأولوية</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($queues as $queue)
                                <tr class="@if($queue->priority) bg-yellow-50 @endif">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                        #{{ $queue->queue_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $queue->appointment?->customer?->name ?? 'غير محدد' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $queue->appointment?->customer?->phone ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $queue->appointment?->service_type ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($queue->priority)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                ⭐ أولوية
                                            </span>
                                        @else
                                            <span class="text-gray-500">عادي</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($queue->status === 'waiting') bg-blue-100 text-blue-800
                                            @elseif($queue->status === 'serving') bg-green-100 text-green-800
                                            @elseif($queue->status === 'completed') bg-gray-100 text-gray-800
                                            @elseif($queue->status === 'cancelled') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            @if($queue->status === 'waiting') في الانتظار
                                            @elseif($queue->status === 'serving') يتم الخدمة
                                            @elseif($queue->status === 'completed') مكتمل
                                            @elseif($queue->status === 'cancelled') ملغي
                                            @else {{ $queue->status }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($queue->status === 'waiting')
                                            <button onclick="serveQueue({{ $queue->id }})" class="text-green-600 hover:text-green-900 ml-3">خدمة</button>
                                            <button onclick="setPriority({{ $queue->id }}, {{ $queue->priority ? 0 : 1 }})" class="text-yellow-600 hover:text-yellow-900 ml-3">
                                                {{ $queue->priority ? 'إلغاء الأولوية' : 'أولوية' }}
                                            </button>
                                        @endif
                                        @if($queue->status === 'serving')
                                            <button onclick="completeQueue({{ $queue->id }})" class="text-blue-600 hover:text-blue-900 ml-3">إنهاء</button>
                                        @endif
                                        <button onclick="removeQueue({{ $queue->id }})" class="text-red-600 hover:text-red-900">حذف</button>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">لا يوجد أحد في قائمة الانتظار</h3>
                    <p class="mt-1 text-sm text-gray-500">ابدأ بإضافة عملاء إلى القائمة</p>
                </div>
            @endif
        </div>
    </main>

    <!-- Add to Queue Modal -->
    <div id="addQueueModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">إضافة عميل للقائمة</h3>
                <button onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="addQueueForm" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">اسم العميل</label>
                    <input type="text" name="customer_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف</label>
                    <input type="tel" name="customer_phone" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                    <input type="email" name="customer_email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نوع الخدمة</label>
                    <input type="text" name="service_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_priority" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="mr-2 text-sm font-medium text-gray-700">عميل له أولوية (VIP)</span>
                    </label>
                </div>

                <div id="addErrorMessage" class="hidden bg-red-50 border border-red-200 text-red-800 rounded-lg p-3"></div>
                <div id="addSuccessMessage" class="hidden bg-green-50 border border-green-200 text-green-800 rounded-lg p-3"></div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        إضافة للقائمة
                    </button>
                    <button type="button" onclick="closeAddModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addQueueModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addQueueModal').classList.add('hidden');
            document.getElementById('addQueueForm').reset();
            document.getElementById('addErrorMessage').classList.add('hidden');
            document.getElementById('addSuccessMessage').classList.add('hidden');
        }

        // Add to queue
        document.getElementById('addQueueForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);

            const errorDiv = document.getElementById('addErrorMessage');
            const successDiv = document.getElementById('addSuccessMessage');

            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');

            try {
                const response = await fetch('/admin/api/queue/add', {
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
                    successDiv.textContent = '✓ تم إضافة العميل للقائمة بنجاح!';
                    successDiv.classList.remove('hidden');

                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    errorDiv.textContent = '✕ ' + (result.message || 'حدث خطأ أثناء الحفظ');
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = '✕ حدث خطأ! حاول مرة أخرى';
                errorDiv.classList.remove('hidden');
            }
        });

        // Call next in queue
        async function callNext() {
            try {
                const response = await fetch('/admin/api/queue/call-next', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    alert('✓ ' + result.message);
                    window.location.reload();
                } else {
                    alert('✕ ' + (result.message || 'حدث خطأ'));
                }
            } catch (error) {
                alert('✕ حدث خطأ! حاول مرة أخرى');
            }
        }

        // Serve queue item
        async function serveQueue(id) {
            try {
                const response = await fetch(`/admin/api/queue/${id}/serve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    window.location.reload();
                } else {
                    alert('✕ ' + (result.message || 'حدث خطأ'));
                }
            } catch (error) {
                alert('✕ حدث خطأ! حاول مرة أخرى');
            }
        }

        // Complete queue item
        async function completeQueue(id) {
            try {
                const response = await fetch(`/admin/api/queue/${id}/complete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    alert('✓ تم إنهاء الخدمة بنجاح!');
                    window.location.reload();
                } else {
                    alert('✕ ' + (result.message || 'حدث خطأ'));
                }
            } catch (error) {
                alert('✕ حدث خطأ! حاول مرة أخرى');
            }
        }

        // Set priority
        async function setPriority(id, priority) {
            try {
                const response = await fetch(`/admin/api/queue/${id}/priority`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ priority: priority })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    window.location.reload();
                } else {
                    alert('✕ ' + (result.message || 'حدث خطأ'));
                }
            } catch (error) {
                alert('✕ حدث خطأ! حاول مرة أخرى');
            }
        }

        // Remove from queue
        async function removeQueue(id) {
            if (!confirm('هل أنت متأكد من حذف هذا العميل من القائمة؟')) {
                return;
            }

            try {
                const response = await fetch(`/admin/api/queue/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    window.location.reload();
                } else {
                    alert('✕ ' + (result.message || 'حدث خطأ'));
                }
            } catch (error) {
                alert('✕ حدث خطأ! حاول مرة أخرى');
            }
        }

        // Close modal on outside click
        document.getElementById('addQueueModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddModal();
            }
        });
    </script>
</body>
</html>
