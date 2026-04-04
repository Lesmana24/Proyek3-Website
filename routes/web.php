<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlantScanController;
Route::get('/', function () {
    $title = 'Selamat Datang';
    $slug = 'welcome';
    return view('konten.welcome', compact('title', 'slug'));
});
Route::get('/daftar', [PenggunaController::class, 'create'])
     ->name('daftar');

// terima submit form
Route::post('/daftar', [PenggunaController::class, 'store']);

// login
Route::get ('/login',  [LoginController::class, 'create'])->name('login');
Route::post('/login',  [LoginController::class, 'store']);

// logout (opsional, bisa POST via form)
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::middleware('auth:pengguna')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
});

Route::middleware('auth:pengguna')->group(function () {
Route::get('/notification', [NotificationController::class, 'index']);
Route::delete('/notification/clear', [NotificationController::class, 'deleteAll'])->name('notification.clear');
} );

Route::post('/update-setting', [HomeController::class, 'updateSettings'])->name('update.setting');
Route::post('/simpan-notif', [NotificationController::class, 'storeLog']);

//ai
Route::get('/ai', [PlantScanController::class, 'index'])->middleware('auth:pengguna')->name('ai.index');

Route::post('/ai/upload', [PlantScanController::class, 'upload'])->name('ai.upload');
Route::get('/ai/result/preview', [PlantScanController::class, 'preview'])->name('ai.preview');
Route::post('/ai/result/store', [PlantScanController::class, 'storeReport'])->name('ai.store');
Route::post('/ai/result/reset', [PlantScanController::class, 'reset'])->name('ai.reset');
Route::get('/ai/result/{id}', [PlantScanController::class, 'result'])->name('ai.result');
Route::post('/api/chat-botanist', [PlantScanController::class, 'chatBotanist'])->name('ai.chat');
