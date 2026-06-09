# Quick Start - Owner API Authentication

## Tóm Tắt
Hệ thống xác thực API cho chủ sân với:
- ✅ Đăng ký/Đăng nhập riêng biệt
- ✅ Role-based middleware (owner)
- ✅ Token authentication (Sanctum)
- ✅ Quản lý sân vận động và sân nhỏ
- ✅ Xem booking của chủ sân

---

## 1. Installation & Setup (Đã Xong ✓)

Các file đã được tạo:
- `app/Http/Middleware/EnsureOwnerRole.php`
- `app/Http/Controllers/Api/OwnerAuthController.php`
- `app/Http/Controllers/Api/OwnerVenueController.php`
- `app/Http/Controllers/Api/OwnerCourtController.php`
- `app/Http/Controllers/Api/OwnerBookingController.php`
- `bootstrap/app.php` (middleware alias updated)
- `routes/api.php` (owner routes added)

**Không cần chạy migration** - sử dụng existing tables

---

## 2. Testing với Postman

### Step 1: Đăng ký chủ sân
```
POST http://localhost:8000/api/owner/register
Content-Type: application/json

{
    "name": "Nguyễn Văn A",
    "email": "owner@test.com",
    "password": "password123",
    "confirm_password": "password123",
    "phone": "0901234567"
}
```

**Response**:
```json
{
    "message": "Đăng ký chủ sân thành công. Vui lòng chờ duyệt từ admin",
    "token": "1|xxxxxxxxxxxx",
    "user": {
        "id": 1,
        "role": "user",  // Chưa phải owner
        "status": "active"
    }
}
```

### Step 2: Admin duyệt (Database)
```sql
-- Cập nhật user thành owner
UPDATE users SET role = 'owner' WHERE email = 'owner@test.com';
```

Hoặc sử dụng Tinker:
```bash
php artisan tinker
>>> $user = User::where('email', 'owner@test.com')->first();
>>> $user->update(['role' => 'owner']);
```

### Step 3: Đăng nhập
```
POST http://localhost:8000/api/owner/login
Content-Type: application/json

{
    "email": "owner@test.com",
    "password": "password123"
}
```

**Response**:
```json
{
    "message": "Đăng nhập thành công",
    "token": "2|xxxxxxxxxxxx",
    "user": {
        "role": "owner",  // ✓ Đã là owner
        "status": "active"
    }
}
```

### Step 4: Lấy info chủ sân
```
GET http://localhost:8000/api/owner/me
Authorization: Bearer 2|xxxxxxxxxxxx
```

### Step 5: Tạo sân vận động
```
POST http://localhost:8000/api/owner/venues
Authorization: Bearer 2|xxxxxxxxxxxx
Content-Type: application/json

{
    "sport_id": 1,
    "name": "Sân Bóng Đá Minh Châu",
    "address": "123 Đường ABC, Hà Nội",
    "lat": 21.0285,
    "lng": 105.8542,
    "description": "Sân bóng đá hiện đại"
}
```

### Step 6: Xem sân của mình
```
GET http://localhost:8000/api/owner/venues
Authorization: Bearer 2|xxxxxxxxxxxx
```

### Step 7: Tạo sân nhỏ
```
POST http://localhost:8000/api/owner/venues/1/courts
Authorization: Bearer 2|xxxxxxxxxxxx
Content-Type: application/json

{
    "name": "Sân 1",
    "type": "Bóng đá 5 người",
    "description": "Sân bóng đá nhỏ"
}
```

### Step 8: Xem booking của chủ sân
```
GET http://localhost:8000/api/owner/bookings
Authorization: Bearer 2|xxxxxxxxxxxx
```

---

## 3. Postman Collection

Import `postman_booking_collection.json` và thêm biến:

```
owner_token = 2|xxxxxxxxxxxx
owner_id = 1
```

---

## 4. Authentication Flow

```
┌─ Public Routes (Không cần token)
│  ├─ POST /api/owner/register
│  └─ POST /api/owner/login
│
├─ Protected Routes (Cần: auth:sanctum + middleware owner)
│  ├─ POST /api/owner/logout
│  ├─ GET /api/owner/me
│  ├─ POST /api/owner/change-password
│  │
│  ├─ Venues (Quản lý sân vận động)
│  │  ├─ GET /api/owner/venues
│  │  ├─ POST /api/owner/venues
│  │  ├─ GET /api/owner/venues/{id}
│  │  ├─ PUT /api/owner/venues/{id}
│  │  └─ DELETE /api/owner/venues/{id}
│  │
│  ├─ Courts (Quản lý sân nhỏ)
│  │  ├─ GET /api/owner/courts
│  │  ├─ POST /api/owner/venues/{venueId}/courts
│  │  ├─ PUT /api/owner/courts/{courtId}
│  │  └─ DELETE /api/owner/courts/{courtId}
│  │
│  └─ Bookings (Xem booking)
│     ├─ GET /api/owner/bookings
│     ├─ GET /api/owner/bookings/stats
│     └─ GET /api/owner/bookings/{id}
│
└─ Middleware Check
   ├─ Token valid? (auth:sanctum)
   ├─ role = 'owner'? (owner middleware)
   └─ status = 'active'? (owner middleware)
```

---

## 5. Error Responses

### 401 Unauthorized
```json
{
    "message": "Unauthorized - Vui lòng đăng nhập"
}
```
**Nguyên nhân**: Không có token hoặc token hết hạn

### 403 Forbidden
```json
{
    "message": "Forbidden - Bạn không phải chủ sân"
}
```
**Nguyên nhân**: User không có role `owner` hoặc status không phải `active`

### 422 Validation Error
```json
{
    "message": "Validation error",
    "errors": {
        "email": ["Email này đã được đăng ký"],
        "password": ["Mật khẩu phải có ít nhất 8 ký tự"]
    }
}
```

---

## 6. Files Structure

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── OwnerAuthController.php      ← Auth (register/login)
│   │   ├── OwnerVenueController.php     ← Venue management
│   │   ├── OwnerCourtController.php     ← Court management
│   │   └── OwnerBookingController.php   ← Booking tracking
│   └── Middleware/
│       └── EnsureOwnerRole.php          ← Role check middleware
├── Models/
│   ├── User.php                         ← +HasApiTokens, role field
│   └── OwnerRegistration.php
├── Providers/
│   └── AppServiceProvider.php
routes/
├── api.php                               ← Owner routes added
└── ...
bootstrap/
└── app.php                               ← Middleware alias registered
```

---

## 7. Database Schema (Existing)

### Users table
```
id | name | email | password | role (user/owner) | status (active/inactive) | ...
```

### OwnerRegistration table
```
id | user_id | name | email | phone | status (pending/approved) | ...
```

### Venues table
```
id | owner_id | sport_id | name | address | lat | lng | status | ...
```

### Courts table
```
id | venue_id | name | type | description | ...
```

### Bookings table
```
id | court_id | user_id | booking_date | status | ...
```

---

## 8. Next Steps (Optional)

- [ ] Thêm OwnerStatsController (doanh thu, thống kê)
- [ ] Thêm quản lý giá khung giờ (SlotPrice)
- [ ] Thêm quản lý khung giờ (TimeSlot)
- [ ] Thêm xem reviews
- [ ] Thêm dashboard chủ sân
- [ ] Thêm webhook notifications

---

## 9. Debugging

### Check routes
```bash
php artisan route:list | grep owner
```

### Check middleware
```bash
php artisan route:list --name=owner.venues.index
```

### Tinker test
```bash
php artisan tinker
>>> $user = User::factory()->create(['role' => 'owner', 'status' => 'active']);
>>> $token = $user->createToken('test')->plainTextToken;
>>> echo $token;
```

---

## 10. Support

Xem tài liệu đầy đủ: `OWNER_API.md`
