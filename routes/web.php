<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('/chat', [App\Http\Controllers\Web\ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat', [App\Http\Controllers\Web\ChatController::class, 'storeConversation'])->name('chat.store.conversation');
    Route::post('/chat/{conversation}', [App\Http\Controllers\Web\ChatController::class, 'store'])->name('chat.store');
    Route::get('/chat/{conversation}', [App\Http\Controllers\Web\ChatController::class, 'show'])->name('chat.show');

});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
