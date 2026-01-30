# Multi-Tenant Booking SaaS

نظام حجز مواعيد متعدد المستأجرين (Multi-tenant) مبني على Laravel 11 + Stancl/Tenancy.

## المميزات

### ✨ المزايا الأساسية
- 🏢 Multi-tenancy: كل عميل (Clinic, Salon, etc.) له database منفصلة
- 📅 Appointments: حجز المواعيد مع طاقم العمل
- 👥 Queue Management: إدارة طوابير الانتظار في الوقت الفعلي
- 🔔 Notifications: إشعارات Email للعملاء والموظفين
- 📊 Reports: تقارير إحصائية مع تصدير Excel/PDF
- 💰 Invoicing: فواتير PDF مع معلومات تفصيلية
- 🌍 Multi-language: دعم العربية والإنجليزية مع RTL

### 🎨 Frontend Features
- 📱 Responsive Design: واجهة Tailwind CSS تعمل على جميع الأجهزة
- 🖥️ Admin Dashboard: لوحة تحكم مع إحصائيات حية وتقارير
- 📝 Customer Booking: صفحة حجز عامة للعملاء
- 🔴 Live Queue Display: شاشة عرض الطوابير مع تحديث تلقائي كل 10 ثوانٍ

## المتطلبات

- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js & NPM

## التثبيت

### 1. Install dependencies
```bash
composer install
npm install
```

### 2. Setup environment
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure database (في `.env`)
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=booking_saas
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Run migrations with demo data
```bash
php artisan migrate:fresh --seed
```

هذا سينشئ:
- Central database مع جدول tenants و domains
- Tenant demo: **demo.localhost**
- مستخدمين تجريبيين:
  - Admin: `admin@demo.localhost` / `password123`
  - Staff: `staff@demo.localhost` / `password123`

### 5. Build frontend assets
```bash
npm run dev
```

### 6. Configure hosts file

أضف هذا السطر إلى `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1   demo.localhost
```

### 7. Start server (Herd/Valet/Sail)

إذا كنت تستخدم Laravel Herd، الموقع جاهز على:
- http://demo.localhost

## الاستخدام

### 🔐 تسجيل الدخول (API)

```bash
POST /api/login
{
  "email": "admin@demo.localhost",
  "password": "password123"
}
```

### 📱 الصفحات العامة (Public Pages)

1. **الصفحة الرئيسية**: http://demo.localhost
2. **حجز موعد**: http://demo.localhost/book
3. **شاشة الطوابير**: http://demo.localhost/queue
4. **حالة طابوري**: http://demo.localhost/my-queue

### 🔒 لوحة التحكم (Admin Panel)

بعد تسجيل الدخول عبر API:

1. **Dashboard**: http://demo.localhost/admin/dashboard
2. **المواعيد**: http://demo.localhost/admin/appointments
3. **الطوابير**: http://demo.localhost/admin/queue
4. **التقارير**: http://demo.localhost/admin/reports

## API Endpoints

### Authentication
```
POST   /api/login              Login
POST   /api/register           Register new user
POST   /api/logout             Logout
```

### Appointments
```
GET    /api/appointments        List all appointments
POST   /api/appointments        Create appointment
GET    /api/appointments/{id}   View appointment
PUT    /api/appointments/{id}   Update appointment
DELETE /api/appointments/{id}   Delete appointment
```

### Queue Management
```
GET    /api/queue               Get current queue status
POST   /api/queue/{id}/call     Call next in queue
POST   /api/queue/{id}/serve    Mark as served
POST   /api/queue/{id}/skip     Skip patient
```

### Reports
```
GET    /api/reports/dashboard   Dashboard stats (today/week/month)
GET    /api/reports/appointments Export appointments Excel
GET    /api/reports/staff       Staff performance
```

### Invoices
```
GET    /api/invoices/{id}       View invoice
GET    /api/invoices/{id}/pdf   Download PDF
POST   /api/invoices            Create invoice
```

### Super Admin (Central DB)
```
GET    /api/super-admin/tenants        List tenants
POST   /api/super-admin/tenants        Create tenant
PUT    /api/super-admin/tenants/{id}   Update tenant
POST   /api/super-admin/tenants/{id}/toggle-status Toggle active/inactive
```

## إضافة Tenant جديد

### عبر API:
```bash
POST /api/super-admin/tenants
{
  "name": "New Clinic",
  "domain": "newclinic.localhost",
  "admin_name": "Admin Name",
  "admin_email": "admin@newclinic.localhost",
  "admin_password": "password123"
}
```

### يدوياً (CLI):
```bash
php artisan tinker
```

```php
$tenant = \App\Models\Tenant::create(['id' => \Str::uuid()]);
$tenant->name = 'New Clinic';
$tenant->active = true;
$tenant->save();

$tenant->domains()->create(['domain' => 'newclinic.localhost']);

// Run migrations for tenant database
$tenant->run(function() {
    \Artisan::call('migrate', ['--path' => 'database/migrations/tenant', '--force' => true]);
});
```

## تطوير Frontend

الملفات الرئيسية:
- **Layouts**: `resources/views/layouts/app.blade.php`
- **Components**: `resources/views/layouts/navigation.blade.php`
- **Admin**: `resources/views/admin/dashboard.blade.php`
- **Customer**: `resources/views/customer/booking.blade.php`
- **Queue**: `resources/views/queue/dashboard.blade.php`

### Live Updates (JavaScript)
شاشة الطوابير تستخدم polling كل 10 ثوانٍ:

```javascript
async function loadQueue() {
    const response = await fetch('/api/queue');
    const data = await response.json();
    updateUI(data);
}

setInterval(loadQueue, 10000); // Update every 10 seconds
```

## الترجمة

### إضافة ترجمات جديدة:

1. **Arabic**: `resources/lang/ar/messages.php`
2. **English**: `resources/lang/en/messages.php`

```php
// resources/lang/ar/messages.php
return [
    'welcome' => 'مرحباً',
    'book_appointment' => 'حجز موعد',
];
```

### استخدام في Blade:
```blade
{{ __('messages.welcome') }}
```

## التخزين (Storage)

- **Uploads**: `storage/app/public/`
- **Logs**: `storage/logs/laravel.log`
- **PDFs**: Generated on-the-fly

## الأمان (Security)

- ✅ Laravel Sanctum: Token-based authentication
- ✅ Database isolation: كل tenant له database منفصلة
- ✅ CSRF Protection: تحقق من CSRF tokens
- ✅ Input validation: تحقق من جميع المدخلات

## الإنتاج (Production)

### Build assets:
```bash
npm run build
```

### Optimize Laravel:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Setup Queue Worker:
```bash
php artisan queue:work --daemon
```

---

Made with ❤️ using Laravel 11 + Tailwind CSS
