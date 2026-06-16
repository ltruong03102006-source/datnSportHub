# Test Script for Admin Venues Management API
# Usage: .\test_admin_venues.ps1 -AdminToken "YOUR_ADMIN_TOKEN"

param(
    [string]$AdminToken = "your_admin_token_here",
    [string]$BaseUrl = "http://localhost:8000/api"
)

$headers = @{
    "Authorization" = "Bearer $AdminToken"
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Admin Venues Management API Tests" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

# Test 1: List all venues
Write-Host "[TEST 1] Listing all venues..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$BaseUrl/admin/venues" -Method Get -Headers $headers
    Write-Host "✓ Success: Retrieved $(($response.data.data | Measure-Object).Count) venues" -ForegroundColor Green
    $venueId = $response.data.data[0].id
    Write-Host "  First venue ID: $venueId`n" -ForegroundColor Green
} catch {
    Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 2: Filter venues by status
Write-Host "[TEST 2] Filtering venues by status='pending'..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$BaseUrl/admin/venues?status=pending" -Method Get -Headers $headers
    Write-Host "✓ Success: Found $(($response.data.data | Measure-Object).Count) pending venues`n" -ForegroundColor Green
} catch {
    Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 3: Search venues
Write-Host "[TEST 3] Searching venues by name..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$BaseUrl/admin/venues?search=ball" -Method Get -Headers $headers
    Write-Host "✓ Success: Found $(($response.data.data | Measure-Object).Count) venues matching 'ball'`n" -ForegroundColor Green
} catch {
    Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 4: Get venue details
Write-Host "[TEST 4] Getting venue details (ID: $venueId)..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$BaseUrl/admin/venues/$venueId" -Method Get -Headers $headers
    Write-Host "✓ Success: Retrieved venue '$($response.data.name)'" -ForegroundColor Green
    Write-Host "  Current status: $($response.data.status)" -ForegroundColor Green
    Write-Host "  Future bookings: $($response.future_bookings_count)`n" -ForegroundColor Green
} catch {
    Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 5: Activate venue
Write-Host "[TEST 5] Activating venue (ID: $venueId)..." -ForegroundColor Yellow
try {
    $body = @{
        reason = "Sân đã đủ điều kiện"
        notes = "Kiểm tra lần 2 passed"
    } | ConvertTo-Json

    $response = Invoke-RestMethod -Uri "$BaseUrl/admin/venues/$venueId/activate" `
        -Method Post -Headers $headers -Body $body
    Write-Host "✓ Success: $($response.message)" -ForegroundColor Green
    Write-Host "  New status: $($response.data.status)`n" -ForegroundColor Green
} catch {
    Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 6: Deactivate venue
Write-Host "[TEST 6] Deactivating venue (ID: $venueId)..." -ForegroundColor Yellow
try {
    $body = @{
        reason = "Vi phạm chính sách"
        notes = "Cần review lại cơ sở vật chất"
    } | ConvertTo-Json

    $response = Invoke-RestMethod -Uri "$BaseUrl/admin/venues/$venueId/deactivate" `
        -Method Post -Headers $headers -Body $body
    Write-Host "✓ Success: $($response.message)" -ForegroundColor Green
    Write-Host "  New status: $($response.data.status)" -ForegroundColor Green
    Write-Host "  Future bookings kept: $($response.future_bookings_count)`n" -ForegroundColor Green
} catch {
    Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 7: View venue activity logs
Write-Host "[TEST 7] Viewing venue activity logs (ID: $venueId)..." -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$BaseUrl/admin/venues/$venueId/logs" -Method Get -Headers $headers
    $logCount = ($response.data.data | Measure-Object).Count
    Write-Host "✓ Success: Retrieved $logCount activity logs" -ForegroundColor Green
    if ($logCount -gt 0) {
        Write-Host "  Latest action: $($response.data.data[0].action)" -ForegroundColor Green
        Write-Host "  By admin: $($response.data.data[0].admin.name)" -ForegroundColor Green
        Write-Host "  Status changed: $($response.data.data[0].old_status) → $($response.data.data[0].new_status)" -ForegroundColor Green
    }
    Write-Host "" -ForegroundColor Green
} catch {
    Write-Host "✗ Error: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 8: Try to activate already active venue (should fail with 400)
Write-Host "[TEST 8] Trying to activate an already active venue (should fail)..." -ForegroundColor Yellow
try {
    $body = @{
        reason = "Test"
        notes = "This should fail"
    } | ConvertTo-Json

    $response = Invoke-RestMethod -Uri "$BaseUrl/admin/venues/$venueId/activate" `
        -Method Post -Headers $headers -Body $body
    Write-Host "✗ Unexpected success - venue should already be active" -ForegroundColor Red
} catch {
    $errorCode = $_.Exception.Response.StatusCode.value__
    if ($errorCode -eq 400) {
        Write-Host "✓ Expected error (400): Venue is already inactive, can't activate again`n" -ForegroundColor Green
    } else {
        Write-Host "✗ Unexpected error: $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Tests completed!" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan
