# User Story #12: Court Availability API

## Mô tả
API xem lịch trống của sân thể thao theo ngày được chọn.

## Endpoint

```
GET /api/courts/{court}/availability?date=YYYY-MM-DD
```

## Parameters

| Tham số | Kiểu | Bắt buộc | Mô tả |
|---------|------|---------|-------|
| `date` | string | ✓ | Ngày cần xem (định dạng: YYYY-MM-DD) |
| `court` | int | ✓ | ID của sân |

## Validation

- `date` bắt buộc
- `date` phải đúng định dạng YYYY-MM-DD
- `date` không được trong quá khứ

## Response

```json
{
  "data": [
    {
      "slot_id": 1,
      "court_id": 1,
      "start_time": "06:00",
      "end_time": "07:00",
      "duration_minutes": 60,
      "price": 100000,
      "price_type": "normal",
      "is_available": true
    },
    {
      "slot_id": 2,
      "court_id": 1,
      "start_time": "07:00",
      "end_time": "08:00",
      "duration_minutes": 60,
      "price": 100000,
      "price_type": "normal",
      "is_available": false
    }
  ]
}
```

## Ví dụ cURL

### Kiểm tra lịch trống cho sân ID 1 ngày 22/06/2026

```bash
curl -X GET "http://localhost:8000/api/courts/1/availability?date=2026-06-22" \
  -H "Accept: application/json"
```

### Response thành công (200)

```json
{
  "data": [
    {
      "slot_id": 1,
      "court_id": 1,
      "start_time": "06:00",
      "end_time": "07:00",
      "duration_minutes": 60,
      "price": 100000,
      "price_type": "normal",
      "is_available": true
    }
  ]
}
```

### Response lỗi (422)

Thiếu date parameter:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "date": ["Vui lòng nhập ngày"]
  }
}
```

Ngày trong quá khứ:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "date": ["Không thể xem lịch trong quá khứ"]
  }
}
```

## Logic

1. Nhận `court_id` và `date`
2. Lấy tất cả `time_slots` của court
3. Join với `slot_prices` để lấy giá dựa vào ngày trong tuần
4. Kiểm tra xem có booking nào cho ngày và time slot đó không
5. Nếu booking tồn tại và status ≠ 'cancelled' → `is_available = false`
6. Ngược lại → `is_available = true`

## Query Optimization

- Sử dụng **eager loading** với `.with()` để tránh N+1 queries
- Lấy bookings một lần thay vì query trong vòng lặp
- Join slot_prices trong eager loading

## Files Tạo/Cập Nhật

### Models
- `app/Models/Booking.php` (new)
- `app/Models/Court.php` (updated - thêm relationship bookings)

### Service Layer
- `app/Services/AvailabilityService.php` (new)
  - Method: `getAvailability(Court $court, Carbon $date): Collection`

### HTTP
- `app/Http/Controllers/Api/CourtAvailabilityController.php` (new)
  - Method: `show(Court $court, GetAvailabilityRequest $request)`
- `app/Http/Requests/GetAvailabilityRequest.php` (new)
- `app/Http/Resources/SlotAvailabilityResource.php` (new)

### Routes
- `routes/api.php` (updated)
  - `GET /api/courts/{court}/availability`

## Testing

### Test 1: Ngày thứ 2 (weekday) - tất cả slots trống

```bash
curl "http://localhost:8000/api/courts/1/availability?date=2026-06-22"
```

Kết quả: Tất cả slots `is_available = true`, price = 100000 (normal)

### Test 2: Ngày thứ 7 (weekend) - tất cả slots trống

```bash
curl "http://localhost:8000/api/courts/1/availability?date=2026-06-20"
```

Kết quả: Tất cả slots `is_available = true`, price = 150000 (peak)

### Test 3: Slot có booking

1. Tạo booking cho 06:00-07:00:
```php
Booking::create([
  'court_id' => 1,
  'user_id' => 1,
  'slot_date' => '2026-06-22',
  'start_time' => '06:00:00',
  'end_time' => '07:00:00',
  'total_price' => 100000,
  'status' => 'confirmed'
]);
```

2. Kiểm tra API:
```bash
curl "http://localhost:8000/api/courts/1/availability?date=2026-06-22"
```

Kết quả: Slot 1 (06:00-07:00) có `is_available = false`

### Test 4: Booking bị cancelled (vẫn có sẵn)

```php
Booking::create([
  'court_id' => 1,
  'user_id' => 1,
  'slot_date' => '2026-06-22',
  'start_time' => '07:00:00',
  'end_time' => '08:00:00',
  'total_price' => 100000,
  'status' => 'cancelled'
]);
```

Kết quả: Slot 2 vẫn có `is_available = true` (vì status = cancelled)

### Test 5: Validation - ngày trong quá khứ

```bash
curl "http://localhost:8000/api/courts/1/availability?date=2026-01-01"
```

Kết quả: 422 error với message "Không thể xem lịch trong quá khứ"

## Performance

- Queries per request: **3-4**
  - 1 query: Lấy court
  - 1 query: Lấy time_slots + prices (eager loaded)
  - 1 query: Lấy bookings
  - 1 query: Route model binding (implicit)

## Lưu ý

- API trả về tất cả time slots của court, không phân trang
- Time format là HH:MM (không bao gồm seconds)
- Price luôn là integer (VND)
- Status booking: pending, confirmed, completed, cancelled, rejected
- Chỉ status "cancelled" không tính là booking
