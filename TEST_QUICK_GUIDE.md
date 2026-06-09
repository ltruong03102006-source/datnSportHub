# 🚀 Quick Test Guide - Owner API

## 3 Cách Test Owner API

---

## 1️⃣ POSTMAN (Dễ nhất) ⭐

### Setup (1 lần)
```
1. Import: postman_owner_api_collection.json
2. Set variables:
   - base_url: http://localhost:8000
   - owner_token: (để trống, lấy sau khi login)
```

### Test Flow (5 bước)
```
1️⃣  Register
   POST /api/owner/register
   Body: name, email, password, phone
   
2️⃣  Admin duyệt (Terminal)
   php artisan tinker
   >>> $user = User::where('email', 'owner@test.com')->first();
   >>> $user->update(['role' => 'owner']);

3️⃣  Login
   POST /api/owner/login
   Body: email, password
   → Copy token to variable "owner_token"

4️⃣  Test protected endpoint
   GET /api/owner/me
   Header: Authorization: Bearer {{owner_token}}
   ✅ Thành công!

5️⃣  Test khác (venues, courts, bookings)
   Xem folder "3-5" trong collection
```

**Tài liệu**: [POSTMAN_TESTING_GUIDE.md](POSTMAN_TESTING_GUIDE.md)

---

## 2️⃣ PowerShell Script (Windows)

### Run (1 lệnh)
```powershell
# Mở PowerShell
cd d:\duantotnghiep\DATN2\datnSportHub
.\test_owner_api.ps1
```

### Script sẽ tự động:
✅ Đăng ký → Login → Test tất cả endpoints → Logout

Xem file: [test_owner_api.ps1](test_owner_api.ps1)

---

## 3️⃣ Bash Script (Linux/Mac)

### Run (1 lệnh)
```bash
# Mở Terminal
cd /path/to/datnSportHub
chmod +x test_owner_api.sh
./test_owner_api.sh
```

Xem file: [test_owner_api.sh](test_owner_api.sh)

---

## 🎯 Chọn Cách Nào?

| Cách | Dễ? | Tốc độ | Tương tác | Dùng khi |
|------|-----|-------|----------|---------|
| **Postman** | ⭐⭐⭐ | Chậm | Cao | Dev/Debug, test UI |
| **PowerShell** | ⭐⭐ | Nhanh | Thấp | Windows, test full flow |
| **Bash** | ⭐⭐ | Nhanh | Thấp | Linux/Mac, automation |

👉 **Khuyến nghị**: **Postman** vì dễ, hiển thị đẹp, và có thể save variables

---

## ⚡ Quick Test (Postman - 5 phút)

### Step 1: Setup
- Mở Postman
- Import: `postman_owner_api_collection.json`
- Set `base_url = http://localhost:8000`

### Step 2: Register
```
Folder: 1. Public - Owner Auth
Click: Register - Đăng ký chủ sân
Click Send
✅ Nhận token (role=user)
```

### Step 3: Admin Duyệt
```
Terminal:
php artisan tinker
>>> $user = User::where('email', 'owner@test.com')->first();
>>> $user->update(['role' => 'owner']);
>>> exit
```

### Step 4: Login & Save Token
```
Folder: 1. Public - Owner Auth
Click: Login - Đăng nhập
Click Send
✅ Nhận token mới (role=owner)

Copy token → Set variable "owner_token"
```

### Step 5: Test Protected
```
Folder: 2. Protected - Auth Management
Click: Get Me - Lấy thông tin
Click Send
✅ Xem được thông tin chủ sân
```

✅ **DONE!** Middleware hoạt động!

---

## 📝 Postman Collection Structure

```
├─ 1. Public - Owner Auth
│  ├─ Register
│  └─ Login
│
├─ 2. Protected - Auth Management
│  ├─ Get Me
│  ├─ Change Password
│  └─ Logout
│
├─ 3. Venues Management
│  ├─ Get Sports
│  ├─ List Venues
│  ├─ Create Venue
│  ├─ Get Venue Detail
│  ├─ Update Venue
│  └─ Delete Venue
│
├─ 4. Courts Management
│  ├─ List Courts
│  ├─ Create Court
│  ├─ Update Court
│  ├─ Get Court Time Slots
│  └─ Delete Court
│
├─ 5. Bookings Management
│  ├─ List All Bookings
│  ├─ Get Bookings Stats
│  ├─ Get Booking Detail
│  ├─ Get Venue Bookings
│  └─ Get Court Bookings
│
└─ 6. Error Testing
   ├─ Test - Không có token (401)
   ├─ Test - Token user (403)
   ├─ Test - Venue không tồn tại (404)
   └─ Test - Email đã tồn tại (422)
```

---

## 🔑 Environment Variables

```
base_url = http://localhost:8000
owner_token = (set từ Login response)
user_token = (if testing user endpoints)
venue_id = (set từ Create Venue response)
court_id = (set từ Create Court response)
booking_id = (if testing booking details)
```

---

## 🧪 Testing Checklist

### Auth
- [ ] Register → 201 ✅
- [ ] Admin approve (Tinker)
- [ ] Login → 200, token ✅
- [ ] Get Me → 200, show user ✅

### Venues
- [ ] Create → 201 ✅
- [ ] List → 200 ✅
- [ ] Get detail → 200 ✅
- [ ] Update → 200 ✅

### Courts
- [ ] Create → 201 ✅
- [ ] List → 200 ✅
- [ ] Get time slots → 200 ✅
- [ ] Update → 200 ✅

### Bookings
- [ ] List → 200 ✅
- [ ] Get stats → 200 ✅
- [ ] Get by venue → 200 ✅
- [ ] Get by court → 200 ✅

### Errors
- [ ] No token → 401 ✅
- [ ] Invalid role → 403 ✅
- [ ] Not found → 404 ✅
- [ ] Bad data → 422 ✅

---

## 💡 Tips

### Tip 1: Auto-set variables (Postman)
Vào tab "Tests" của Login request, thêm:
```javascript
if (pm.response.code === 200) {
  var data = pm.response.json();
  pm.environment.set("owner_token", data.token);
}
```

### Tip 2: Check status code
```javascript
pm.test("Status code is 200", function () {
  pm.expect(pm.response.code).to.equal(200);
});
```

### Tip 3: Reuse request
Copy request → Rename → Modify URL/body → Test

### Tip 4: View history
Postman History → tìm request cũ

---

## ❌ Troubleshooting

### ❌ 401 Unauthorized
**Nguyên nhân**: Không có token hoặc token sai
**Giải pháp**: Login lại, copy token mới vào variable

### ❌ 403 Forbidden
**Nguyên nhân**: Không phải owner hoặc chưa được duyệt
**Giải pháp**: Kiểm tra role = 'owner' (Tinker)

### ❌ 404 Not Found
**Nguyên nhân**: Resource ID sai hoặc không sở hữu
**Giải pháp**: List trước để lấy đúng ID

### ❌ 422 Validation Error
**Nguyên nhân**: Dữ liệu input không hợp lệ
**Giải pháp**: Kiểm tra error message, điền đủ required fields

### ❌ 500 Server Error
**Nguyên nhân**: Bug trong code
**Giải pháp**: Xem `storage/logs/laravel.log`

---

## 📚 Documentation

- 📖 [POSTMAN_TESTING_GUIDE.md](POSTMAN_TESTING_GUIDE.md) - Chi tiết từng endpoint
- 🏗️ [OWNER_API_ARCHITECTURE.md](OWNER_API_ARCHITECTURE.md) - System design
- 📝 [OWNER_API.md](OWNER_API.md) - API reference
- 🚀 [OWNER_API_QUICKSTART.md](OWNER_API_QUICKSTART.md) - Quick start

---

**Ready to test? 🎯**
1. Mở Postman
2. Import `postman_owner_api_collection.json`
3. Follow Quick Test (5 bước)
4. ✅ Done!

Enjoy! 🚀
