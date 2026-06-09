# Owner Authentication System - Architecture

## Sơ Đồ Hệ Thống

```
┌─────────────────────────────────────────────────────────────────┐
│                    CLIENT (Mobile/Web)                          │
└────────────────────────┬────────────────────────────────────────┘
                         │
         ┌───────────────┼───────────────┐
         │               │               │
    POST /register  POST /login  Other requests
         │               │               │
         ↓               ↓               ↓
    ┌─────────────────────────────────────────┐
    │   OwnerAuthController (Public Routes)   │
    │                                         │
    │  - register()  ← Tạo user (role=user)   │
    │  - login()     ← Kiểm tra email/pwd     │
    ├─────────────────────────────────────────┤
    │   OwnerAuthController (Protected)       │
    │                                         │
    │  - logout()         ← Xóa token         │
    │  - me()             ← Lấy user info     │
    │  - changePassword() ← Đổi pwd           │
    └────────────┬────────────────────────────┘
                 │
         Check Token (auth:sanctum)
                 │
         ┌───────↓────────┐
         │   Token Valid? │
         └────────┬───────┘
                  │ Yes
         ┌────────↓──────────────┐
         │  EnsureOwnerRole      │
         │  (Middleware)         │
         │                       │
         │ - role = 'owner'? ✓   │
         │ - status = 'active'? ✓│
         └────────┬──────────────┘
                  │ Yes
         ┌────────↓──────────────┐
         │  Route Handlers       │
         │                       │
         │ OwnerVenueController  │
         │ OwnerCourtController  │
         │ OwnerBookingController
         └───────────────────────┘
```

---

## Middleware Flow

```
Request
   ↓
[Public Route?]
├─ Yes → Execute (no auth)
└─ No ↓
   [Has Token?]
   ├─ No → 401 Unauthorized
   └─ Yes ↓
      [Token Valid?]
      ├─ No → 401 Unauthorized
      └─ Yes ↓
         [EnsureOwnerRole Middleware]
         ├─ role != 'owner' → 403 Forbidden (not owner)
         ├─ status != 'active' → 403 Forbidden (not active)
         └─ ✓ → Execute Route Handler
            ↓
         Controller Method
            ↓
         Response
```

---

## Data Flow - Đăng Ký → Đăng Nhập

```
1. REGISTRATION FLOW
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Client POST /api/owner/register
   │
   ↓
OwnerAuthController::register()
   ├─ Validate input
   ├─ Create User (role='user', status='active')
   ├─ Create OwnerRegistration (status='pending')
   ├─ Generate token
   └─ Return token + user data
        │
        └─ User: role='user' (chưa phải owner)

┌─────────────────────────────────────────┐
│ ADMIN APPROVAL (Database or Tinker)     │
│ UPDATE users SET role='owner'           │
│ WHERE id = ?                            │
└─────────────────────────────────────────┘
        │
        └─ User: role='owner' ✓


2. LOGIN FLOW
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Client POST /api/owner/login
   │
   ↓
OwnerAuthController::login()
   ├─ Find User by email
   ├─ Verify password (Hash::check)
   ├─ Check role = 'owner'? ✓
   ├─ Check status = 'active'? ✓
   ├─ Generate new token (delete old ones)
   └─ Return token + user data
        │
        └─ Token: valid for owner


3. ACCESS PROTECTED ENDPOINT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Client GET /api/owner/venues
Authorization: Bearer {token}
   │
   ↓
[auth:sanctum] Middleware
   ├─ Parse token
   └─ Set $request->user()
        │
        ↓
[owner] Middleware (EnsureOwnerRole)
   ├─ Check $user->role = 'owner'? ✓
   ├─ Check $user->status = 'active'? ✓
   └─ Next ✓
        │
        ↓
OwnerVenueController::index()
   ├─ Get venues where owner_id = $user->id
   └─ Return venues
```

---

## Route Groups & Middleware

```
┌─ Public Routes (No Auth)
│  POST /api/owner/register
│  POST /api/owner/login
│
├─ Protected Routes [auth:sanctum, owner]
│  ├─ Auth Endpoints
│  │  ├─ POST /api/owner/logout
│  │  ├─ GET /api/owner/me
│  │  └─ POST /api/owner/change-password
│  │
│  ├─ Venue Management
│  │  ├─ GET /api/owner/venues                    [list]
│  │  ├─ POST /api/owner/venues                   [create]
│  │  ├─ GET /api/owner/venues/{id}               [show]
│  │  ├─ PUT /api/owner/venues/{id}               [update]
│  │  ├─ DELETE /api/owner/venues/{id}            [delete]
│  │  └─ GET /api/owner/sports                    [dropdown]
│  │
│  ├─ Court Management
│  │  ├─ GET /api/owner/courts                    [list]
│  │  ├─ POST /api/owner/venues/{venueId}/courts [create]
│  │  ├─ PUT /api/owner/courts/{courtId}          [update]
│  │  ├─ DELETE /api/owner/courts/{courtId}       [delete]
│  │  └─ GET /api/owner/courts/{courtId}/time-slots
│  │
│  └─ Booking Management
│     ├─ GET /api/owner/bookings                  [list all]
│     ├─ GET /api/owner/bookings/stats            [statistics]
│     ├─ GET /api/owner/bookings/{id}             [show]
│     ├─ GET /api/owner/venues/{venueId}/bookings [by venue]
│     └─ GET /api/owner/courts/{courtId}/bookings [by court]
```

---

## Authorization Check

```
┌─────────────────────────────────────────────┐
│       EnsureOwnerRole Middleware            │
│                                             │
│ Checks:                                     │
│ 1. User exists                              │
│ 2. User->role === 'owner'                   │
│ 3. User->status === 'active'                │
│                                             │
│ If any check fails → 403 Forbidden          │
└─────────────────────────────────────────────┘

Example Response (Fail):
{
    "message": "Forbidden - Bạn không phải chủ sân"
}

{
    "message": "Forbidden - Tài khoản chủ sân chưa được kích hoạt"
}
```

---

## Resource Ownership Check

```
┌─────────────────────────────────────────────────┐
│    Ownership Verification in Controllers        │
│                                                 │
│  Every CRUD operation checks:                   │
│  $resource->owner_id === $user->id              │
│                                                 │
│  If not matching → 404 Not Found                │
│  (security: not revealing resource exists)      │
└─────────────────────────────────────────────────┘

Example in OwnerVenueController::update():
  
$venue = Venue::where('owner_id', $user->id)  ← Check ownership
                ->where('id', $id)
                ->first();

if (!$venue) {
    return 404; // User doesn't own this venue
}
```

---

## Error Handling Hierarchy

```
        Request
           ↓
    [Syntax Error?] → 400 Bad Request
           ↓ No
    [Validation Error?] → 422 Unprocessable Entity
           ↓ No
    [Not Authenticated?] → 401 Unauthorized
           ↓ No
    [Not Owner Role?] → 403 Forbidden
           ↓ No
    [Not Active Status?] → 403 Forbidden
           ↓ No
    [Resource Not Found?] → 404 Not Found
           ↓ No
    [Ownership Check Failed?] → 404 Not Found
           ↓ No
    [Business Logic Error?] → 400 Bad Request
           ↓ No
    [Server Error?] → 500 Internal Server Error
           ↓ No
        ✓ Success → 200/201
```

---

## Token Management

```
┌────────────────────────────────────────┐
│  User Registration / Login              │
│                                         │
│  → User::createToken('auth_token')      │
│  → Sanctum generates plainTextToken     │
│  → Return to client                     │
│  → Client stores in localStorage/etc    │
└────────────────────────────────────────┘

Token Structure:
  1|xxxxxxxxxxxxxxxxxxxxx
  │ │
  │ └─ Unique token string (Sanctum)
  └─ Token ID

Usage:
  Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxx

Cleanup:
  $user->tokens()->delete()  // Delete all tokens
  $user->currentAccessToken()->delete()  // Delete current
```

---

## Database Relationships

```
User (1) ──────┬──────── (1) OwnerRegistration
               │
               └─────── (1) Venue
                            │
                            └─────── (1..N) Court
                                       │
                                       └─────── (1..N) Booking
```

---

## File Organization

```
SportHub/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── OwnerAuthController.php       [NEW] ✓
│   │   │       ├── OwnerVenueController.php      [NEW] ✓
│   │   │       ├── OwnerCourtController.php      [NEW] ✓
│   │   │       └── OwnerBookingController.php    [NEW] ✓
│   │   │
│   │   └── Middleware/
│   │       └── EnsureOwnerRole.php               [NEW] ✓
│   │
│   ├── Models/
│   │   ├── User.php                             [MODIFIED]
│   │   └── ...
│   │
│   └── Providers/
│       └── AppServiceProvider.php
│
├── bootstrap/
│   └── app.php                                  [MODIFIED]
│
├── routes/
│   └── api.php                                  [MODIFIED]
│
└── Documentation/
    ├── OWNER_API.md                             [NEW] ✓
    └── OWNER_API_QUICKSTART.md                  [NEW] ✓
```

---

## Summary Checklist

- ✅ Middleware (EnsureOwnerRole)
- ✅ 4 Controllers (Auth, Venue, Court, Booking)
- ✅ 21 API Endpoints
- ✅ Token Authentication (Sanctum)
- ✅ Role-based Authorization
- ✅ Resource Ownership Checks
- ✅ Error Handling
- ✅ Documentation (2 files)
- ✅ Syntax Validation
- ✅ Route Registration Verified

**Status**: Ready for Testing ✓
