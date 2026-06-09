#!/bin/bash

# ============================================================================
# Owner API Testing Script - Using cURL
# Test Owner API endpoints from command line
# ============================================================================

BASE_URL="http://localhost:8000"

echo "=========================================="
echo "Owner API Testing Script"
echo "=========================================="
echo ""

# ============================================================================
# 1. REGISTER
# ============================================================================
echo "[1] REGISTER - Đăng ký chủ sân"
echo "---"

REGISTER_RESPONSE=$(curl -s -X POST "$BASE_URL/api/owner/register" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Nguyễn Văn A",
    "email": "owner@test.com",
    "password": "password123",
    "confirm_password": "password123",
    "phone": "0901234567"
  }')

echo "$REGISTER_RESPONSE" | jq .

# Extract token (if needed for further testing)
REGISTER_TOKEN=$(echo "$REGISTER_RESPONSE" | jq -r '.token // empty')
echo "Token: $REGISTER_TOKEN"
echo ""

# ============================================================================
# 2. ADMIN APPROVAL (Manual - run in Tinker)
# ============================================================================
echo "[2] ADMIN APPROVAL - Admin duyệt (Manual)"
echo "---"
echo "Run in terminal:"
echo "  php artisan tinker"
echo "  >>> \$user = User::where('email', 'owner@test.com')->first();"
echo "  >>> \$user->update(['role' => 'owner']);"
echo "Press Enter when done..."
read -p ""
echo ""

# ============================================================================
# 3. LOGIN
# ============================================================================
echo "[3] LOGIN - Đăng nhập"
echo "---"

LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/owner/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "owner@test.com",
    "password": "password123"
  }')

echo "$LOGIN_RESPONSE" | jq .

# Extract token for subsequent requests
OWNER_TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.token // empty')
echo "Token: $OWNER_TOKEN"
echo ""

if [ -z "$OWNER_TOKEN" ]; then
  echo "❌ Login failed! Cannot continue without token."
  exit 1
fi

# ============================================================================
# 4. GET ME
# ============================================================================
echo "[4] GET ME - Lấy thông tin chủ sân"
echo "---"

curl -s -X GET "$BASE_URL/api/owner/me" \
  -H "Authorization: Bearer $OWNER_TOKEN" | jq .
echo ""

# ============================================================================
# 5. GET SPORTS
# ============================================================================
echo "[5] GET SPORTS - Danh sách thể thao"
echo "---"

SPORTS_RESPONSE=$(curl -s -X GET "$BASE_URL/api/owner/sports" \
  -H "Authorization: Bearer $OWNER_TOKEN")

echo "$SPORTS_RESPONSE" | jq .

SPORT_ID=$(echo "$SPORTS_RESPONSE" | jq -r '.data[0].id // 1')
echo "Sport ID: $SPORT_ID"
echo ""

# ============================================================================
# 6. CREATE VENUE
# ============================================================================
echo "[6] CREATE VENUE - Tạo sân vận động"
echo "---"

VENUE_RESPONSE=$(curl -s -X POST "$BASE_URL/api/owner/venues" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -d "{
    \"sport_id\": $SPORT_ID,
    \"name\": \"Sân Bóng Đá Minh Châu\",
    \"address\": \"123 Đường ABC, Hà Nội\",
    \"lat\": 21.0285,
    \"lng\": 105.8542,
    \"description\": \"Sân bóng đá hiện đại\"
  }")

echo "$VENUE_RESPONSE" | jq .

VENUE_ID=$(echo "$VENUE_RESPONSE" | jq -r '.data.id // empty')
echo "Venue ID: $VENUE_ID"
echo ""

if [ -z "$VENUE_ID" ]; then
  echo "❌ Venue creation failed! Cannot continue."
  exit 1
fi

# ============================================================================
# 7. LIST VENUES
# ============================================================================
echo "[7] LIST VENUES - Danh sách sân của chủ sân"
echo "---"

curl -s -X GET "$BASE_URL/api/owner/venues" \
  -H "Authorization: Bearer $OWNER_TOKEN" | jq .
echo ""

# ============================================================================
# 8. GET VENUE DETAIL
# ============================================================================
echo "[8] GET VENUE DETAIL - Chi tiết sân"
echo "---"

curl -s -X GET "$BASE_URL/api/owner/venues/$VENUE_ID" \
  -H "Authorization: Bearer $OWNER_TOKEN" | jq .
echo ""

# ============================================================================
# 9. UPDATE VENUE
# ============================================================================
echo "[9] UPDATE VENUE - Cập nhật sân"
echo "---"

curl -s -X PUT "$BASE_URL/api/owner/venues/$VENUE_ID" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -d '{
    "name": "Sân Bóng Đá Minh Châu - Updated",
    "description": "Sân bóng đá hiện đại - cập nhật"
  }' | jq .
echo ""

# ============================================================================
# 10. CREATE COURT
# ============================================================================
echo "[10] CREATE COURT - Tạo sân nhỏ"
echo "---"

COURT_RESPONSE=$(curl -s -X POST "$BASE_URL/api/owner/venues/$VENUE_ID/courts" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -d '{
    "name": "Sân 1",
    "type": "Bóng đá 5 người",
    "description": "Sân bóng đá nhỏ"
  }')

echo "$COURT_RESPONSE" | jq .

COURT_ID=$(echo "$COURT_RESPONSE" | jq -r '.data.id // empty')
echo "Court ID: $COURT_ID"
echo ""

# ============================================================================
# 11. LIST COURTS
# ============================================================================
echo "[11] LIST COURTS - Danh sách sân nhỏ"
echo "---"

curl -s -X GET "$BASE_URL/api/owner/courts" \
  -H "Authorization: Bearer $OWNER_TOKEN" | jq .
echo ""

# ============================================================================
# 12. GET COURT TIME SLOTS
# ============================================================================
echo "[12] GET COURT TIME SLOTS - Khung giờ của sân"
echo "---"

curl -s -X GET "$BASE_URL/api/owner/courts/$COURT_ID/time-slots" \
  -H "Authorization: Bearer $OWNER_TOKEN" | jq .
echo ""

# ============================================================================
# 13. LIST BOOKINGS
# ============================================================================
echo "[13] LIST BOOKINGS - Danh sách booking"
echo "---"

curl -s -X GET "$BASE_URL/api/owner/bookings" \
  -H "Authorization: Bearer $OWNER_TOKEN" | jq .
echo ""

# ============================================================================
# 14. BOOKINGS STATS
# ============================================================================
echo "[14] BOOKINGS STATS - Thống kê booking"
echo "---"

curl -s -X GET "$BASE_URL/api/owner/bookings/stats" \
  -H "Authorization: Bearer $OWNER_TOKEN" | jq .
echo ""

# ============================================================================
# 15. GET VENUE BOOKINGS
# ============================================================================
echo "[15] GET VENUE BOOKINGS - Booking theo sân"
echo "---"

curl -s -X GET "$BASE_URL/api/owner/venues/$VENUE_ID/bookings" \
  -H "Authorization: Bearer $OWNER_TOKEN" | jq .
echo ""

# ============================================================================
# 16. GET COURT BOOKINGS
# ============================================================================
echo "[16] GET COURT BOOKINGS - Booking theo sân nhỏ"
echo "---"

curl -s -X GET "$BASE_URL/api/owner/courts/$COURT_ID/bookings" \
  -H "Authorization: Bearer $OWNER_TOKEN" | jq .
echo ""

# ============================================================================
# 17. TEST ERROR - NO TOKEN
# ============================================================================
echo "[17] ERROR TEST - Không có token (401)"
echo "---"

curl -s -X GET "$BASE_URL/api/owner/me" | jq .
echo ""

# ============================================================================
# 18. TEST ERROR - INVALID TOKEN
# ============================================================================
echo "[18] ERROR TEST - Token không hợp lệ (401)"
echo "---"

curl -s -X GET "$BASE_URL/api/owner/me" \
  -H "Authorization: Bearer invalid_token" | jq .
echo ""

# ============================================================================
# 19. CHANGE PASSWORD
# ============================================================================
echo "[19] CHANGE PASSWORD - Đổi mật khẩu"
echo "---"

curl -s -X POST "$BASE_URL/api/owner/change-password" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $OWNER_TOKEN" \
  -d '{
    "current_password": "password123",
    "new_password": "newpassword456",
    "confirm_password": "newpassword456"
  }' | jq .
echo ""

# ============================================================================
# 20. LOGIN AGAIN (with new password)
# ============================================================================
echo "[20] LOGIN AGAIN - Đăng nhập lại với mật khẩu mới"
echo "---"

NEW_LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/owner/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "owner@test.com",
    "password": "newpassword456"
  }')

echo "$NEW_LOGIN_RESPONSE" | jq .

NEW_OWNER_TOKEN=$(echo "$NEW_LOGIN_RESPONSE" | jq -r '.token // empty')
echo "New Token: $NEW_OWNER_TOKEN"
echo ""

# ============================================================================
# 21. UPDATE COURT
# ============================================================================
echo "[21] UPDATE COURT - Cập nhật sân nhỏ"
echo "---"

curl -s -X PUT "$BASE_URL/api/owner/courts/$COURT_ID" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $NEW_OWNER_TOKEN" \
  -d '{
    "name": "Sân 1 - Updated",
    "type": "Bóng đá 5 người"
  }' | jq .
echo ""

# ============================================================================
# 22. LOGOUT
# ============================================================================
echo "[22] LOGOUT - Đăng xuất"
echo "---"

curl -s -X POST "$BASE_URL/api/owner/logout" \
  -H "Authorization: Bearer $NEW_OWNER_TOKEN" | jq .
echo ""

echo "=========================================="
echo "✅ Testing Complete!"
echo "=========================================="
