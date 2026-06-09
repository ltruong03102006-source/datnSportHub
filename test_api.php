<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$rand = rand(1000, 9999);
$email = "test{$rand}@example.com";
$password = "password123";

echo "Creating user $email...\n";

// Simulate Register
$request = Illuminate\Http\Request::create('/register', 'POST', [
    'name' => 'API Test User',
    'email' => $email,
    'password' => $password,
    'password_confirmation' => $password,
]);
$response = $kernel->handle($request);
echo "Register Status: " . $response->getStatusCode() . "\n";
echo "Register Body: " . $response->getContent() . "\n\n";

// Simulate Login
$request = Illuminate\Http\Request::create('/login', 'POST', [
    'email' => $email,
    'password' => $password,
]);
$response = $kernel->handle($request);
echo "Login Status: " . $response->getStatusCode() . "\n";
echo "Login Body: " . $response->getContent() . "\n\n";
