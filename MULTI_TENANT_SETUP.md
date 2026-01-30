# Multi-Tenant Setup - Documentation

## 📋 نظرة عامة

تم إعداد نظام Multi-Tenant كامل باستخدام `stancl/tenancy` مع الميزات التالية:

---

## ✅ 1. Tenant-Aware Models

جميع الـ Models أصبحت Tenant-aware:

### Models المُعدّلة:
- ✅ **User** - مع `BelongsToTenant` و `HasRoles`
- ✅ **Appointment** - مع `BelongsToTenant`
- ✅ **Queue** - مع `BelongsToTenant`
- ✅ **Notification** - مع `BelongsToTenant`
- ✅ **Invoice** - مع `BelongsToTenant`
- ✅ **Setting** - مرتبط بـ Tenant عبر `tenant_id` كـ primary key
- ✅ **Tenant** - يستخدم `stancl/tenancy` base model

### العلاقات (Relationships):
```php
// Tenant Model
$tenant->settings()  // One Setting per Tenant
$tenant->users()     // Many Users per Tenant

// User Model
$user->tenant()      // Belongs to Tenant
$user->roles()       // Spatie Permissions

// Appointment Model
$appointment->tenant()
$appointment->customer()  // User
$appointment->staff()     // User
$appointment->queue()
```

---

## 🛡️ 2. Middleware

### أ) **InitializeTenancyByDomain**
- التعرف على Tenant عبر subdomain
- يتم تفعيله تلقائياً من `stancl/tenancy`

### ب) **InitializeTenancyByToken** 
- التعرف على Tenant عبر:
  - Header: `X-Tenant-ID`
  - Query Parameter: `?tenant_id=xxx`
  - Bearer Token
- يتحقق من وجود وصحة Tenant
- يتحقق من أن Tenant نشط

### ج) **CheckSuperAdmin**
- يتحقق من أن المستخدم Super Admin
- يتحقق من أن `tenant_id = null` (central user)
- يتحقق من role "Super Admin"

### د) **SetTenantLocale**
- يضبط اللغة حسب إعدادات Tenant

### تسجيل الـ Middleware:
```php
// في bootstrap/app.php
'tenant' => InitializeTenancyByDomain::class,
'tenant.token' => InitializeTenancyByToken::class,
'tenant.locale' => SetTenantLocale::class,
'super.admin' => CheckSuperAdmin::class,
```

---

## 👨‍💼 3. Super Admin Dashboard

### Controllers:

#### **DashboardController**
```php
GET /api/super-admin/dashboard              // إحصائيات عامة
GET /api/super-admin/dashboard/tenants-overview  // نظرة على كل Tenants
GET /api/super-admin/dashboard/system-stats      // إحصائيات النظام
```

#### **TenantController**
```php
GET    /api/super-admin/tenants           // عرض كل Tenants
POST   /api/super-admin/tenants           // إنشاء Tenant جديد
GET    /api/super-admin/tenants/{id}      // عرض Tenant محدد
PUT    /api/super-admin/tenants/{id}      // تحديث Tenant
DELETE /api/super-admin/tenants/{id}      // حذف Tenant
POST   /api/super-admin/tenants/{id}/toggle-status  // تفعيل/تعطيل
GET    /api/super-admin/tenants/{id}/statistics     // إحصائيات Tenant
```

### ميزات Super Admin:
1. إدارة كل Tenants (CRUD)
2. تفعيل/تعطيل Tenants
3. عرض إحصائيات لكل Tenant
4. مراقبة النظام بالكامل

---

## 🌐 4. Routes API

### أ) Super Admin Routes:
```php
Prefix: /api/super-admin
Middleware: ['auth:sanctum', 'super.admin']
```

### ب) Tenant Routes (By Domain):
```php
Prefix: /api
Middleware: ['tenant', 'tenant.locale']
Access: عبر subdomain (tenant1.booking-saas.test)
```

### ج) Tenant Routes (By Token):
```php
Prefix: /api/v1
Middleware: ['tenant.token', 'tenant.locale']
Access: عبر Header X-Tenant-ID
```

---

## 🔐 5. Super Admin User

تم إنشاء Super Admin:
```
Email: superadmin@booking-saas.test
Password: password
tenant_id: null (central user)
Role: Super Admin
```

---

## 📝 6. استخدام النظام

### إنشاء Tenant جديد:
```bash
POST /api/super-admin/tenants
Headers:
  Authorization: Bearer {super_admin_token}
Body:
{
  "name": "مؤسسة الأمل",
  "domain": "amal",
  "active": true
}
```

### الوصول كـ Tenant (Subdomain):
```bash
GET https://amal.booking-saas.test/api/appointments
Headers:
  Authorization: Bearer {tenant_user_token}
```

### الوصول كـ Tenant (Token):
```bash
GET /api/v1/appointments
Headers:
  X-Tenant-ID: {tenant_id}
  Authorization: Bearer {token}
```

---

## 📊 7. الهيكل النهائي

```
Central Database (booking_saas)
├── tenants (جدول مركزي)
├── domains (جدول مركزي)
├── users (Super Admin فقط، tenant_id = null)
└── roles (مشترك)

Tenant Databases (tenant_{uuid})
├── users
├── appointments
├── queues
├── notifications
├── invoices
└── settings
```

---

## ✨ الخطوات التالية المقترحة:

1. إنشاء Tenant Controllers للـ CRUD operations
2. إضافة Authentication (Sanctum) للـ Tenants
3. إضافة Role-based permissions داخل كل Tenant
4. إنشاء API documentation
5. إضافة Rate Limiting
6. إضافة Logging & Monitoring

---

تم إعداد النظام بالكامل! 🎉
