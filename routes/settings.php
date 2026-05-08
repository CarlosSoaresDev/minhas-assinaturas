<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::view('settings/profile', 'pages.settings.profile')->name('profile.edit');
});

Route::middleware(['auth'])->group(function () {
    Volt::route('settings/password', 'settings.⚡password')->name('password.edit');
});

Route::middleware(['auth', 'password.confirm'])->group(function () {
    Volt::route('settings/two-factor', 'settings.⚡two-factor')->name('two-factor.edit');
});
