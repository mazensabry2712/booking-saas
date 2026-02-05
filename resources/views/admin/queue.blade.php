<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Manage Queue') }} - {{ tenant()->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    @include('partials.admin-nav')

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
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم الدور</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">اسم العميل</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم الهاتف</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">البريد الإلكتروني</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">الموظف</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">الخدمة</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">الأولوية</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">ملاحظات</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($queues as $queue)
                                <tr class="@if($queue->is_vip) bg-yellow-50 @endif">
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                        #{{ $queue->queue_number }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $queue->appointment?->customer?->name ?? 'غير محدد' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $queue->appointment?->customer?->phone ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $queue->appointment?->customer?->email ?? '-' }}
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $queue->appointment?->staff?->name ?? '-' }}
                                        @if($queue->appointment?->staff?->specialization_ar || $queue->appointment?->staff?->specialization)
                                            <span class="block text-xs text-gray-500">{{ $queue->appointment?->staff?->specialization_ar ?? $queue->appointment?->staff?->specialization }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($queue->appointment?->service)
                                            {{ app()->getLocale() === 'ar' && $queue->appointment->service->name_ar ? $queue->appointment->service->name_ar : $queue->appointment->service->name }}
                                            <span class="block text-xs text-blue-600">{{ $queue->appointment->service->duration }} {{ __('min') }}</span>
                                        @elseif($queue->appointment?->service_type)
                                            {{ $queue->appointment->service_type }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm">
                                        @if($queue->is_vip)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                ⭐ VIP
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">عادي</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-500 max-w-xs">
                                        @if($queue->notes)
                                            <span class="block truncate" title="{{ $queue->notes }}">{{ \Illuminate\Support\Str::limit($queue->notes, 30) }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
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
                                            @elseif($queue->status === 'cancelled') تم التخطي
                                            @else {{ $queue->status }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium space-x-2 space-x-reverse">
                                        <button onclick="viewQueue({{ $queue->id }})" class="text-gray-600 hover:text-gray-900">عرض</button>
                                        @if($queue->status === 'waiting')
                                            <button onclick="serveQueue({{ $queue->id }})" class="text-green-600 hover:text-green-900">خدمة</button>
                                            <button onclick="setPriority({{ $queue->id }}, {{ $queue->is_vip ? 0 : 1 }})" class="text-yellow-600 hover:text-yellow-900">
                                                {{ $queue->is_vip ? 'عادي' : 'VIP' }}
                                            </button>
                                        @endif
                                        @if($queue->status === 'serving')
                                            <button onclick="completeQueue({{ $queue->id }})" class="text-blue-600 hover:text-blue-900">إنهاء</button>
                                            <button onclick="returnToWaiting({{ $queue->id }})" class="text-orange-600 hover:text-orange-900">إرجاع</button>
                                        @endif
                                        <button onclick="editQueue({{ $queue->id }})" class="text-indigo-600 hover:text-indigo-900">تعديل</button>
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

                <!-- بيانات العميل -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">اسم العميل <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف <span class="text-red-500">*</span></label>
                        <input type="tel" name="customer_phone" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                    <input type="email" name="customer_email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- اختيار التخصص والموظف والخدمة -->
                <div class="border-t pt-4 mt-4">
                    <h4 class="text-sm font-semibold text-gray-800 mb-3">اختيار الموظف والخدمة</h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- التخصص -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">التخصص <span class="text-red-500">*</span></label>
                            <select name="specialization" id="specializationSelect" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">اختر التخصص</option>
                                @php
                                    $specializations = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'Staff'))
                                        ->whereNotNull('specialization')
                                        ->where('specialization', '!=', '')
                                        ->select('specialization', 'specialization_ar')
                                        ->distinct()
                                        ->get();
                                @endphp
                                @foreach($specializations as $spec)
                                    <option value="{{ $spec->specialization }}">{{ $spec->specialization_ar ?: $spec->specialization }}</option>
                                @endforeach
                            </select>
                            @if($specializations->isEmpty())
                                <p class="text-xs text-orange-600 mt-1">⚠️ لا توجد تخصصات - يرجى إضافة تخصصات للموظفين من <a href="{{ route('admin.staff') }}" class="underline">صفحة الموظفين</a></p>
                            @endif
                        </div>

                        <!-- الموظف -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الموظف <span class="text-red-500">*</span></label>
                            <select name="staff_id" id="staffSelect" required disabled class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100">
                                <option value="">اختر التخصص أولاً</option>
                            </select>
                        </div>

                        <!-- الخدمة -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الخدمة <span class="text-red-500">*</span></label>
                            <select name="service_id" id="serviceSelect" required disabled class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100">
                                <option value="">اختر الموظف أولاً</option>
                            </select>
                        </div>
                    </div>

                    <!-- معلومات الخدمة المختارة -->
                    <div id="serviceInfo" class="hidden mt-3 p-3 bg-blue-50 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <span class="font-medium">مدة الخدمة:</span>
                            <span id="serviceDuration">-</span> دقيقة
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="أي ملاحظات إضافية عن العميل..."></textarea>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_priority" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="mr-2 text-sm font-medium text-gray-700">⭐ عميل له أولوية (VIP)</span>
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

    <!-- Edit Queue Modal -->
    <div id="editQueueModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">تعديل بيانات العميل</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="editQueueForm" class="space-y-4">
                @csrf
                <input type="hidden" name="queue_id" id="edit_queue_id">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">اسم العميل <span class="text-red-500">*</span></label>
                    <input type="text" name="customer_name" id="edit_customer_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف <span class="text-red-500">*</span></label>
                    <input type="tel" name="customer_phone" id="edit_customer_phone" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                    <input type="email" name="customer_email" id="edit_customer_email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                    <textarea name="notes" id="edit_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="أي ملاحظات إضافية عن العميل..."></textarea>
                </div>

                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_vip" id="edit_is_vip" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="mr-2 text-sm font-medium text-gray-700">⭐ عميل له أولوية (VIP)</span>
                    </label>
                </div>

                <div id="editErrorMessage" class="hidden bg-red-50 border border-red-200 text-red-800 rounded-lg p-3"></div>
                <div id="editSuccessMessage" class="hidden bg-green-50 border border-green-200 text-green-800 rounded-lg p-3"></div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                        حفظ التعديلات
                    </button>
                    <button type="button" onclick="closeEditModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Queue Modal -->
    <div id="viewQueueModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">تفاصيل العميل</h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div id="viewQueueContent" class="space-y-4">
                <!-- Queue Number & Status -->
                <div class="flex items-center justify-between bg-gray-50 p-4 rounded-lg">
                    <div class="text-center">
                        <span class="text-3xl font-bold text-blue-600" id="view_queue_number">#1</span>
                        <p class="text-sm text-gray-500">رقم الدور</p>
                    </div>
                    <div id="view_status_badge"></div>
                </div>

                <!-- Customer Info -->
                <div class="bg-white border rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 ml-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        بيانات العميل
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500">الاسم</label>
                            <p class="font-medium text-gray-900" id="view_customer_name">-</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">رقم الهاتف</label>
                            <p class="font-medium text-gray-900" id="view_customer_phone">-</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">البريد الإلكتروني</label>
                            <p class="font-medium text-gray-900" id="view_customer_email">-</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">الأولوية</label>
                            <p id="view_vip_status">-</p>
                        </div>
                    </div>
                    <!-- Notes Section -->
                    <div class="mt-3 pt-3 border-t" id="view_notes_section">
                        <label class="text-xs text-gray-500">ملاحظات</label>
                        <p class="font-medium text-gray-900 whitespace-pre-wrap" id="view_notes">-</p>
                    </div>
                </div>

                <!-- Staff & Service Info -->
                <div class="bg-white border rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 ml-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        الموظف والخدمة
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500">الموظف</label>
                            <p class="font-medium text-gray-900" id="view_staff_name">-</p>
                            <p class="text-sm text-gray-500" id="view_staff_specialization"></p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">الخدمة</label>
                            <p class="font-medium text-gray-900" id="view_service_name">-</p>
                            <p class="text-sm text-blue-600" id="view_service_duration"></p>
                        </div>
                    </div>
                </div>

                <!-- Time Info -->
                <div class="bg-white border rounded-lg p-4">
                    <h4 class="font-semibold text-gray-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 ml-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        معلومات الوقت
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500">تاريخ الإضافة</label>
                            <p class="font-medium text-gray-900" id="view_created_at">-</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">آخر تحديث</label>
                            <p class="font-medium text-gray-900" id="view_updated_at">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex gap-3">
                <button onclick="editQueueFromView()" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    تعديل
                </button>
                <button onclick="closeViewModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">
                    إغلاق
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentViewQueueId = null;

        function openAddModal() {
            document.getElementById('addQueueModal').classList.remove('hidden');
            resetForm();
        }

        function closeAddModal() {
            document.getElementById('addQueueModal').classList.add('hidden');;
            document.getElementById('addQueueForm').reset();
            document.getElementById('addErrorMessage').classList.add('hidden');
            document.getElementById('addSuccessMessage').classList.add('hidden');
            resetForm();
        }

        function resetForm() {
            const staffSelect = document.getElementById('staffSelect');
            const serviceSelect = document.getElementById('serviceSelect');
            const serviceInfo = document.getElementById('serviceInfo');

            document.getElementById('specializationSelect').value = '';
            staffSelect.innerHTML = '<option value="">اختر التخصص أولاً</option>';
            staffSelect.disabled = true;
            serviceSelect.innerHTML = '<option value="">اختر الموظف أولاً</option>';
            serviceSelect.disabled = true;
            serviceInfo.classList.add('hidden');
        }

        // When specialization changes → Load staff from API
        document.getElementById('specializationSelect').addEventListener('change', async function() {
            const specialization = this.value;
            const staffSelect = document.getElementById('staffSelect');
            const serviceSelect = document.getElementById('serviceSelect');
            const serviceInfo = document.getElementById('serviceInfo');

            // Reset dependent fields
            serviceSelect.innerHTML = '<option value="">اختر الموظف أولاً</option>';
            serviceSelect.disabled = true;
            serviceInfo.classList.add('hidden');

            if (!specialization) {
                staffSelect.innerHTML = '<option value="">اختر التخصص أولاً</option>';
                staffSelect.disabled = true;
                return;
            }

            staffSelect.innerHTML = '<option value="">جاري التحميل...</option>';
            staffSelect.disabled = true;

            try {
                const response = await fetch(`/admin/api/staff/by-specialization/${encodeURIComponent(specialization)}`);
                const result = await response.json();

                if (result.success && result.data.length > 0) {
                    staffSelect.innerHTML = '<option value="">اختر الموظف</option>';
                    result.data.forEach(staff => {
                        const option = document.createElement('option');
                        option.value = staff.id;
                        option.textContent = staff.name;
                        staffSelect.appendChild(option);
                    });
                    staffSelect.disabled = false;
                } else {
                    staffSelect.innerHTML = '<option value="">لا يوجد موظفين في هذا التخصص</option>';
                    staffSelect.disabled = true;
                }
            } catch (error) {
                console.error('Error loading staff:', error);
                staffSelect.innerHTML = '<option value="">خطأ في التحميل</option>';
                staffSelect.disabled = true;
            }
        });

        // When staff changes → Load their services from API
        document.getElementById('staffSelect').addEventListener('change', async function() {
            const staffId = this.value;
            const serviceSelect = document.getElementById('serviceSelect');
            const serviceInfo = document.getElementById('serviceInfo');

            if (!staffId) {
                serviceSelect.innerHTML = '<option value="">اختر الموظف أولاً</option>';
                serviceSelect.disabled = true;
                serviceInfo.classList.add('hidden');
                return;
            }

            serviceSelect.innerHTML = '<option value="">جاري التحميل...</option>';
            serviceSelect.disabled = true;

            try {
                const response = await fetch(`/admin/api/staff/${staffId}/services`);
                const result = await response.json();

                if (result.success && result.data.length > 0) {
                    serviceSelect.innerHTML = '<option value="">اختر الخدمة</option>';
                    result.data.forEach(service => {
                        const option = document.createElement('option');
                        option.value = service.id;
                        option.textContent = `${service.name_ar || service.name} (${service.duration} دقيقة)`;
                        option.dataset.duration = service.duration;
                        serviceSelect.appendChild(option);
                    });
                    serviceSelect.disabled = false;
                } else {
                    serviceSelect.innerHTML = '<option value="">لا توجد خدمات لهذا الموظف</option>';
                }
            } catch (error) {
                serviceSelect.innerHTML = '<option value="">خطأ في التحميل</option>';
            }
        });

        // When service changes, show duration
        document.getElementById('serviceSelect').addEventListener('change', function() {
            const serviceInfo = document.getElementById('serviceInfo');
            const selectedOption = this.options[this.selectedIndex];

            if (this.value && selectedOption.dataset.duration) {
                document.getElementById('serviceDuration').textContent = selectedOption.dataset.duration;
                serviceInfo.classList.remove('hidden');
            } else {
                serviceInfo.classList.add('hidden');
            }
        });

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

        // Return to waiting
        async function returnToWaiting(id) {
            if (!confirm('هل تريد إرجاع هذا العميل لقائمة الانتظار؟')) {
                return;
            }

            try {
                const response = await fetch(`/admin/api/queue/${id}/return-waiting`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    alert('✓ تم إرجاع العميل لقائمة الانتظار');
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

        document.getElementById('editQueueModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        // Edit Queue Functions
        function closeEditModal() {
            document.getElementById('editQueueModal').classList.add('hidden');
            document.getElementById('editQueueForm').reset();
            document.getElementById('editErrorMessage').classList.add('hidden');
            document.getElementById('editSuccessMessage').classList.add('hidden');
        }

        async function editQueue(id) {
            try {
                const response = await fetch(`/admin/api/queue/${id}`);
                const result = await response.json();

                if (result.success) {
                    const queue = result.data;
                    document.getElementById('edit_queue_id').value = queue.id;
                    document.getElementById('edit_customer_name').value = queue.appointment?.customer?.name || '';
                    document.getElementById('edit_customer_phone').value = queue.appointment?.customer?.phone || '';
                    document.getElementById('edit_customer_email').value = queue.appointment?.customer?.email || '';
                    document.getElementById('edit_notes').value = queue.notes || '';
                    document.getElementById('edit_is_vip').checked = queue.is_vip;
                    document.getElementById('editQueueModal').classList.remove('hidden');
                } else {
                    alert('خطأ في تحميل البيانات');
                }
            } catch (error) {
                alert('حدث خطأ في الاتصال');
            }
        }

        document.getElementById('editQueueForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const queueId = document.getElementById('edit_queue_id').value;
            const errorDiv = document.getElementById('editErrorMessage');
            const successDiv = document.getElementById('editSuccessMessage');

            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');

            const data = {
                customer_name: document.getElementById('edit_customer_name').value,
                customer_phone: document.getElementById('edit_customer_phone').value,
                customer_email: document.getElementById('edit_customer_email').value,
                notes: document.getElementById('edit_notes').value,
                is_vip: document.getElementById('edit_is_vip').checked ? 1 : 0
            };

            try {
                const response = await fetch(`/admin/api/queue/${queueId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    successDiv.textContent = '✓ تم حفظ التعديلات بنجاح';
                    successDiv.classList.remove('hidden');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    errorDiv.textContent = '✕ ' + (result.message || 'حدث خطأ');
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorDiv.textContent = '✕ حدث خطأ في الاتصال';
                errorDiv.classList.remove('hidden');
            }
        });

        // View Queue Functions
        function closeViewModal() {
            document.getElementById('viewQueueModal').classList.add('hidden');
            currentViewQueueId = null;
        }

        document.getElementById('viewQueueModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeViewModal();
            }
        });

        async function viewQueue(id) {
            currentViewQueueId = id;
            try {
                const response = await fetch(`/admin/api/queue/${id}`);
                const result = await response.json();

                if (result.success) {
                    const queue = result.data;

                    // Queue number
                    document.getElementById('view_queue_number').textContent = '#' + queue.queue_number;

                    // Status badge
                    let statusHtml = '';
                    if (queue.status === 'waiting') {
                        statusHtml = '<span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">في الانتظار</span>';
                    } else if (queue.status === 'serving') {
                        statusHtml = '<span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">يتم الخدمة</span>';
                    } else if (queue.status === 'completed') {
                        statusHtml = '<span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">مكتمل</span>';
                    } else {
                        statusHtml = '<span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">ملغي</span>';
                    }
                    document.getElementById('view_status_badge').innerHTML = statusHtml;

                    // Customer info
                    document.getElementById('view_customer_name').textContent = queue.appointment?.customer?.name || '-';
                    document.getElementById('view_customer_phone').textContent = queue.appointment?.customer?.phone || '-';
                    document.getElementById('view_customer_email').textContent = queue.appointment?.customer?.email || '-';

                    // VIP status
                    if (queue.is_vip) {
                        document.getElementById('view_vip_status').innerHTML = '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">⭐ VIP</span>';
                    } else {
                        document.getElementById('view_vip_status').innerHTML = '<span class="text-gray-500">عادي</span>';
                    }

                    // Staff info
                    document.getElementById('view_staff_name').textContent = queue.appointment?.staff?.name || '-';
                    document.getElementById('view_staff_specialization').textContent = queue.appointment?.staff?.specialization_ar || queue.appointment?.staff?.specialization || '';

                    // Service info
                    if (queue.appointment?.service) {
                        document.getElementById('view_service_name').textContent = queue.appointment.service.name_ar || queue.appointment.service.name;
                        document.getElementById('view_service_duration').textContent = queue.appointment.service.duration + ' دقيقة';
                    } else {
                        document.getElementById('view_service_name').textContent = queue.appointment?.service_type || '-';
                        document.getElementById('view_service_duration').textContent = '';
                    }

                    // Time info
                    document.getElementById('view_created_at').textContent = new Date(queue.created_at).toLocaleString('ar-EG');
                    document.getElementById('view_updated_at').textContent = new Date(queue.updated_at).toLocaleString('ar-EG');

                    // Notes
                    const notesSection = document.getElementById('view_notes_section');
                    const notesElement = document.getElementById('view_notes');
                    if (queue.notes && queue.notes.trim() !== '') {
                        notesElement.textContent = queue.notes;
                        notesSection.classList.remove('hidden');
                    } else {
                        notesElement.textContent = 'لا توجد ملاحظات';
                        notesSection.classList.remove('hidden');
                    }

                    document.getElementById('viewQueueModal').classList.remove('hidden');
                } else {
                    alert('خطأ في تحميل البيانات');
                }
            } catch (error) {
                alert('حدث خطأ في الاتصال');
            }
        }

        function editQueueFromView() {
            closeViewModal();
            if (currentViewQueueId) {
                editQueue(currentViewQueueId);
            }
        }
    </script>
</body>
</html>
