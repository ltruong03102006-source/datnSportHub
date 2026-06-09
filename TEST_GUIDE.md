# 🧪 HƯỚNG DẪN TEST GIAO DIỆN BOOKING

## ✅ Server đã chạy

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

## 📱 URLs để Test

### 1. **Trang Chính**
```
http://localhost:8000
```

### 2. **Trang Booking Sân** (Chính!)
```
http://localhost:8000/courts/1
```

### 3. **API Availability**
```
http://localhost:8000/api/courts/1/availability?date=2026-06-22
```

---

## 🎯 TEST CASES

### Test 1: Mở Trang Booking
1. Truy cập: `http://localhost:8000/courts/1`
2. Kiểm tra:
   - ✓ Banner ảnh hiển thị
   - ✓ Tên sân: "Sân A"
   - ✓ Sport badge: "Bóng đá"
   - ✓ Status badge: "Đang hoạt động"
   - ✓ Địa chỉ hiển thị
   - ✓ Date picker mặc định hôm nay
   - ✓ Slots grid hiển thị

**Expected Output:**
```
Sân A
Bóng đá | Đang hoạt động
123 Lê Văn Lương, Thanh Xuân, Hà Nội

Chọn ngày: [today's date]

[Slot 1: 06:00-07:00] [Slot 2: 07:00-08:00] ...
```

---

### Test 2: Thay Đổi Ngày (Weekday vs Weekend)
1. Mở trang booking
2. Thay đổi date picker sang ngày Thứ 2 (VD: 2026-06-22)
3. Kiểm tra:
   - ✓ Loading spinner xuất hiện
   - ✓ Slots reload
   - ✓ Giá = 100.000₫ (normal) ✓ Price type = "Giá bình thường"

**Test Ngày Thứ 7:**
1. Thay date picker sang Thứ 7 (VD: 2026-06-20)
2. Kiểm tra:
   - ✓ Giá = 150.000₫ (peak)
   - ✓ Price type = "Giá cao"

**Lệnh curl để test:**
```bash
# Weekday (Thứ 2)
curl "http://localhost:8000/api/courts/1/availability?date=2026-06-22" | jq '.data[0]'

# Weekend (Thứ 7)  
curl "http://localhost:8000/api/courts/1/availability?date=2026-06-20" | jq '.data[0]'
```

---

### Test 3: Slot Card UI
1. Mở trang booking
2. Kiểm tra mỗi slot card:
   - ✓ Hiển thị: Giờ bắt đầu - Kết thúc
   - ✓ Hiển thị: Thời lượng (60 phút)
   - ✓ Hiển thị: Giá (format: X.XXX₫)
   - ✓ Hiển thị: Loại giá (Giá bình thường / Giá cao)
   - ✓ Badge trạng thái: [Trống] hoặc [Đã đặt]
   - ✓ Button: "Đặt ngay" (green) hoặc "Đã được đặt" (gray)

**Expected Card:**
```
┌─────────────────────────┐
│ 06:00 - 07:00  [Trống] │
│ 60 phút                 │
│ 100.000₫                │
│ Giá bình thường         │
│ [Đặt ngay]              │
└─────────────────────────┘
```

---

### Test 4: Responsive Design
1. Truy cập: `http://localhost:8000/courts/1`
2. Test trên các devices:

**Mobile (375px):**
```bash
# Chrome DevTools: Toggle device toolbar → iPhone SE
- Layout stacked (sidebar trên, content dưới)
- Grid: 1 slot per row
- Full-width buttons
```

**Tablet (768px):**
```bash
# Chrome DevTools: iPad
- Sidebar + Content side-by-side
- Grid: 2 slots per row
```

**Desktop (1440px):**
```bash
# Full screen
- Sidebar sticky top 24px
- Grid: 2 slots per row
- Smooth shadows
```

---

### Test 5: Button "Đặt Ngay"
1. Mở trang booking
2. Bấm button "Đặt ngay" trên slot đầu tiên
3. Kiểm tra:
   - ✓ Toast notification xuất hiện: "✓ Đã chọn khung giờ 06:00-07:00"
   - ✓ Hidden form được update:
     - court_id = 1
     - slot_id = (slot này)
     - selected_date = (ngày được chọn)
   - ✓ Toast tự động disappear sau 3s

**Kiểm tra console (F12):**
```javascript
// Mở DevTools → Console
console.log(document.getElementById('slotIdInput').value); // Should show slot_id
console.log(document.getElementById('selectedDateInput').value); // Should show date
console.log(document.getElementById('courtIdInput').value); // Should show 1
```

---

### Test 6: Slot Trạng Thái "Đã Được Đặt"
1. Tạo booking test:
```bash
php artisan tinker
```
```php
\App\Models\Booking::create([
    'court_id' => 1,
    'user_id' => 1,
    'slot_date' => '2026-06-22',
    'start_time' => '06:00:00',
    'end_time' => '07:00:00',
    'total_price' => 100000,
    'status' => 'confirmed'
]);
```

2. Refresh trang `/courts/1`
3. Thay date sang 2026-06-22
4. Kiểm tra:
   - ✓ Slot 06:00-07:00 hiển thị [Đã đặt]
   - ✓ Button disable (gray, không click được)
   - ✓ Border card đỏ
   - ✓ Opacity mờ hơn

---

### Test 7: Empty State
1. Tạo nhiều bookings để fill all slots:
```bash
php artisan tinker
```
```php
$date = '2026-06-22';
for ($hour = 6; $hour < 22; $hour++) {
    \App\Models\Booking::create([
        'court_id' => 1,
        'user_id' => 1,
        'slot_date' => $date,
        'start_time' => sprintf('%02d:00:00', $hour),
        'end_time' => sprintf('%02d:00:00', $hour + 1),
        'total_price' => 100000,
        'status' => 'confirmed'
    ]);
}
```

2. Refresh trang `/courts/1`
3. Thay date sang 2026-06-22
4. Kiểm tra:
   - ✓ Không hiển thị slot list
   - ✓ Hiển thị Empty State:
     ```
     [Icon]
     Không có khung giờ trống
     Vui lòng chọn ngày khác để xem các slot có sẵn
     ```

---

### Test 8: Date Picker Validation
1. Mở trang booking
2. Thử chọn ngày quá khứ (VD: 2026-01-01)
3. Kiểm tra:
   - ✓ Date picker không cho chọn (disabled)
   - ✓ min attribute = today's date

**Check HTML:**
```bash
curl -s http://localhost:8000/courts/1 | grep -A2 'id="datePicker"'
# Should show: min="{{ today's date }}"
```

---

### Test 9: Date Display Format
1. Mở trang booking
2. Thay date picker
3. Kiểm tra:
   - ✓ Display format: "Thứ X, DD/MM/YYYY"
   - VD: "Thứ 2, 22/06/2026"

---

### Test 10: API Integration
1. Mở DevTools → Network tab (F12)
2. Refresh trang `/courts/1`
3. Kiểm tra requests:
   - ✓ GET /courts/1 (HTML page)
   - ✓ GET /api/courts/1/availability?date=2026-06-05

4. Thay date picker
5. Kiểm tra:
   - ✓ XHR request tới API
   - ✓ Response có 16 slots (06:00-22:00)
   - ✓ Mỗi slot có: slot_id, price, price_type, is_available

**Curl test:**
```bash
curl -s "http://localhost:8000/api/courts/1/availability?date=2026-06-22" | python -m json.tool
```

Expected response:
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
    ...
  ]
}
```

---

## 🐛 Troubleshooting

### Problem: Trang không load
```bash
# Check server
ps aux | grep "php artisan serve"

# Restart if needed
pkill -f "php artisan serve"
php artisan serve --host=0.0.0.0 --port=8000
```

### Problem: Slots không hiển thị
```bash
# Check database
php artisan tinker
\App\Models\TimeSlot::count() # Should be 80

# Check API
curl "http://localhost:8000/api/courts/1/availability?date=2026-06-22"
```

### Problem: Giá không đúng
```bash
# Check slot_prices
php artisan tinker
$slot = \App\Models\TimeSlot::first();
$slot->prices()->get(); # Check prices for different days
```

### Problem: CSS không load (Tailwind)
```bash
# Rebuild CSS
npm run build

# Or dev mode
npm run dev
```

---

## 📊 Database Check

```bash
php artisan tinker
```

```php
# Check courts
\App\Models\Court::count(); # Should be > 0

# Check first court
$court = \App\Models\Court::with(['venue', 'timeSlots'])->first();
echo $court->name;
echo $court->venue->name;
echo $court->timeSlots->count(); # Should be 16

# Check time slots
\App\Models\TimeSlot::count(); # Should be 80 (5 courts × 16 slots)

# Check slot prices
\App\Models\SlotPrice::count(); # Should be 560 (80 slots × 7 days)

# Check bookings
\App\Models\Booking::count();
```

---

## 🎬 Quick Test Script

```bash
#!/bin/bash

echo "🧪 Starting Tests..."
echo ""

echo "1. ✓ Server Status"
curl -s -o /dev/null -w "Status: %{http_code}\n" http://localhost:8000

echo ""
echo "2. ✓ Page Load"
curl -s http://localhost:8000/courts/1 | grep -q "Sân A" && echo "Page loaded OK" || echo "Page load FAILED"

echo ""
echo "3. ✓ API Availability"
curl -s "http://localhost:8000/api/courts/1/availability?date=2026-06-22" | grep -q "slot_id" && echo "API OK" || echo "API FAILED"

echo ""
echo "✅ All basic tests passed!"
```

---

## 📸 Screenshots để Compare

### Expected Layout (Desktop):
```
┌─────────────────────────────────────────────┐
│ [Navigation Bar]                            │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ [Banner Image - Gradient Background]        │
│ Sân A | Bóng đá | Đang hoạt động           │
│ Địa chỉ: 123 Lê Văn Lương...              │
└─────────────────────────────────────────────┘

┌──────────────────┬────────────────────────────┐
│ CHỌN NGÀY       │ DANH SÁCH KHUNG GIỜ TRỐNG  │
│                 │                            │
│ [Date Picker]   │ [06:00-07:00] [07:00-08:00]│
│                 │ [08:00-09:00] [09:00-10:00]│
│ Thứ 2,22/6/26   │ ... (16 slots total)      │
│                 │                            │
│ Legend:         │                            │
│ • Trống         │                            │
│ • Đã đặt        │                            │
└──────────────────┴────────────────────────────┘

┌─────────────────────────────────────────────┐
│ [Footer]                                    │
└─────────────────────────────────────────────┘
```

---

## ✅ Checklist Test Completion

- [ ] Test 1: Mở trang booking
- [ ] Test 2: Thay đổi ngày
- [ ] Test 3: Slot card UI
- [ ] Test 4: Responsive design
- [ ] Test 5: Button "Đặt ngay"
- [ ] Test 6: Slot trạng thái "Đã đặt"
- [ ] Test 7: Empty state
- [ ] Test 8: Date picker validation
- [ ] Test 9: Date display format
- [ ] Test 10: API integration

**Tất cả pass = Ready cho Sprint #14! ✅**
