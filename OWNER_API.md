# Owner API Authentication Documentation

## Tổng Quan
Hệ thống xác thực API riêng cho chủ sân (Role: owner) với middleware bảo vệ, cho phép:
- Đăng ký tài khoản chủ sân
- Đăng nhập và nhận token
- Quản lý sân vận động (venue)
- Quản lý sân nhỏ (court)
- Đổi mật khẩu

## 1. Cấu Trúc Hệ Thống

### Middleware: `EnsureOwnerRole`
- **Đường dẫn**: `app/Http/Middleware/EnsureOwnerRole.php`
- **Chức năng**: Kiểm tra user có role `owner` và status `active`
- **Alias**: `owner` (được đăng ký trong `bootstrap/app.php`)

### Controllers

#### OwnerAuthController
- **Đường dẫn**: `app/Http/Controllers/Api/OwnerAuthController.php`
- **Chức năng**: Xử lý đăng ký, đăng nhập, đổi mật khẩu

#### OwnerVenueController
- **Đường dẫn**: `app/Http/Controllers/Api/OwnerVenueController.php`
- **Chức năng**: Quản lý sân vận động

#### OwnerCourtController
- **Đường dẫn**: `app/Http/Controllers/Api/OwnerCourtController.php`
- **Chức năng**: Quản lý sân nhỏ

## 2. Các Endpoint API

### Public Routes (Không cần xác thực)

#### 2.1 Đăng ký chủ sân
```
POST /api/owner/register
Content-Type: application/json

{
    "name": "Nguyễn Văn A",
    "email": "owner@example.com",
    "password": "password123",
    "confirm_password": "password123",
    "phone": "0901234567"
}
```

**Response (201)**:
```json
{
    "message": "Đăng ký chủ sân thành công. Vui lòng chờ duyệt từ admin",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
        "id": 1,
        "name": "Nguyễn Văn A",
        "email": "owner@example.com",
        "role": "user",
        "status": "active"
    }
}
```

#### 2.2 Đăng nhập
```
POST /api/owner/login
Content-Type: application/json

{
    "email": "owner@example.com",
    "password": "password123"
}
```

**Response (200)**:
```json
{
    "message": "Đăng nhập thành công",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
        "id": 1,
        "name": "Nguyễn Văn A",
        "email": "owner@example.com",
        "role": "owner",
        "status": "active"
    }
}
```

### Protected Routes (Cần xác thực + role owner)

#### 2.3 Đăng xuất
```
POST /api/owner/logout
Authorization: Bearer {token}
```

**Response (200)**:
```json
{
    "message": "Đăng xuất thành công"
}
```

#### 2.4 Lấy thông tin chủ sân hiện tại
```
GET /api/owner/me
Authorization: Bearer {token}
```

**Response (200)**:
```json
{
    "user": {
        "id": 1,
        "name": "Nguyễn Văn A",
        "email": "owner@example.com",
        "role": "owner",
        "status": "active"
    }
}
```

#### 2.5 Đổi mật khẩu
```
POST /api/owner/change-password
Authorization: Bearer {token}
Content-Type: application/json

{
    "current_password": "password123",
    "new_password": "newpassword456",
    "confirm_password": "newpassword456"
}
```

**Response (200)**:
```json
{
    "message": "Đổi mật khẩu thành công"
}
```

### Venue Management (Quản lý sân vận động)

#### 2.6 Lấy danh sách sân vận động
```
GET /api/owner/venues
Authorization: Bearer {token}
```

**Response (200)**:
```json
{
    "message": "Danh sách sân của bạn",
    "data": [
        {
            "id": 1,
            "owner_id": 1,
            "sport_id": 1,
            "name": "Sân Bóng Đá Minh Châu",
            "address": "123 Đường ABC, Hà Nội",
            "lat": 21.0285,
            "lng": 105.8542,
            "description": "Sân bóng đá hiện đại",
            "banner": "venues/banner.jpg",
            "status": "active",
            "created_at": "2026-06-09T10:30:00",
            "updated_at": "2026-06-09T10:30:00",
            "sport": { ... },
            "courts": [ ... ]
        }
    ]
}
```

#### 2.7 Tạo sân vận động mới
```
POST /api/owner/venues
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "sport_id": 1,
    "name": "Sân Bóng Đá Minh Châu",
    "address": "123 Đường ABC, Hà Nội",
    "lat": 21.0285,
    "lng": 105.8542,
    "description": "Sân bóng đá hiện đại",
    "banner": <image_file>
}
```

**Response (201)**:
```json
{
    "message": "Tạo sân thành công. Sân của bạn đang chờ duyệt.",
    "data": { ... }
}
```

#### 2.8 Lấy chi tiết sân vận động
```
GET /api/owner/venues/{id}
Authorization: Bearer {token}
```

#### 2.9 Cập nhật sân vận động
```
PUT /api/owner/venues/{id}
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
    "name": "Sân mới",
    "description": "Mô tả mới",
    "banner": <image_file>
}
```

#### 2.10 Xóa sân vận động
```
DELETE /api/owner/venues/{id}
Authorization: Bearer {token}
```

### Court Management (Quản lý sân nhỏ)

#### 2.11 Lấy danh sách sân nhỏ
```
GET /api/owner/courts
Authorization: Bearer {token}
```

#### 2.12 Tạo sân nhỏ
```
POST /api/owner/venues/{venueId}/courts
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Sân 1",
    "type": "Bóng đá 5 người",
    "description": "Sân bóng đá nhỏ"
}
```

#### 2.13 Cập nhật sân nhỏ
```
PUT /api/owner/courts/{courtId}
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "Sân 1 - Mới",
    "description": "Mô tả mới"
}
```

#### 2.14 Xóa sân nhỏ
```
DELETE /api/owner/courts/{courtId}
Authorization: Bearer {token}
```

#### 2.15 Lấy khung giờ của sân nhỏ
```
GET /api/owner/courts/{courtId}/time-slots
Authorization: Bearer {token}
```

#### 2.16 Lấy danh sách loại thể thao
```
GET /api/owner/sports
Authorization: Bearer {token}
```

## 3. Error Handling

### Các lỗi thường gặp

| Status | Message | Giải thích |
|--------|---------|-----------|
| 401 | Unauthorized - Vui lòng đăng nhập | Không có token hoặc token không hợp lệ |
| 403 | Forbidden - Bạn không phải chủ sân | User không có role `owner` |
| 403 | Forbidden - Tài khoản chủ sân chưa được kích hoạt | Account bị khóa hoặc chưa được duyệt |
| 404 | Sân không được tìm thấy hoặc bạn không có quyền | Resource không tồn tại hoặc không sở hữu |
| 422 | Validation error | Dữ liệu input không hợp lệ |
| 500 | Lỗi hệ thống | Lỗi server |

**Ví dụ error response**:
```json
{
    "message": "Validation error",
    "errors": {
        "email": ["Email này đã được đăng ký"],
        "password": ["Mật khẩu phải có ít nhất 8 ký tự"]
    }
}
```

## 4. Flow Quy Trình Đăng Ký

```
1. Owner gọi POST /api/owner/register
   ↓
2. Tạo User với role="user" (chưa phải owner)
   ↓
3. Tạo OwnerRegistration với status="pending"
   ↓
4. Trả về token (tạm thời)
   ↓
5. Admin duyệt -> cập nhật role="owner" + status="active"
   ↓
6. Owner có thể đăng nhập với role "owner"
```

## 5. Token Authentication

Sử dụng Laravel Sanctum API tokens:

```
Authorization: Bearer {plainTextToken}
```

**Lấy token sau khi login**:
```javascript
const response = await fetch('/api/owner/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
});

const data = await response.json();
const token = data.token;
```

**Sử dụng token trong request tiếp theo**:
```javascript
const response = await fetch('/api/owner/me', {
    headers: { 'Authorization': `Bearer ${token}` }
});
```

## 6. Testing với Postman

### Import Collection
1. Mở Postman
2. Click "Import" → chọn file `postman_booking_collection.json`
3. Chọn folder "Owner API" (nếu đã thêm vào collection)

### Hoặc tạo request thủ công
1. **POST** /api/owner/register → lấy token
2. Lưu token vào Postman variable: `owner_token`
3. Sử dụng `{{owner_token}}` trong Authorization header

## 7. Database Schema

### Users table
```sql
- id
- name
- email
- password (hashed)
- role (user, owner, admin) ← role owner được tạo sau khi admin duyệt
- status (active, inactive, suspended)
- created_at, updated_at
```

### OwnerRegistration table
```sql
- id
- user_id (nullable, liên kết khi tạo từ API)
- name
- email
- phone
- status (pending, approved, rejected)
- created_at, updated_at
```

## 8. Ghi Chú Quan Trọng

1. **Role từ user → owner**: Khi admin duyệt OwnerRegistration, cập nhật User role thành "owner"
2. **Token khác nhau**: User token và Owner token là khác nhau (do middleware kiểm tra)
3. **Xác thực hai lớp**: Cần `auth:sanctum` + middleware `owner`
4. **Venue status**: Sân mới có status="pending", cần admin duyệt
5. **Xóa sân nhỏ**: Không thể xóa nếu có booking

## 9. File Cấu Hình

- **Middleware**: `app/Http/Middleware/EnsureOwnerRole.php`
- **Controllers**: 
  - `app/Http/Controllers/Api/OwnerAuthController.php`
  - `app/Http/Controllers/Api/OwnerVenueController.php`
  - `app/Http/Controllers/Api/OwnerCourtController.php`
- **Routes**: `routes/api.php` (phần Owner)
- **Bootstrap**: `bootstrap/app.php` (middleware alias)

## 10. Mở Rộng Trong Tương Lai

Có thể thêm:
- [ ] OwnerBookingController - xem các booking của chủ sân
- [ ] OwnerStatsController - thống kê, doanh thu
- [ ] SlotPriceController - quản lý giá khung giờ
- [ ] TimeSlotController - quản lý khung giờ
- [ ] OwnerReviewController - xem reviews
