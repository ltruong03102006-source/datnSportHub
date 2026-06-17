# Admin Venues Management API

## Tổng Quan
Tính năng cho phép Admin quản lý trạng thái hoạt động của các sân thể thao:
- **Kích hoạt sân** (activate): Chuyển trạng thái sân thành `active`
- **Ẩn sân** (deactivate): Chuyển trạng thái sân thành `inactive`
- **Ghi log hoạt động**: Tất cả thay đổi được ghi lại trong bảng `venue_logs`
- **Giữ booking cũ**: Khi ẩn sân, các booking trong tương lai vẫn được giữ lại

## Kiến Trúc

### Models
- **`Venue`**: Model sân thể thao
  - `status`: enum ('active', 'inactive', 'pending')
  - Relationship: `logs()` → VenueLog
  
- **`VenueLog`**: Model ghi log thay đổi trạng thái sân
  - `venue_id`: Khóa ngoại đến Venue
  - `admin_id`: ID admin thực hiện hành động
  - `action`: enum ('activated', 'deactivated')
  - `old_status` / `new_status`: Trạng thái trước/sau
  - `reason`: Lý do thay đổi
  - `notes`: Ghi chú thêm

### Controllers
- **`AdminVenueController`**: 
  - `index()`: Liệt kê danh sách sân (với lọc)
  - `show()`: Xem chi tiết sân
  - `activate()`: Kích hoạt sân
  - `deactivate()`: Ẩn sân
  - `logs()`: Xem lịch sử thay đổi

### Middleware
- **`admin`**: Kiểm tra role = 'admin'

## API Endpoints

### 1. Liệt kê danh sách sân
```
GET /api/admin/venues
```

**Query Parameters:**
```
- status: string (active, inactive, pending) - tùy chọn
- search: string - tìm kiếm theo tên hoặc địa chỉ
- owner_id: integer - lọc theo owner
- sport_id: integer - lọc theo thể thao
- sort_by: string (default: created_at) - trường sắp xếp
- sort_order: string (default: desc) - ASC hoặc DESC
- per_page: integer (default: 15) - số sân trên trang
```

**Response:**
```json
{
  "success": true,
  "message": "Danh sách sân",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "owner_id": 2,
        "sport_id": 1,
        "name": "Sân bóng chuyền A",
        "address": "123 Đường ABC",
        "status": "active",
        "created_at": "2026-06-16T10:00:00Z",
        "updated_at": "2026-06-16T10:00:00Z",
        "owner": { ... },
        "sport": { ... }
      }
    ],
    "total": 50,
    "per_page": 15
  }
}
```

### 2. Xem chi tiết sân
```
GET /api/admin/venues/{venue_id}
```

**Response:**
```json
{
  "success": true,
  "message": "Chi tiết sân",
  "data": {
    "id": 1,
    "name": "Sân bóng chuyền A",
    "status": "active",
    "owner": { "id": 2, "name": "Chủ sân", "email": "owner@example.com" },
    "sport": { "id": 1, "name": "Bóng chuyền" },
    "courts": [ ... ],
    "logs": [
      {
        "id": 1,
        "action": "activated",
        "old_status": "pending",
        "new_status": "active",
        "reason": "Đã kiểm tra đủ điều kiện",
        "admin": { "id": 1, "name": "Admin A" },
        "created_at": "2026-06-16T10:00:00Z"
      }
    ]
  },
  "future_bookings_count": 5
}
```

### 3. Kích hoạt sân
```
POST /api/admin/venues/{venue_id}/activate
```

**Request Body:**
```json
{
  "reason": "Sân đã được kiểm tra hạng tầng",
  "notes": "Duyệt từ lần kiểm tra ngày 16/06/2026"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Sân đã được kích hoạt thành công.",
  "data": {
    "id": 1,
    "name": "Sân bóng chuyền A",
    "status": "active",
    "updated_at": "2026-06-16T10:30:00Z"
  }
}
```

### 4. Ẩn sân
```
POST /api/admin/venues/{venue_id}/deactivate
```

**Request Body:**
```json
{
  "reason": "Sân không đủ tiêu chuẩn",
  "notes": "Cần cải thiện chất lượng nền nhà"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Sân đã được ẩn thành công. (3 booking trong tương lai vẫn được giữ lại.)",
  "data": {
    "id": 1,
    "name": "Sân bóng chuyền A",
    "status": "inactive",
    "updated_at": "2026-06-16T10:30:00Z"
  },
  "future_bookings_count": 3
}
```

### 5. Xem lịch sử thay đổi sân
```
GET /api/admin/venues/{venue_id}/logs
```

**Query Parameters:**
```
- per_page: integer (default: 20) - số log trên trang
```

**Response:**
```json
{
  "success": true,
  "message": "Lịch sử thay đổi sân",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 5,
        "venue_id": 1,
        "admin_id": 1,
        "action": "deactivated",
        "old_status": "active",
        "new_status": "inactive",
        "reason": "Báo cáo vi phạm từ khách hàng",
        "notes": "Cần điều tra thêm",
        "admin": {
          "id": 1,
          "name": "Admin A",
          "email": "admin@example.com"
        },
        "created_at": "2026-06-16T10:30:00Z",
        "updated_at": "2026-06-16T10:30:00Z"
      }
    ],
    "total": 3,
    "per_page": 20
  }
}
```

## Quy Tắc Kinh Doanh

### Yêu Cầu Quyền Truy Cập
- ✅ Chỉ **Admin** (role = 'admin') mới có quyền thực hiện
- ❌ **Court Owner** không thể tự kích hoạt/ẩn sân của mình
- ✅ Yêu cầu middleware `auth:sanctum` + `admin`

### Kích Hoạt Sân
- Chuyển trạng thái từ `pending` hoặc `inactive` → `active`
- Không kích hoạt nếu sân đã `active`

### Ẩn Sân
- Chuyển trạng thái từ `active` → `inactive`
- Không ẩn nếu sân đã `inactive`
- **Giữ lại các booking trong tương lai:**
  - Không hủy booking
  - Không cho phép tạo booking mới
  - Ghi chú số lượng booking trong future

### Ghi Log
- Mỗi hành động (kích hoạt/ẩn) đều được ghi lại
- Log chứa:
  - Admin thực hiện (admin_id)
  - Hành động (action)
  - Trạng thái trước/sau
  - Lý do & ghi chú
  - Thời gian thực hiện (created_at)

## HTTP Status Codes

| Code | Ý Nghĩa |
|------|---------|
| 200 | Thành công |
| 400 | Dữ liệu không hợp lệ (VD: sân đã active) |
| 403 | Không có quyền (không phải admin) |
| 404 | Không tìm thấy sân |
| 500 | Lỗi server |

## Ví Dụ Test

### 1. Liệt kê sân có trạng thái 'pending'
```bash
curl -X GET "http://localhost:8000/api/admin/venues?status=pending" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

### 2. Kích hoạt một sân
```bash
curl -X POST "http://localhost:8000/api/admin/venues/1/activate" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reason": "Sân đã đạt tiêu chuẩn",
    "notes": "Kiểm tra lần 2 đã ok"
  }'
```

### 3. Ẩn một sân
```bash
curl -X POST "http://localhost:8000/api/admin/venues/1/deactivate" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "reason": "Vi phạm quy định",
    "notes": "Báo cáo từ khách hàng"
  }'
```

### 4. Xem lịch sử thay đổi sân
```bash
curl -X GET "http://localhost:8000/api/admin/venues/1/logs?per_page=10" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

## Cảnh Báo & Lưu Ý

⚠️ **Quan trọng:**
- Admin không được tự kích hoạt sân của chính Admin đó (nếu Admin cũng là Owner)
- Khi ẩn sân, hệ thống sẽ **KHÔNG** tự động hủy booking → phải liên hệ khách hàng thủ công
- Log được lưu vĩnh viễn, không thể xóa
- Status 'pending' là trạng thái mặc định khi Owner tạo sân mới

## Integrations

### Controller: [AdminVenueController](../app/Http/Controllers/Api/AdminVenueController.php)
### Model: [VenueLog](../app/Models/VenueLog.php)
### Migration: [create_venue_logs_table](../database/migrations/2026_06_16_000001_create_venue_logs_table.php)
### Routes: [api.php](../routes/api.php) - Admin section
