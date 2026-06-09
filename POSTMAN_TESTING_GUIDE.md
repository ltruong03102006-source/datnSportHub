# Hướng Dẫn Test Owner API Bằng Postman

## 📥 Setup Postman

### Step 1: Import Collection
1. Mở Postman
2. Click **"Import"** (góc trái)
3. Chọn **"Upload Files"** hoặc **"Link"**
4. Tìm file `postman_owner_api_collection.json`
5. Click **Import**

**Hoặc**: Copy link collection → Postman sẽ tự tải về

### Step 2: Setup Variables
1. Click vào **Collection** → **Owner API - Sport Hub**
2. Chọn tab **"Variables"**
3. Điền giá trị:
   - `base_url`: `http://localhost:8000`
   - `owner_token`: (để trống, sẽ điền sau khi login)
   - `user_token`: (để trống)
   - `venue_id`: (để trống, sẽ lấy sau khi tạo sân)
   - `court_id`: (để trống)
   - `booking_id`: (để trống)
4. Click **Save**

---

## 🧪 Testing Step-by-Step

### **Phase 1: Đăng Ký & Admin Approval**

#### Step 1.1: Đăng ký chủ sân
```
Folder: 1. Public - Owner Auth
Request: Register - Đăng ký chủ sân
Method: POST
URL: {{base_url}}/api/owner/register
```

**Body (raw JSON)**:
```json
{
  "name": "Nguyễn Văn A",
  "email": "owner@test.com",
  "password": "password123",
  "confirm_password": "password123",
  "phone": "0901234567"
}
```

**Expected Response (201)**:
```json
{
  "message": "Đăng ký chủ sân thành công. Vui lòng chờ duyệt từ admin",
  "token": "1|xxxxxxxxxxxxx",
  "user": {
    "id": 1,
    "name": "Nguyễn Văn A",
    "email": "owner@test.com",
    "role": "user",
    "status": "active"
  }
}
```

**Lưu ý**: `role: "user"` (chưa phải owner)

---

#### Step 1.2: Admin duyệt (Tinker)
Mở terminal → chạy Tinker:
```bash
php artisan tinker
```

```php
>>> $user = User::where('email', 'owner@test.com')->first();
>>> $user->update(['role' => 'owner']);
>>> exit
```

**Kiểm tra**:
```php
>>> $user->role  // Should be: "owner"
```

---

### **Phase 2: Đăng Nhập & Lấy Token**

#### Step 2.1: Đăng nhập
```
Folder: 1. Public - Owner Auth
Request: Login - Đăng nhập
Method: POST
URL: {{base_url}}/api/owner/login
```

**Body (raw JSON)**:
```json
{
  "email": "owner@test.com",
  "password": "password123"
}
```

**Expected Response (200)**:
```json
{
  "message": "Đăng nhập thành công",
  "token": "2|xxxxxxxxxxxxx",
  "user": {
    "id": 1,
    "name": "Nguyễn Văn A",
    "email": "owner@test.com",
    "role": "owner",
    "status": "active"
  }
}
```

✅ Giờ `role: "owner"`

---

#### Step 2.2: Lưu Token vào Postman
1. Copy token từ response: `2|xxxxxxxxxxxxx`
2. Click vào **Environment/Variables** (góc phải)
3. Tìm `owner_token` → paste token vào **Current Value**
4. Click **Save**

**Hoặc** - Set biến tự động:
```javascript
// Trong Tests tab của Login request
var data = pm.response.json();
pm.environment.set("owner_token", data.token);
```

---

### **Phase 3: Test Protected Endpoints**

#### Step 3.1: Lấy thông tin chủ sân
```
Folder: 2. Protected - Auth Management
Request: Get Me - Lấy thông tin
Method: GET
URL: {{base_url}}/api/owner/me
Header: Authorization: Bearer {{owner_token}}
```

**Response**:
```json
{
  "user": {
    "id": 1,
    "name": "Nguyễn Văn A",
    "email": "owner@test.com",
    "role": "owner",
    "status": "active"
  }
}
```

✅ Middleware kiểm tra thành công

---

### **Phase 4: Quản Lý Sân Vận Động (Venue)**

#### Step 4.1: Lấy danh sách thể thao
```
Folder: 3. Venues Management
Request: Get Sports - Danh sách thể thao
Method: GET
URL: {{base_url}}/api/owner/sports
Header: Authorization: Bearer {{owner_token}}
```

**Response**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Bóng đá",
      "description": "Bóng đá"
    },
    {
      "id": 2,
      "name": "Bóng chuyền",
      "description": "Bóng chuyền"
    }
  ]
}
```

📝 **Note**: Lưu `sport_id` (thường là 1)

---

#### Step 4.2: Tạo sân mới
```
Folder: 3. Venues Management
Request: Create Venue - Tạo sân
Method: POST
URL: {{base_url}}/api/owner/venues
```

**Headers**:
```
Content-Type: application/json
Authorization: Bearer {{owner_token}}
```

**Body (raw JSON)**:
```json
{
  "sport_id": 1,
  "name": "Sân Bóng Đá Minh Châu",
  "address": "123 Đường ABC, Hà Nội",
  "lat": 21.0285,
  "lng": 105.8542,
  "description": "Sân bóng đá hiện đại"
}
```

**Response (201)**:
```json
{
  "message": "Tạo sân thành công. Sân của bạn đang chờ duyệt.",
  "data": {
    "id": 1,
    "owner_id": 1,
    "sport_id": 1,
    "name": "Sân Bóng Đá Minh Châu",
    "address": "123 Đường ABC, Hà Nội",
    "lat": "21.0285",
    "lng": "105.8542",
    "description": "Sân bóng đá hiện đại",
    "status": "pending",
    "created_at": "2026-06-09T10:30:00",
    "updated_at": "2026-06-09T10:30:00"
  }
}
```

✅ **Lưu `venue_id` từ response** (thường là 1)

---

#### Step 4.3: Lấy danh sách sân
```
Folder: 3. Venues Management
Request: List Venues - Danh sách sân
Method: GET
URL: {{base_url}}/api/owner/venues
Header: Authorization: Bearer {{owner_token}}
```

**Response**: Array các sân của chủ sân này

---

#### Step 4.4: Lấy chi tiết sân
```
Folder: 3. Venues Management
Request: Get Venue Detail - Chi tiết sân
Method: GET
URL: {{base_url}}/api/owner/venues/{{venue_id}}
Header: Authorization: Bearer {{owner_token}}
```

---

#### Step 4.5: Cập nhật sân
```
Folder: 3. Venues Management
Request: Update Venue - Cập nhật sân
Method: PUT
URL: {{base_url}}/api/owner/venues/{{venue_id}}
```

**Body**:
```json
{
  "name": "Sân Bóng Đá Minh Châu - Updated",
  "description": "Sân bóng đá hiện đại - cập nhật"
}
```

---

### **Phase 5: Quản Lý Sân Nhỏ (Court)**

#### Step 5.1: Tạo sân nhỏ
```
Folder: 4. Courts Management
Request: Create Court - Tạo sân nhỏ
Method: POST
URL: {{base_url}}/api/owner/venues/{{venue_id}}/courts
```

**Body**:
```json
{
  "name": "Sân 1",
  "type": "Bóng đá 5 người",
  "description": "Sân bóng đá nhỏ"
}
```

**Response (201)**:
```json
{
  "message": "Tạo sân nhỏ thành công",
  "data": {
    "id": 1,
    "venue_id": 1,
    "name": "Sân 1",
    "type": "Bóng đá 5 người",
    "description": "Sân bóng đá nhỏ",
    "created_at": "2026-06-09T10:35:00"
  }
}
```

✅ **Lưu `court_id`** (thường là 1)

---

#### Step 5.2: Lấy danh sách sân nhỏ
```
Folder: 4. Courts Management
Request: List Courts - Danh sách sân nhỏ
Method: GET
URL: {{base_url}}/api/owner/courts
Header: Authorization: Bearer {{owner_token}}
```

---

#### Step 5.3: Lấy khung giờ của sân nhỏ
```
Folder: 4. Courts Management
Request: Get Court Time Slots - Khung giờ
Method: GET
URL: {{base_url}}/api/owner/courts/{{court_id}}/time-slots
Header: Authorization: Bearer {{owner_token}}
```

---

### **Phase 6: Xem Booking**

#### Step 6.1: Lấy tất cả booking
```
Folder: 5. Bookings Management
Request: List All Bookings - Tất cả booking
Method: GET
URL: {{base_url}}/api/owner/bookings
Header: Authorization: Bearer {{owner_token}}
```

---

#### Step 6.2: Lấy thống kê booking
```
Folder: 5. Bookings Management
Request: Get Bookings Stats - Thống kê
Method: GET
URL: {{base_url}}/api/owner/bookings/stats
Header: Authorization: Bearer {{owner_token}}
```

**Response**:
```json
{
  "message": "Thống kê booking",
  "data": {
    "total": 5,
    "confirmed": 3,
    "pending": 1,
    "cancelled": 1
  }
}
```

---

#### Step 6.3: Lấy booking theo sân
```
Folder: 5. Bookings Management
Request: Get Venue Bookings - Booking theo sân
Method: GET
URL: {{base_url}}/api/owner/venues/{{venue_id}}/bookings
Header: Authorization: Bearer {{owner_token}}
```

---

### **Phase 7: Test Error Cases**

#### Test 7.1: Không có token (401)
```
Folder: 6. Error Testing
Request: Test - Không có token
Method: GET
URL: {{base_url}}/api/owner/me
(KHÔNG có header Authorization)
```

**Response (401)**:
```json
{
  "message": "Unauthorized - Vui lòng đăng nhập"
}
```

---

#### Test 7.2: Token không hợp lệ (403)
```
Folder: 6. Error Testing
Request: Test - Token user
Method: GET
URL: {{base_url}}/api/owner/me
Header: Authorization: Bearer {{user_token}}
(nếu user_token là của user thường)
```

**Response (403)**:
```json
{
  "message": "Forbidden - Bạn không phải chủ sân"
}
```

---

#### Test 7.3: Resource không tồn tại (404)
```
Folder: 6. Error Testing
Request: Test - Venue không tồn tại
Method: GET
URL: {{base_url}}/api/owner/venues/99999
Header: Authorization: Bearer {{owner_token}}
```

**Response (404)**:
```json
{
  "message": "Sân không được tìm thấy hoặc bạn không có quyền"
}
```

---

#### Test 7.4: Validation error (422)
```
Folder: 6. Error Testing
Request: Test - Email đã tồn tại
Method: POST
URL: {{base_url}}/api/owner/register
```

**Body**:
```json
{
  "name": "Owner 2",
  "email": "owner@test.com",
  "password": "password123",
  "confirm_password": "password123",
  "phone": "0901234567"
}
```

**Response (422)**:
```json
{
  "message": "Validation error",
  "errors": {
    "email": ["Email này đã được đăng ký"]
  }
}
```

---

## 💡 Tips & Tricks

### Tip 1: Tự động set variables
Trong **Tests** tab của request Login:
```javascript
if (pm.response.code === 200) {
  var jsonData = pm.response.json();
  pm.environment.set("owner_token", jsonData.token);
  pm.environment.set("owner_id", jsonData.user.id);
}
```

Trong **Tests** tab của request Create Venue:
```javascript
if (pm.response.code === 201) {
  var jsonData = pm.response.json();
  pm.environment.set("venue_id", jsonData.data.id);
}
```

### Tip 2: Kiểm tra status code
```javascript
// Pre-request Script or Tests
pm.test("Status code is 200", function () {
  pm.expect(pm.response.code).to.equal(200);
});
```

### Tip 3: Kiểm tra response body
```javascript
pm.test("Response has token", function () {
  var jsonData = pm.response.json();
  pm.expect(jsonData.token).to.exist;
});
```

### Tip 4: Reuse collection
- Save collection với biến
- Chia sẻ file JSON với team
- Import lại lần sau

---

## 🔍 Debugging

### Nếu bị 401 Unauthorized
❌ **Vấn đề**: Token hết hạn hoặc không gửi token
✅ **Giải pháp**: 
- Login lại
- Copy token mới vào biến `owner_token`
- Kiểm tra header Authorization

### Nếu bị 403 Forbidden
❌ **Vấn đề**: Người dùng không phải owner hoặc chưa active
✅ **Giải pháp**:
- Kiểm tra Admin đã duyệt chưa (Tinker: `$user->role`)
- Kiểm tra status là 'active'
- Dùng owner token, không phải user token

### Nếu bị 404 Not Found
❌ **Vấn đề**: Resource không tồn tại hoặc không sở hữu
✅ **Giải pháp**:
- Kiểm tra ID có đúng không
- Kiểm tra resource có thuộc về owner này không
- Lấy danh sách trước để xem ID đúng

### Nếu bị 422 Validation Error
❌ **Vấn đề**: Dữ liệu không hợp lệ
✅ **Giải pháp**:
- Kiểm tra lại body JSON
- Xem error message để biết field nào sai
- Kiểm tra required fields

---

## 📋 Testing Checklist

### Authentication
- [ ] Register successful (201)
- [ ] Admin approve → role = owner
- [ ] Login successful (200)
- [ ] Token returned
- [ ] Token saved to variable

### Protected Routes
- [ ] Get Me works with token
- [ ] 401 without token
- [ ] 403 with user token (not owner)
- [ ] 403 if status not active

### Venues
- [ ] List venues
- [ ] Create venue (201)
- [ ] Get venue detail (200)
- [ ] Update venue (200)
- [ ] Delete venue (200)

### Courts
- [ ] Create court in venue (201)
- [ ] List courts (200)
- [ ] Get time slots (200)
- [ ] Update court (200)
- [ ] Delete court (200)

### Bookings
- [ ] List bookings (200)
- [ ] Get stats (200)
- [ ] Get venue bookings (200)
- [ ] Get court bookings (200)

### Error Handling
- [ ] 401 Unauthorized (no token)
- [ ] 403 Forbidden (not owner)
- [ ] 404 Not Found (resource missing)
- [ ] 422 Validation error
- [ ] 500 Server error

---

## 🎯 Next Steps

1. ✅ Import collection
2. ✅ Setup variables
3. ✅ Register account
4. ✅ Admin approve (Tinker)
5. ✅ Login
6. ✅ Test endpoints
7. ✅ Check responses
8. ✅ Test error cases

Enjoy! 🚀
