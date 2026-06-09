# ============================================================================
# Owner API Testing Script - PowerShell (Windows)
# Test Owner API endpoints from PowerShell
# ============================================================================

$BASE_URL = "http://localhost:8000"

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "Owner API Testing Script (PowerShell)" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# ============================================================================
# 1. REGISTER
# ============================================================================
Write-Host "[1] REGISTER - Đăng ký chủ sân" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Gray

$registerBody = @{
    name = "Nguyễn Văn A"
    email = "owner@test.com"
    password = "password123"
    confirm_password = "password123"
    phone = "0901234567"
} | ConvertTo-Json

$registerResponse = Invoke-WebRequest -Uri "$BASE_URL/api/owner/register" `
    -Method POST `
    -Headers @{"Content-Type" = "application/json"} `
    -Body $registerBody

Write-Host ($registerResponse.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10) -ForegroundColor Green

$registerToken = ($registerResponse.Content | ConvertFrom-Json).token
Write-Host "Token: $registerToken" -ForegroundColor Cyan
Write-Host ""

# ============================================================================
# 2. ADMIN APPROVAL (Manual)
# ============================================================================
Write-Host "[2] ADMIN APPROVAL - Admin duyệt (Manual)" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Gray
Write-Host "Chạy lệnh sau trong PowerShell:" -ForegroundColor Yellow
Write-Host "  cd d:\duantotnghiep\DATN2\datnSportHub" -ForegroundColor Cyan
Write-Host "  php artisan tinker" -ForegroundColor Cyan
Write-Host "  >>> `$user = User::where('email', 'owner@test.com')->first();" -ForegroundColor Cyan
Write-Host "  >>> `$user->update(['role' => 'owner']);" -ForegroundColor Cyan
Write-Host "  >>> exit" -ForegroundColor Cyan
Write-Host ""
Read-Host "Nhấn Enter khi đã duyệt xong"
Write-Host ""

# ============================================================================
# 3. LOGIN
# ============================================================================
Write-Host "[3] LOGIN - Đăng nhập" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Gray

$loginBody = @{
    email = "owner@test.com"
    password = "password123"
} | ConvertTo-Json

$loginResponse = Invoke-WebRequest -Uri "$BASE_URL/api/owner/login" `
    -Method POST `
    -Headers @{"Content-Type" = "application/json"} `
    -Body $loginBody

Write-Host ($loginResponse.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10) -ForegroundColor Green

$ownerToken = ($loginResponse.Content | ConvertFrom-Json).token
Write-Host "Token: $ownerToken" -ForegroundColor Cyan
Write-Host ""

if ([string]::IsNullOrEmpty($ownerToken)) {
    Write-Host "❌ Login failed! Cannot continue without token." -ForegroundColor Red
    exit
}

# ============================================================================
# 4. GET ME
# ============================================================================
Write-Host "[4] GET ME - Lấy thông tin chủ sân" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Gray

$meResponse = Invoke-WebRequest -Uri "$BASE_URL/api/owner/me" `
    -Method GET `
    -Headers @{"Authorization" = "Bearer $ownerToken"}

Write-Host ($meResponse.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10) -ForegroundColor Green
Write-Host ""

# ============================================================================
# 5. GET SPORTS
# ============================================================================
Write-Host "[5] GET SPORTS - Danh sách thể thao" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Gray

$sportsResponse = Invoke-WebRequest -Uri "$BASE_URL/api/owner/sports" `
    -Method GET `
    -Headers @{"Authorization" = "Bearer $ownerToken"}

$sportsData = $sportsResponse.Content | ConvertFrom-Json
Write-Host ($sportsData | ConvertTo-Json -Depth 10) -ForegroundColor Green

$sportId = $sportsData.data[0].id
Write-Host "Sport ID: $sportId" -ForegroundColor Cyan
Write-Host ""

# ============================================================================
# 6. CREATE VENUE
# ============================================================================
Write-Host "[6] CREATE VENUE - Tạo sân vận động" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Gray

$venueBody = @{
    sport_id = $sportId
    name = "Sân Bóng Đá Minh Châu"
    address = "123 Đường ABC, Hà Nội"
    lat = 21.0285
    lng = 105.8542
    description = "Sân bóng đá hiện đại"
} | ConvertTo-Json

$venueResponse = Invoke-WebRequest -Uri "$BASE_URL/api/owner/venues" `
    -Method POST `
    -Headers @{"Content-Type" = "application/json"; "Authorization" = "Bearer $ownerToken"} `
    -Body $venueBody

$venueData = $venueResponse.Content | ConvertFrom-Json
Write-Host ($venueData | ConvertTo-Json -Depth 10) -ForegroundColor Green

$venueId = $venueData.data.id
Write-Host "Venue ID: $venueId" -ForegroundColor Cyan
Write-Host ""

if ([string]::IsNullOrEmpty($venueId)) {
    Write-Host "❌ Venue creation failed!" -ForegroundColor Red
    exit
}

# ============================================================================
# 7. LIST VENUES
# ============================================================================
Write-Host "[7] LIST VENUES - Danh sách sân" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Gray

$listVenuesResponse = Invoke-WebRequest -Uri "$BASE_URL/api/owner/venues" `
    -Method GET `
    -Headers @{"Authorization" = "Bearer $ownerToken"}

Write-Host ($listVenuesResponse.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10) -ForegroundColor Green
Write-Host ""

# ============================================================================
# 8. CREATE COURT
# ============================================================================
Write-Host "[8] CREATE COURT - Tạo sân nhỏ" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Gray

$courtBody = @{
    name = "Sân 1"
    type = "Bóng đá 5 người"
    description = "Sân bóng đá nhỏ"
} | ConvertTo-Json

$courtResponse = Invoke-WebRequest -Uri "$BASE_URL/api/owner/venues/$venueId/courts" `
    -Method POST `
    -Headers @{"Content-Type" = "application/json"; "Authorization" = "Bearer $ownerToken"} `
    -Body $courtBody

$courtData = $courtResponse.Content | ConvertFrom-Json
Write-Host ($courtData | ConvertTo-Json -Depth 10) -ForegroundColor Green

$courtId = $courtData.data.id
Write-Host "Court ID: $courtId" -ForegroundColor Cyan
Write-Host ""

# ============================================================================
# 9. LIST BOOKINGS
# ============================================================================
Write-Host "[9] LIST BOOKINGS - Danh sách booking" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Gray

$bookingsResponse = Invoke-WebRequest -Uri "$BASE_URL/api/owner/bookings" `
    -Method GET `
    -Headers @{"Authorization" = "Bearer $ownerToken"}

Write-Host ($bookingsResponse.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10) -ForegroundColor Green
Write-Host ""

# ============================================================================
# 10. BOOKINGS STATS
# ============================================================================
Write-Host "[10] BOOKINGS STATS - Thống kê booking" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Gray

$statsResponse = Invoke-WebRequest -Uri "$BASE_URL/api/owner/bookings/stats" `
    -Method GET `
    -Headers @{"Authorization" = "Bearer $ownerToken"}

Write-Host ($statsResponse.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10) -ForegroundColor Green
Write-Host ""

# ============================================================================
# 11. TEST ERROR - NO TOKEN
# ============================================================================
Write-Host "[11] ERROR TEST - Không có token (401)" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Gray

try {
    $noTokenResponse = Invoke-WebRequest -Uri "$BASE_URL/api/owner/me" `
        -Method GET `
        -ErrorAction Stop
} catch {
    $errorResponse = $_.Exception.Response
    $errorStream = $errorResponse.GetResponseStream()
    $errorReader = [System.IO.StreamReader]::new($errorStream)
    $errorContent = $errorReader.ReadToEnd()
    Write-Host "Status: $($errorResponse.StatusCode)" -ForegroundColor Red
    Write-Host ($errorContent | ConvertFrom-Json | ConvertTo-Json -Depth 10) -ForegroundColor Red
}
Write-Host ""

# ============================================================================
# 12. CHANGE PASSWORD
# ============================================================================
Write-Host "[12] CHANGE PASSWORD - Đổi mật khẩu" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Gray

$passwordBody = @{
    current_password = "password123"
    new_password = "newpassword456"
    confirm_password = "newpassword456"
} | ConvertTo-Json

$passwordResponse = Invoke-WebRequest -Uri "$BASE_URL/api/owner/change-password" `
    -Method POST `
    -Headers @{"Content-Type" = "application/json"; "Authorization" = "Bearer $ownerToken"} `
    -Body $passwordBody

Write-Host ($passwordResponse.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10) -ForegroundColor Green
Write-Host ""

# ============================================================================
# 13. LOGIN WITH NEW PASSWORD
# ============================================================================
Write-Host "[13] LOGIN WITH NEW PASSWORD - Đăng nhập với mật khẩu mới" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Gray

$newLoginBody = @{
    email = "owner@test.com"
    password = "newpassword456"
} | ConvertTo-Json

$newLoginResponse = Invoke-WebRequest -Uri "$BASE_URL/api/owner/login" `
    -Method POST `
    -Headers @{"Content-Type" = "application/json"} `
    -Body $newLoginBody

Write-Host ($newLoginResponse.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10) -ForegroundColor Green

$newOwnerToken = ($newLoginResponse.Content | ConvertFrom-Json).token
Write-Host "New Token: $newOwnerToken" -ForegroundColor Cyan
Write-Host ""

# ============================================================================
# 14. LOGOUT
# ============================================================================
Write-Host "[14] LOGOUT - Đăng xuất" -ForegroundColor Yellow
Write-Host "---" -ForegroundColor Gray

$logoutResponse = Invoke-WebRequest -Uri "$BASE_URL/api/owner/logout" `
    -Method POST `
    -Headers @{"Authorization" = "Bearer $newOwnerToken"}

Write-Host ($logoutResponse.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10) -ForegroundColor Green
Write-Host ""

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "✅ Testing Complete!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Cyan
