<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FirebaseController;

Route::get('/', function () {
    return view('auth/login');
});

Route::get('/users', function () {
    return view('users');
});

// AUTH
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);

Route::get('/mahasiswa', function () {
    if (!session('firebase_user')) {
        return redirect('/login');
    }
    return view('mahasiswa.index');
});

Route::get('/verifikasi', function () {
    return view('auth.verifikasi');
});

Route::get('/lengkapi-data', [AuthController::class, 'showLengkapiData']);
Route::post('/lengkapi-data', [AuthController::class, 'lengkapiData']);

Route::post('/admin/tambah-user', [AdminController::class, 'tambahUser'])->middleware('auth.session');
Route::post('/admin/update-user', [AdminController::class, 'updateUser'])->middleware('auth.session');
Route::delete('/admin/hapus-user', [AdminController::class, 'hapusUser'])->name('admin.hapusUser')->middleware('auth.session');




