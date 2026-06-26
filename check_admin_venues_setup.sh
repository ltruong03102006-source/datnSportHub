#!/bin/bash
# Artisan commands to setup test data for Admin Venues Management

echo "================================"
echo "Setting up test data for Admin Venues Management"
echo "================================"

cd /d/duantotnghiep/DATN2/datnSportHub

# Check if admin account exists
echo ""
echo "[1] Checking for admin account..."
php artisan tinker << 'EOF'
$admin = \App\Models\User::where('role', 'admin')->first();
if ($admin) {
    echo "✓ Admin found: {$admin->name} ({$admin->email})\n";
} else {
    echo "✗ No admin account found.\n";
}
exit();
EOF

# List all venues
echo ""
echo "[2] Listing all venues..."
php artisan tinker << 'EOF'
$venues = \App\Models\Venue::with('owner', 'sport')->take(5)->get();
if ($venues->count() > 0) {
    echo "✓ Found {$venues->count()} venues:\n";
    foreach ($venues as $v) {
        echo "   - ID: {$v->id}, Name: {$v->name}, Status: {$v->status}, Owner: {$v->owner->name}\n";
    }
} else {
    echo "✗ No venues found.\n";
}
exit();
EOF

# List venue logs
echo ""
echo "[3] Checking venue logs table..."
php artisan tinker << 'EOF'
$logs = \App\Models\VenueLog::with('venue', 'admin')->take(5)->get();
if ($logs->count() > 0) {
    echo "✓ Found {$logs->count()} venue logs:\n";
    foreach ($logs as $l) {
        echo "   - Action: {$l->action}, Venue: {$l->venue->name}, Admin: {$l->admin->name}\n";
    }
} else {
    echo "✓ Venue logs table is empty (no actions logged yet).\n";
}
exit();
EOF

echo ""
echo "================================"
echo "Setup check completed!"
echo "================================"
