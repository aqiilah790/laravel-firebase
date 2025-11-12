<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FirebaseController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/users', function () {
    return view('users');
});

// Route::get('/mahasiswa', function () {
//     return view('mahasiswa.index');
// });

// AUTH
Route::get('/register', [AuthController::class, 'showRegister']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);

// HALAMAN MAHASISWA
// Route::middleware(function ($request, $next) {
//     if (!session('firebase_user')) {
//         return redirect('/login');
//     }
//     return $next($request);
// })->group(function () {
//     Route::get('/mahasiswa', function () {
//         return view('mahasiswa.index');
//     });
// });

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


// Route::get('/api/mahasiswa', [MahasiswaController::class, 'index']);
// Route::post('/api/mahasiswa', [MahasiswaController::class, 'store']);
// Route::put('/api/mahasiswa/{id}', [MahasiswaController::class, 'update']);
// Route::delete('/api/mahasiswa/{id}', [MahasiswaController::class, 'destroy']);

// Route::get('/users', [FirebaseController::class, 'index']);
// Route::post('/users', [FirebaseController::class, 'store']);
// Route::put('/users/{id}', [FirebaseController::class, 'update']);
// Route::delete('/users/{id}', [FirebaseController::class, 'delete']);
