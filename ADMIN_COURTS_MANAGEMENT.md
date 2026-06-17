# Admin Quản lý Danh sách Sân (Web Interface)

## 📋 Tóm Tắt

Tính năng cho phép Admin quản lý trạng thái của các sân con (courts) trên giao diện web:
- ✅ Liệt kê danh sách sân với lọc & tìm kiếm
- ✅ Xem chi tiết sân
- ✅ Kích hoạt / Ẩn sân (toggle status)
- ✅ Chỉnh sửa thông tin sân
- ✅ Xóa sân (nếu không có booking)
- ✅ Cập nhật trạng thái hàng loạt (batch)
- ✅ Hiển thị badge trạng thái (xanh/đỏ)
- ✅ Phân trang & thống kê

## 🗂️ Cấu Trúc File

### Controllers
- **`app/Http/Controllers/Web/AdminCourtController.php`** - 6 methods:
  - `index()` - Danh sách sân (lọc, tìm kiếm, phân trang)
  - `show()` - Chi tiết sân + lịch sử
  - `toggleStatus()` - Thay đổi trạng thái (active ↔ inactive)
  - `update()` - Cập nhật thông tin sân
  - `destroy()` - Xóa sân
  - `batchUpdateStatus()` - Cập nhật trạng thái hàng loạt

### Models
- **`app/Models/Court.php`** - Cập nhật:
  - Fillable: ['venue_id', 'name', 'status', 'is_bookable_online']
  - Scope: `active()`, `inactive()`
  - Methods: `canBeBooked()`, `hasFutureBookings()`

### Views (Blade Templates)
- **`resources/views/admin/courts/index.blade.php`**
  - Danh sách sân
  - Filter: tên, trạng thái, cơ sở sân
  - Thống kê (tổng, hoạt động, đã ẩn)
  - Modal chỉnh sửa
  - Batch actions

- **`resources/views/admin/courts/show.blade.php`**
  - Chi tiết sân
  - Thông tin cơ bản
  - Thống kê (ca giờ, lịch đặt)
  - Ca giờ hoạt động
  - Lịch đặt gần đây
  - Action buttons

### Routes
```
GET    /admin/courts                        - Danh sách sân
GET    /admin/courts/{court}                - Chi tiết sân
PATCH  /admin/courts/{court}/toggle-status  - Thay đổi trạng thái
PUT    /admin/courts/{court}                - Cập nhật sân
DELETE /admin/courts/{court}                - Xóa sân
POST   /admin/courts/batch-update-status    - Cập nhật hàng loạt
```

### Navigation
- Thêm menu "Quản lý sân" vào sidebar admin ✓

## 🎯 Tính Năng Chi Tiết

### 1. Danh sách sân (`/admin/courts`)

**Thống kê (Cards):**
- Tổng cộng: Số sân tất cả
- Hoạt động: Sân có status = active (Badge xanh)
- Đã ẩn: Sân có status = inactive (Badge đỏ)

**Bảng danh sách:**
| Checkbox | ID | Tên Sân | Cơ sở | Chủ sân | Địa chỉ | Trạng thái | Hành động |
|----------|----|----|--------|--------|--------|-----------|----------|

**Hành động trên mỗi sân:**
- 👁️ Xem chi tiết (View button)
- 👁️‍🗨️ Kích hoạt/Ẩn (Toggle button - đổi màu)
- ✏️ Chỉnh sửa (Edit button - modal)
- 🗑️ Xóa (Delete button - confirm)

**Filter & Tìm kiếm:**
- Tìm kiếm theo tên sân
- Lọc theo trạng thái (Tất cả, Hoạt động, Đã ẩn)
- Lọc theo cơ sở sân (dropdown)
- Nút Reset filter

**Batch Actions:**
- Chọn nhiều sân (checkbox)
- Chọn trạng thái cần cập nhật
- Nút "Áp dụng" (enabled khi có sân được chọn)

**Phân trang:** Bootstrap pagination (15 sân/trang)

### 2. Chi tiết sân (`/admin/courts/{id}`)

**Thông tin cơ bản:**
- ID, Tên sân
- Cơ sở sân (link)
- Chủ sân
- Thể thao
- Địa chỉ
- Trạng thái (Badge)
- Có thể đặt online? (Badge)
- Ngày tạo & cập nhật

**Hành động:**
- Kích hoạt / Ẩn (toggle button)
- Xóa sân (delete button)

**Sidebar (Thống kê):**
- Tổng ca giờ
- Tổng lịch đặt
- Lịch đặt chưa hoàn thành

**Liên kết nhanh:**
- Xem sân khác cùng cơ sở
- Quản lý cơ sở sân

**Ca giờ hoạt động:**
- Bảng: Ngày, Thời gian, Giá
- Hiển thị tối đa 10 ca, count số ca còn lại

**Lịch đặt gần đây:**
- Bảng: ID, Khách hàng, Ngày, Thời gian, Trạng thái
- Hiển thị 10 đơn gần nhất
- Sort by created_at DESC

### 3. Trạng thái Sân

**Status enum:** 'active' | 'inactive'

**Active (Hoạt động):**
- Badge: 🟢 Màu xanh
- Text: "✓ Hoạt động"
- Nút: "👁️‍🗨️ Ẩn" (màu vàng)
- Users có thể thấy & đặt sân

**Inactive (Đã ẩn):**
- Badge: 🔴 Màu đỏ
- Text: "✗ Đã ẩn"
- Nút: "✓ Kích hoạt" (màu xanh)
- Users KHÔNG thể thấy, KHÔNG thể đặt
- Booking cũ vẫn giữ

### 4. Business Logic

#### Khi ẩn sân (toggle inactive):
```
- Update status = 'inactive'
- Booking trong tương lai KHÔNG bị hủy
- Hiển thị alert: "Sân đã được ẩn. (X booking vẫn được giữ)"
- Người dùng không thể tạo booking mới
```

#### Khi kích hoạt sân (toggle active):
```
- Update status = 'active'
- Sân được hiển thị lại
- Cho phép đặt sân mới
```

#### Xóa sân:
```
- Kiểm tra: Có booking không?
- Nếu có → Error: "Không thể xóa, vẫn có X lịch đặt"
- Nếu không → Delete successfully
```

#### Khi user đặt sân:
```
1. Kiểm tra court.status = 'active' ✓
2. Kiểm tra court.is_bookable_online = true ✓
3. Nếu inactive → Error 403
4. Nếu is_bookable_online = false → Error 403
```

## 🔒 Middleware & Quyền

- **Route protection:** `middleware(['auth', 'admin'])`
- **Chỉ Admin** được phép:
  - Xem danh sách sân
  - Thay đổi trạng thái
  - Xóa sân
  - Chỉnh sửa
- **Owner KHÔNG** được phép self-manage (web routes block)

## 📊 Database

### Bảng courts
```
id (PK)
venue_id (FK)
name (string)
status (enum: 'active', 'inactive') [default: 'active']
is_bookable_online (boolean) [default: true]
created_at, updated_at
```

### Status query
```php
// Danh sách sân active
Court::active()->get();

// Danh sách sân inactive
Court::inactive()->get();

// Kiểm tra có thể đặt không
if ($court->canBeBooked()) { ... }

// Kiểm tra có booking tương lai
if ($court->hasFutureBookings()) { ... }
```

## 🎨 UI/UX

- **Framework:** Bootstrap 5
- **Icons:** FontAwesome 6.4
- **Design:** Admin dashboard theme (green primary color)
- **Responsive:** Mobile-friendly
- **Alerts:**
  - Success: Green alert
  - Error: Red alert
  - Info: Blue alert
- **Confirmations:** JavaScript confirm() trước khi action

## 📍 Navigation

**Admin Sidebar Menu:**
```
📊 Tổng quan → /admin/dashboard
👥 Quản lý người dùng → /admin/users
✓ Đăng ký chủ sân → /admin/owner-registrations
🏢 Quản lý cơ sở → /admin/venues
[NEW] 📋 Quản lý sân → /admin/courts ← NEW
📅 Quản lý đặt sân → /admin/bookings
```

## 🧪 Testing Checklist

- [ ] Truy cập /admin/courts → Danh sách sân
- [ ] Lọc theo tên → Chỉ hiển thị sân khớp
- [ ] Lọc theo trạng thái → Chỉ hiển thị status cần
- [ ] Lọc theo cơ sở → Chỉ hiển thị sân của venue
- [ ] Reset filter → Hiển thị tất cả sân
- [ ] Click "Xem chi tiết" → Mở show page
- [ ] Click "Ẩn" sân active → Đổi thành inactive
- [ ] Click "Kích hoạt" sân inactive → Đổi thành active
- [ ] Confirm alert trước khi toggle
- [ ] Click "Edit" → Modal chỉnh sửa
- [ ] Click "Delete" sân có booking → Error
- [ ] Click "Delete" sân không booking → Success
- [ ] Chọn checkbox → Enable batch button
- [ ] Batch update status → Cập nhật hàng loạt
- [ ] User truy cập sân inactive → 404 error
- [ ] User không thể đặt sân inactive → Error 403
- [ ] Show page hiển thị stats, slots, bookings

## 📝 Code Quality

✅ Route Model Binding: `Court $court`
✅ Validation: Request validation
✅ Error handling: Try-catch, abort()
✅ Responsive design: Bootstrap grid
✅ Security: Middleware auth + admin
✅ Comments: PHPDoc comments
✅ Naming: PSR-12 standard
✅ Database: Efficient queries (with, eager loading)

## 🚀 Deployment

1. **Run migration** (nếu cần):
   ```bash
   php artisan migrate
   ```

2. **Clear cache**:
   ```bash
   php artisan cache:clear
   php artisan view:clear
   php artisan route:clear
   ```

3. **Access**:
   ```
   https://yourapp.com/admin/courts
   ```

## 📚 Related Files

- [AdminCourtController](../app/Http/Controllers/Web/AdminCourtController.php)
- [Court Model](../app/Models/Court.php)
- [Courts Index View](../resources/views/admin/courts/index.blade.php)
- [Courts Show View](../resources/views/admin/courts/show.blade.php)
- [Admin Layout](../resources/views/admin/layouts/app.blade.php)
- [Web Routes](../routes/web.php)

## 🔗 API Connection

Admin Web Interface dùng riêng (không dùng API). Nếu cần tích hợp API:
- API endpoints: `/api/admin/courts` (xem file ADMIN_VENUES_MANAGEMENT.md)
- Web interface: `/admin/courts` (hiện tại)

---

**Created:** 2026-06-16
**Version:** 1.0
**Status:** ✅ Ready for production
