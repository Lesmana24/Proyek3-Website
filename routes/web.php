<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlantScanController;

// ==========================================
// 1. AREA PUBLIK (TIDAK BUTUH LOGIN)
// ==========================================
Route::get('/', function () {
    $title = 'Selamat Datang';
    $slug  = 'welcome';
    return view('konten.welcome', compact('title', 'slug'));
});

// Middleware 'guest' mencegah user yang SUDAH login balik lagi ke halaman form login/daftar
Route::middleware('guest:pengguna')->group(function () {
    Route::get('/daftar', [PenggunaController::class, 'create'])->name('daftar');
    Route::post('/daftar', [PenggunaController::class, 'store']);

    Route::get('/login',  [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

// ==========================================
// 2. AREA PRIVAT (WAJIB LOGIN)
// ==========================================
Route::middleware('auth:pengguna')->group(function () {
    
    // Akses Akun & Homepage
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::post('/update-setting', [HomeController::class, 'updateSettings'])->name('update.setting');

    // Notifikasi
    Route::get('/notification', [NotificationController::class, 'index']);
    Route::delete('/notification/clear', [NotificationController::class, 'deleteAll'])->name('notification.clear');
    Route::post('/simpan-notif', [NotificationController::class, 'storeLog']);

    // ------------------------------------------
    // FITUR UTAMA AI (Semuanya Wajib Login!)
    // ------------------------------------------
    Route::get('/ai', [PlantScanController::class, 'index'])->name('ai.index');
    Route::post('/ai/upload', [PlantScanController::class, 'upload'])->name('ai.upload');
    
    // Alur Hasil Deteksi
    Route::get('/ai/result/preview', [PlantScanController::class, 'preview'])->name('ai.preview');
    Route::post('/ai/result/store', [PlantScanController::class, 'storeReport'])->name('ai.store');
    Route::post('/ai/result/reset', [PlantScanController::class, 'reset'])->name('ai.reset');
    Route::get('/ai/result/{id}', [PlantScanController::class, 'result'])->name('ai.result');
    
    // Hapus Riwayat Deteksi
    Route::delete('/ai/history/{id}', [PlantScanController::class, 'destroy'])->name('ai.history.destroy');

    // Chatbot Integrasi Gemini
    Route::post('/api/chat-botanist', [PlantScanController::class, 'chatBotanist'])->name('ai.chat');

});
