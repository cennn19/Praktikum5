<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BeritaController;
use Illuminate\Support\Facades\Route;

// Redirect root ke berita
Route::redirect('/', '/berita');

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Berita (butuh login untuk create, store, edit, update, destroy)
Route::get('/berita', [BeritaController::class, 'index'])->name('berita.index');

Route::middleware('auth')->group(function () {
    Route::get('/berita/create', [BeritaController::class, 'create'])->name('berita.create');
    Route::post('/berita', [BeritaController::class, 'store'])->name('berita.store');
    Route::get('/berita/{berita}/edit', [BeritaController::class, 'edit'])->name('berita.edit');
    Route::put('/berita/{berita}', [BeritaController::class, 'update'])->name('berita.update');

    Route::middleware('admin')->group(function () {
        Route::delete('/berita/{berita}', [BeritaController::class, 'destroy'])->name('berita.destroy');
    });
});

// Harus paling bawah karena {berita} bisa bentrok dengan 'create'
Route::get('/berita/{berita}', [BeritaController::class, 'show'])->name('berita.show');
Route::post('/berita/{berita}/komentar', [BeritaController::class, 'komentar'])->name('berita.komentar');