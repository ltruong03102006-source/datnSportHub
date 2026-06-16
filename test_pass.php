<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
echo "User pass: " . ($user ? $user->password : 'null') . "\n";

$owner = App\Models\User::where('role', 'owner')->first();
echo "Owner pass: " . ($owner ? $owner->password : 'null') . "\n";
