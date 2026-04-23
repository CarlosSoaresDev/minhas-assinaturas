<?php

use App\Http\Controllers\Auth\SocialiteController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Páginas Institucionais
Route::get('/termos', function () {
    return view('pages.terms');
})->name('terms');

Route::get('/privacidade', function () {
    return view('pages.privacy');
})->name('privacy');

// --- Autenticação Social (Google) ---
Route::get('/auth/google/redirect', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback'])->name('auth.google.callback');

Route::middleware(['auth'])->group(function () {
    // Dashboard (Usando a View original como na sua imagem)
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::middleware(['admin'])->group(function () {
        Route::view('/usuarios', 'pages.admin.users')->name('admin.users.index');
        Route::get('/logs', \App\Livewire\Admin\ActivityLogs::class)->name('admin.logs');
        Route::get('/admin/categorias', \App\Livewire\Admin\ManageCategories::class)->name('admin.categories');
        Route::get('/admin/alertas', \App\Livewire\Admin\ManualAlerts::class)->name('admin.alerts');
        Route::get('/admin/servicos', \App\Livewire\Admin\AllSubscriptions::class)->name('admin.services');
    });

    // Subscriptions (Privacy Scope Protected)
    Route::get('/subscriptions', \App\Livewire\Subscriptions\Index::class)->name('front.subscriptions.index');
});

require __DIR__.'/settings.php';
