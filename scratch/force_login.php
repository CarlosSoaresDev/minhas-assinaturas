<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::first();
if ($user) {
    Auth::login($user);
    echo "Logado como: " . $user->email;
}
