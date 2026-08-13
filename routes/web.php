<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\JenisSuratController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PengajuanSuratController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/verifikasi', [VerificationController::class, 'index'])->name('verification.index');
Route::post('/verifikasi', [VerificationController::class, 'verify'])->name('verification.verify');
Route::get('/verifikasi/{code}', [VerificationController::class, 'show'])->name('verification.show');

// Route untuk Tamu
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Route untuk Member
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [LaporanController::class, 'index'])->name('dashboard');
    // Route arsip lama dibatasi ke aksi yang benar-benar masih punya handler.
    Route::resource('surat-masuk', SuratMasukController::class)->only(['index', 'create', 'store', 'show']);
    // Route Disposisi (Inbox & Proses)
    Route::resource('disposisi', DisposisiController::class)->only(['index', 'show', 'update']);
    // Route arsip lama dibatasi ke aksi yang benar-benar masih punya handler.
    Route::resource('surat-keluar', SuratKeluarController::class)->only(['index', 'create', 'store', 'show', 'update']);
    Route::resource('jenis-surat', JenisSuratController::class)->except(['create', 'edit', 'show']);
    Route::post('pengajuan-surat/{pengajuan_surat}/process', [PengajuanSuratController::class, 'process'])->name('pengajuan-surat.process');
    Route::post('pengajuan-surat/{pengajuan_surat}/sign', [PengajuanSuratController::class, 'sign'])->name('pengajuan-surat.sign');
    Route::get('pengajuan-surat/{pengajuan_surat}/preview', [PengajuanSuratController::class, 'preview'])->name('pengajuan-surat.preview');
    Route::get('pengajuan-surat/{pengajuan_surat}/export/{format}', [PengajuanSuratController::class, 'export'])->name('pengajuan-surat.export');
    Route::get('pengajuan-surat/{pengajuan_surat}/attachment/{field}', [PengajuanSuratController::class, 'attachment'])->name('pengajuan-surat.attachment');
    Route::resource('pengajuan-surat', PengajuanSuratController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    // Route Laporan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('users', UserController::class)->only(['index', 'create', 'store', 'destroy']);

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
