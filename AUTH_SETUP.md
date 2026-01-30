# Authentication & Roles - Documentation

## 🔐 نظام المصادقة والصلاحيات

تم إعداد نظام مصادقة كامل باستخدام **Laravel Sanctum** مع نظام أدوار وصلاحيات شامل.

---

## ✅ 1. Authentication Setup

### Sanctum Configuration
- تم إضافة `HasApiTokens` trait لـ User Model
- استخدام Token-based authentication
- دعم Token abilities لكل Role

---

## 👥 2. Roles (الأدوار)

### الأدوار المتاحة:

| Role | الوصف | النطاق |
|------|--------|--------|
| **Super Admin** | صلاحيات كاملة لإدارة النظام | Central (tenant_id = null) |
| **Admin Tenant** | صلاحيات كاملة داخل Tenant | Tenant-specific |
| **Staff** | إدارة المواعيد والدور فقط | Tenant-specific |
| **Customer** | حجز المواعيد فقط | Tenant-specific |

---

## 🔑 3. Permissions (الصلاحيات)

### Super Admin Permissions:
```
- manage-tenants
- create-tenant
- update-tenant
- delete-tenant
- view-tenant-statistics
- activate-tenant
- deactivate-tenant
```

### Admin Tenant Permissions:
```
- manage-users
- manage-staff
- manage-appointments
- manage-queues
- manage-invoices
- manage-notifications
- manage-settings
- view-reports
```

### Staff Permissions:
```
- view-appointments
- create-appointment
- update-appointment
- view-queue
- update-queue
- view-customers
```

### Customer Permissions:
```
- view-own-appointments
- create-own-appointment
- cancel-own-appointment
- view-own-invoices
- view-own-queue
```

---

## 🛡️ 4. Middleware

### تسجيل الـ Middleware:
```php
// في bootstrap/app.php
'role' => CheckRole::class,           // التحقق من Role
'ability' => CheckTokenAbility::class, // التحقق من Token Abilities
```

### استخدام Middleware:

#### التحقق من Role واحد:
```php
Route::middleware(['role:Admin Tenant'])->group(function () {
    // Routes for Admin Tenant only
});
```

#### التحقق من عدة Roles:
```php
Route::middleware(['role:Admin Tenant|Staff'])->group(function () {
    // Routes for Admin Tenant OR Staff
});
```

#### التحقق من Token Abilities:
```php
Route::middleware(['ability:admin-tenant'])->group(function () {
    // Routes requiring admin-tenant ability
});
```

---

## 🚀 5. Authentication Endpoints

### A) Super Admin Authentication

#### Login:
```http
POST /api/super-admin/auth/login
Content-Type: application/json

{
  "email": "superadmin@booking-saas.test",
  "password": "password"
}

Response:
{
  "success": true,
  "message": "Super Admin logged in successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "Super Admin",
      "email": "superadmin@booking-saas.test",
      "role": "Super Admin"
    },
    "token": "1|xxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

#### Profile:
```http
GET /api/super-admin/auth/profile
Authorization: Bearer {token}
```

#### Logout:
```http
POST /api/super-admin/auth/logout
Authorization: Bearer {token}
```

---

### B) Tenant User Authentication

#### Login (By Domain):
```http
POST https://tenant1.booking-saas.test/api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}

Response:
{
  "success": true,
  "message": "Logged in successfully",
  "data": {
    "user": {
      "id": 2,
      "name": "John Doe",
      "email": "user@example.com",
      "role": "Customer",
      "tenant_id": "uuid-here"
    },
    "tenant": {
      "id": "uuid-here",
      "name": "Tenant Name",
      "domain": "tenant1"
    },
    "token": "2|xxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

#### Register (Customer only):
```http
POST https://tenant1.booking-saas.test/api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### Login (By Token/Header):
```http
POST /api/v1/auth/login
X-Tenant-ID: {tenant_id}
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

---

## 📋 6. Protected Routes Examples

### Super Admin Routes:
```php
// All require: ['auth:sanctum', 'super.admin']
GET    /api/super-admin/dashboard
GET    /api/super-admin/tenants
POST   /api/super-admin/tenants
PUT    /api/super-admin/tenants/{id}
DELETE /api/super-admin/tenants/{id}
```

### Tenant Routes by Role:

#### Admin Tenant Only:
```php
GET  /api/settings
PUT  /api/settings
GET  /api/invoices
POST /api/invoices
```

#### Admin Tenant & Staff:
```php
GET    /api/appointments
POST   /api/appointments
PUT    /api/appointments/{id}
DELETE /api/appointments/{id}

GET    /api/queues
POST   /api/queues
PUT    /api/queues/{id}
```

#### Customer Only:
```php
POST /api/appointments              // Create appointment
GET  /api/my-appointments           // View own appointments
GET  /api/my-queue                  // View own queue
GET  /api/my-invoices               // View own invoices
GET  /api/invoices/{id}/download    // Download own invoice
```

---

## 🎯 7. User Model Helper Methods

```php
// Check if user is Super Admin
$user->isSuperAdmin()    // Returns bool

// Check if user is Admin Tenant
$user->isAdminTenant()   // Returns bool

// Check if user is Staff
$user->isStaff()         // Returns bool

// Check if user is Customer
$user->isCustomer()      // Returns bool

// Check role using Spatie
$user->hasRole('Admin Tenant')

// Check permission
$user->can('manage-appointments')

// Get all permissions
$user->getAllPermissions()
```

---

## 🔐 8. Token Abilities

عند تسجيل الدخول، يتم إنشاء Token مع abilities حسب الـ Role:

| Role | Token Abilities |
|------|----------------|
| Super Admin | `['super-admin']` |
| Admin Tenant | `['admin-tenant']` |
| Staff | `['staff']` |
| Customer | `['customer']` |

---

## 📊 9. Testing Authentication

### Test Super Admin Login:
```bash
curl -X POST http://localhost/api/super-admin/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "superadmin@booking-saas.test",
    "password": "password"
  }'
```

### Test Tenant Login:
```bash
curl -X POST http://tenant1.booking-saas.test/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password"
  }'
```

### Test Protected Route:
```bash
curl -X GET http://localhost/api/super-admin/dashboard \
  -H "Authorization: Bearer {your-token}"
```

---

## ✨ الخطوات المكتملة:

- ✅ إعداد Sanctum Authentication
- ✅ إنشاء Auth Controllers (Super Admin & Tenant)
- ✅ إنشاء Role Middleware
- ✅ تحديث User Model مع Helper Methods
- ✅ إنشاء Roles & Permissions Seeder
- ✅ تحديث Routes مع Role-based Access Control
- ✅ دعم Token Abilities

---

النظام جاهز للاستخدام! 🎉
