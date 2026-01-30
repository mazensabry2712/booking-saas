# Queue Management API - Documentation

## 📋 نظام إدارة الدور (Queue Management)

تم إعداد نظام إدارة دور كامل مع دعم VIP وحساب الوقت المقدر للانتظار.

---

## ✅ الميزات المنفذة

### 1. **إنشاء رقم الدور تلقائياً**
- يتم إنشاء رقم دور تلقائي لكل يوم
- يبدأ من 1 كل يوم ويزيد تلقائياً

### 2. **دعم عملاء VIP**
- حقل `is_vip` في جدول Users
- حقل `priority` في جدول Queues
- الـ VIP يحصلون على أولوية أعلى (priority = 1)
- العملاء العاديين (priority = 0)

### 3. **تحديث الوقت المقدر للانتظار**
- حساب تلقائي للوقت المقدر بالدقائق
- يتحدث عند كل عملية (إضافة، next، skip)
- يأخذ في الاعتبار أولوية VIP

---

## 🚀 API Endpoints

### Base URL
```
https://tenant.booking-saas.test/api/
```

### Authentication
جميع الـ endpoints تتطلب:
```
Authorization: Bearer {token}
Role: Admin Tenant أو Staff
```

---

## 📌 1. عرض الدور (Index)

### Request:
```http
GET /api/queue
```

### Query Parameters:
- `status` (optional): Waiting, Serving, Served, Skipped
- `date` (optional): YYYY-MM-DD (default: today)

### Response:
```json
{
  "success": true,
  "data": {
    "total": 10,
    "waiting": 7,
    "current": {
      "id": 5,
      "queue_number": 5,
      "status": "Serving",
      "priority": 1,
      "estimated_wait_time": 0
    },
    "queues": [
      {
        "id": 1,
        "queue_number": 1,
        "status": "Waiting",
        "priority": 1,
        "estimated_wait_time": 15,
        "appointment": {
          "id": 10,
          "date": "2026-01-27",
          "time_slot": "10:00 AM",
          "customer": {
            "id": 5,
            "name": "أحمد محمد",
            "is_vip": true
          },
          "staff": {
            "id": 3,
            "name": "موظف 1"
          }
        }
      }
    ]
  }
}
```

---

## 📌 2. إضافة إلى الدور (Add)

### Request:
```http
POST /api/queue/add
Content-Type: application/json

{
  "appointment_id": 10
}
```

### Response (Success):
```json
{
  "success": true,
  "message": "Added to queue successfully",
  "data": {
    "id": 1,
    "tenant_id": "uuid-here",
    "appointment_id": 10,
    "queue_number": 1,
    "status": "Waiting",
    "priority": 1,
    "estimated_wait_time": 30,
    "created_at": "2026-01-27T10:00:00.000000Z",
    "appointment": {
      "customer": {
        "name": "أحمد محمد",
        "is_vip": true
      }
    }
  }
}
```

### Response (Error - Already in queue):
```json
{
  "error": "Appointment already in queue",
  "message": "This appointment is already added to the queue",
  "data": {
    "id": 1,
    "queue_number": 1,
    "status": "Waiting"
  }
}
```

---

## 📌 3. استدعاء التالي (Next)

### Request:
```http
POST /api/queue/next
```

### Response (Success):
```json
{
  "success": true,
  "message": "Next customer called",
  "data": {
    "id": 2,
    "queue_number": 2,
    "status": "Serving",
    "priority": 1,
    "estimated_wait_time": 0,
    "served_at": "2026-01-27T10:15:00.000000Z",
    "appointment": {
      "id": 11,
      "status": "Confirmed",
      "customer": {
        "name": "محمد علي",
        "is_vip": true
      }
    }
  }
}
```

### Response (No one waiting):
```json
{
  "success": false,
  "message": "No waiting customers in queue"
}
```

### Logic:
1. يتم وضع الـ "Serving" الحالي كـ "Served"
2. يتم اختيار التالي حسب:
   - أولاً: أعلى priority (VIP)
   - ثانياً: أقل queue_number
3. يتم تحديث status إلى "Serving"
4. يتم تحديث appointment status إلى "Confirmed"
5. يتم تحديث الوقت المقدر لباقي الدور

---

## 📌 4. تغيير الأولوية (Priority)

### Request:
```http
POST /api/queue/priority
Content-Type: application/json

{
  "queue_id": 5,
  "priority": 2
}
```

### Validation:
- `queue_id`: required, must exist
- `priority`: required, integer, min: 0, max: 10

### Response:
```json
{
  "success": true,
  "message": "Priority updated successfully",
  "data": {
    "id": 5,
    "queue_number": 5,
    "status": "Waiting",
    "priority": 2,
    "estimated_wait_time": 15
  }
}
```

### Priority Levels:
- `0`: عادي (Normal)
- `1`: VIP
- `2+`: أولوية أعلى (Higher priority)

---

## 📌 5. تخطي (Skip)

### Request:
```http
POST /api/queues/{id}/skip
```

### Response:
```json
{
  "success": true,
  "message": "Queue entry skipped",
  "data": {
    "id": 3,
    "queue_number": 3,
    "status": "Skipped"
  }
}
```

---

## 📌 6. عرض حسب الحالة (By Status)

### Request:
```http
GET /api/queues/status/{status}
```

### Status Options:
- `Waiting`
- `Serving`
- `Served`
- `Skipped`

### Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "queue_number": 1,
      "status": "Waiting",
      "priority": 1
    }
  ]
}
```

---

## 📌 7. حالة الدور للعميل (My Queue) - Customer Only

### Request:
```http
GET /api/my-queue
Authorization: Bearer {customer-token}
```

### Response (In queue):
```json
{
  "success": true,
  "data": {
    "queue": {
      "id": 5,
      "queue_number": 5,
      "status": "Waiting",
      "priority": 0,
      "created_at": "2026-01-27T10:00:00.000000Z"
    },
    "position": 3,
    "estimated_wait_time": 45,
    "is_vip": false
  }
}
```

### Response (Not in queue):
```json
{
  "success": false,
  "message": "You are not in the queue today"
}
```

---

## 🧮 خوارزمية حساب الوقت المقدر

```php
// Average service time per customer
$avgServiceTime = 15; // minutes

// Count queues ahead (considering priority)
$queuesAhead = count(queues with higher priority OR same priority but lower number);

// Calculate estimated time
$estimatedMinutes = $queuesAhead * $avgServiceTime;
```

### مثال:
- متوسط وقت الخدمة: 15 دقيقة
- عدد الأشخاص قبلك: 3
- الوقت المقدر: 3 × 15 = 45 دقيقة

---

## 🔄 تحديث تلقائي للوقت المقدر

يتم تحديث الوقت المقدر تلقائياً عند:
1. إضافة شخص جديد للدور (`add`)
2. استدعاء التالي (`next`)
3. تغيير الأولوية (`priority`)
4. تخطي شخص (`skip`)

---

## 📊 Database Schema

### Users Table (إضافة):
```sql
is_vip BOOLEAN DEFAULT false
```

### Queues Table (إضافة):
```sql
priority INTEGER DEFAULT 0
estimated_wait_time INTEGER DEFAULT 0  -- in minutes
served_at TIMESTAMP NULL
```

---

## 🎯 سيناريوهات الاستخدام

### 1. إضافة عميل عادي للدور:
```bash
POST /api/queue/add
{
  "appointment_id": 10
}

# Result: queue_number=1, priority=0, estimated_wait_time=0
```

### 2. إضافة عميل VIP للدور:
```bash
POST /api/queue/add
{
  "appointment_id": 11  # Customer is VIP
}

# Result: queue_number=2, priority=1, estimated_wait_time=0 (يتقدم على العادي)
```

### 3. استدعاء التالي:
```bash
POST /api/queue/next

# Result: VIP (priority=1) يتم استدعاءه أولاً
```

### 4. ترقية عميل لـ VIP في الدور:
```bash
POST /api/queue/priority
{
  "queue_id": 5,
  "priority": 1
}

# Result: يتم تحديث ترتيبه في الدور
```

---

## ✨ الميزات الإضافية

- ✅ رقم دور تلقائي لكل يوم
- ✅ دعم VIP مع أولوية
- ✅ حساب الوقت المقدر
- ✅ تحديث تلقائي للأوقات
- ✅ حالات متعددة (Waiting, Serving, Served, Skipped)
- ✅ API للعملاء لمعرفة موقعهم
- ✅ تخطي العملاء
- ✅ إدارة الأولويات

---

النظام جاهز للاستخدام! 🎉
