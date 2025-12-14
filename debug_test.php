<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Running migrations...\n";
    Artisan::call('migrate:fresh', ['--force' => true]);
    
    echo "Creating roles...\n";
    $role = Role::create(['name' => 'CLIENT']);
    echo "Role created: " . $role->name . "\n";
    
    echo "Creating user...\n";
    $user = User::create([
        'nom' => 'Debug User',
        'email' => 'debug@example.com',
        'password' => Hash::make('password'),
    ]);
    echo "User created: " . $user->id . "\n";
    
    echo "Assigning role...\n";
    $user->assignRole('CLIENT');
    echo "Role assigned.\n";
    
    echo "Testing Auth::attempt...\n";
    if (\Illuminate\Support\Facades\Auth::attempt(['email' => 'debug@example.com', 'password' => 'password'])) {
        echo "Auth successful.\n";
    } else {
        echo "Auth FAILED.\n";
    }

    echo "Creating token...\n";
    $token = $user->createToken('auth_token')->plainTextToken;
    echo "Token created: " . substr($token, 0, 10) . "...\n";
    
    echo "Loading resources...\n";
    $resource = \App\Http\Resources\UserResource::make($user->load('roles'));
    echo "Resource created.\n";
    
    echo "SUCCESS\n";
    
} catch (\Throwable $e) {
    $error = "ERROR: " . $e->getMessage() . "\n";
    $error .= "File: " . $e->getFile() . "\n";
    $error .= "Line: " . $e->getLine() . "\n";
    // $error .= $e->getTraceAsString();
    file_put_contents('error.log', json_encode($error));
    echo "ERROR LOGGED\n";
}
