<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::create([
    'name' => 'Test User 2',
    'email' => 'test2@example.com',
    'password' => Illuminate\Support\Facades\Hash::make('password123'),
    'role' => 'user',
    'status' => 'active',
]);

echo "DB pass: " . $user->password . "\n";
echo "Check pass: " . (Illuminate\Support\Facades\Hash::check('password123', $user->password) ? 'true' : 'false') . "\n";
