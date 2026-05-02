<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

Illuminate\Support\Facades\Route::get('/test-role', function(\Illuminate\Http\Request $request) {
    $user = App\Models\User::where('email', 'admin@vizura.com')->first();
    Illuminate\Support\Facades\Auth::login($user);
    return "HasRole: " . ($user->hasRole('Super Admin') ? 'yes' : 'no') . " Roles: " . $user->roles->pluck('name')->join(', ');
});

$request = Illuminate\Http\Request::create('/test-role', 'GET');
$response = $kernel->handle($request);
echo $response->getContent() . "\n";
