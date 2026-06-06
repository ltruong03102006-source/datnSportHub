# User Story #13: Court Booking Page

## Mô tả
Giao diện chọn ngày và khung giờ trống để đặt sân thể thao.

## Endpoint

```
GET /courts/{court}
```

### Ví dụ
```
http://localhost:8000/courts/1
```

## Các Component

### 1. **Court Info Section**
- Ảnh banner sân
- Tên sân
- Môn thể thao (badge)
- Trạng thái (active/inactive)
- Địa chỉ
- Thông tin: Giờ mở cửa, thời lượng slot

### 2. **Date Picker (Sidebar)**
- Input date mặc định ngày hiện tại
- Không cho chọn ngày quá khứ
- Hiển thị ngày được chọn (format: Thứ X, DD/MM/YYYY)
- Legend: Trống / Đã được đặt

### 3. **Slots List (Main)**
- Loading spinner khi fetch API
- Grid layout responsive (2 cột trên mobile, 2 cột tablet)
- Mỗi slot hiển thị:
  - Giờ bắt đầu - kết thúc
  - Thời lượng (60 phút)
  - Trạng thái (Trống/Đã được đặt)
  - Giá (format: X.XXX₫)
  - Loại giá (Giá bình thường / Giá cao)
  - Button (Đặt ngay / Đã được đặt)

### 4. **UI/UX**
- Responsive design (mobile, tablet, desktop)
- Smooth animations & transitions
- Toast notification khi bấm "Đặt ngay"
- Empty state khi không có slot
- Error handling với thông báo lỗi

## Files Tạo/Cập Nhật

### Controllers
- `app/Http/Controllers/Web/CourtBookingController.php` (new)
  - Method: `show(Court $court): View`
  - Load: venue, sport, timeSlots

### Views
- `resources/views/courts/booking.blade.php` (new)
  - Layout: Tailwind CSS
  - Components: Court info, date picker, slots grid, modals

### Routes
- `routes/web.php` (updated)
  - `GET /courts/{court}` → `CourtBookingController@show`

### Models
- Sử dụng: Court, TimeSlot, SlotPrice, Booking (via API)

## Features

✓ Date picker với validation (không quá khứ)  
✓ Fetch API availability theo ngày được chọn  
✓ Responsive grid layout  
✓ Loading state + Empty state + Error handling  
✓ Toast notification  
✓ Price formatting (VI locale)  
✓ Hidden form để chuẩn bị cho Sprint #14  

## Query Optimization

```php
$court->load([
    'venue' => fn($query) => $query->select('id', 'name', 'address', 'sport_id', 'banner'),
    'venue.sport' => fn($query) => $query->select('id', 'name'),
    'timeSlots' => fn($query) => $query->select('id', 'court_id', 'start_time', 'end_time', 'duration_minutes'),
]);
```

## Ví dụ Sử Dụng

### 1. Truy cập trang booking
```
http://localhost:8000/courts/1
```

### 2. Giao diện hiển thị:
```
┌─────────────────────────────────────────────────┐
│ [Banner Image]                                  │
│ Sân A | Bóng đá | Đang hoạt động              │
│ Địa chỉ: 123 Đường ABC, Quận 1                │
└─────────────────────────────────────────────────┘

┌─────────────────┬──────────────────────────────┐
│ CHỌN NGÀY       │ DANH SÁCH KHUNG GIỜ TRỐNG   │
│                 │                              │
│ [Date Picker]   │ ┌──────────────────────────┐│
│                 │ │ 06:00 - 07:00     [Trống]││
│ Thứ 2,22/6/26   │ │ 60 phút                   ││
│                 │ │ 100.000₫ (Giá bình thường)││
│ Legend:         │ │ [Đặt ngay]                ││
│ • Trống         │ └──────────────────────────┘│
│ • Đã được đặt   │ ┌──────────────────────────┐│
│                 │ │ 07:00 - 08:00     [Đặt] ││
│                 │ │ ... (more slots)          ││
│                 │ └──────────────────────────┘│
└─────────────────┴──────────────────────────────┘
```

### 3. Khi bấm "Đặt ngay"
- Lưu court_id, slot_id, selected_date
- Hiển thị toast: "✓ Đã chọn khung giờ 06:00-07:00"
- Sprint #14 sẽ redirect tới trang xác nhận: `/bookings/confirm?court_id=1&slot_id=1&date=2026-06-22`

## JavaScript API

### Hàm chính:

```javascript
// Fetch availability từ API
fetchAvailability(dateString)

// Render slots
renderSlots(slots)

// Tạo slot card
createSlotCard(slot)

// Khi bấm đặt
bookSlot(slotId, startTime, endTime, price)

// Hiển thị/ẩn loading
showLoading(show)

// Hiển thị error
showError(message)

// Hiển thị toast
showToast(message)
```

## Data Flow

```
┌──────────────────┐
│ Load Page        │
└────────┬─────────┘
         │
         ▼
┌──────────────────────────────┐
│ 1. Get Court với relationships
│    - venue.sport
│    - timeSlots
└────────┬─────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│ 2. Display Court Info        │
│    - banner, name, sport etc │
└────────┬─────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│ 3. Fetch Availability API    │
│    - date từ datePicker      │
│    - GET /api/courts/{id}/   │
│      availability?date=YYYY-MM-DD
└────────┬─────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│ 4. Render Slots              │
│    - Grid layout             │
│    - Show available/booked   │
└────────┬─────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│ 5. User Interaction          │
│    - Change date             │
│    - Click "Đặt ngay"        │
└────────┬─────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│ 6. Save Selection (Sprint #14)
│    - court_id                │
│    - slot_id                 │
│    - selected_date           │
└──────────────────────────────┘
```

## Responsive Design

### Mobile (< 768px)
- Sidebar ngăn cách (block layout)
- Grid: 1 slot per row
- Full width inputs

### Tablet (768px - 1024px)
- Sidebar + Main side-by-side
- Grid: 2 slots per row

### Desktop (> 1024px)
- Sidebar sticky top
- Grid: 2 slots per row
- Smooth shadows & transitions

## Performance

- Initial Load: ~3 API calls
- Date Change: 1 API call (availability)
- Lazy loading: Slots render thực tế khi fetch xong
- CSS: Tailwind purged (production)
- JS: Vanilla (no jQuery)

## Chuẩn bị cho Sprint #14

Khi bấm "Đặt ngay", form hidden sẽ được fill:

```html
<form id="bookingForm" method="POST">
    <input type="hidden" name="court_id" value="1">
    <input type="hidden" name="slot_id" value="5">
    <input type="hidden" name="selected_date" value="2026-06-22">
</form>
```

Sprint #14 sẽ implement:
1. Booking confirmation page
2. Payment gateway integration
3. Create booking API endpoint
4. Booking success page

## Error Handling

- **Network Error**: "Không thể tải dữ liệu. Vui lòng thử lại."
- **No Slots**: "Không có khung giờ trống. Vui lòng chọn ngày khác"
- **Invalid Date**: Disabled ngày quá khứ ở date picker
- **API Error**: Show error message + console log

## Browser Compatibility

- Chrome ✓
- Firefox ✓
- Safari ✓
- Edge ✓
- Mobile browsers ✓

## Testing

### Test 1: Page Load
1. Navigate to `/courts/1`
2. Verify: Court info displayed
3. Verify: Date picker focused on today
4. Verify: Slots loaded automatically

### Test 2: Date Change
1. Change date in date picker
2. Verify: Date display updated
3. Verify: Slots reloaded from API
4. Verify: Loading spinner shown during fetch

### Test 3: Slot Selection
1. Click "Đặt ngay" button
2. Verify: Toast notification shown
3. Verify: Hidden form updated with values
4. Verify: Console logs slot info

### Test 4: Mobile Responsive
1. Resize to 375px (mobile)
2. Verify: Sidebar stacks above content
3. Verify: Grid shows 1 slot per row
4. Verify: Buttons full width

### Test 5: Empty State
1. Select future date with no bookings
2. All slots should show "Đặt ngay"
3. Select date with all bookings
4. Show "Không có khung giờ trống"

## Lưu ý

- Tất cả prices trong DB là integer (VND)
- Time format: HH:MM (không seconds)
- Price display dùng `toLocaleString('vi-VN')`
- Toast auto-dismiss sau 3s
- Date validation ở backend (API)
