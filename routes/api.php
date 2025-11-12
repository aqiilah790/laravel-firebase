<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FirebaseController;

Route::get('/users', [FirebaseController::class, 'index']);
Route::post('/users', [FirebaseController::class, 'store']);
Route::put('/users/{id}', [FirebaseController::class, 'update']);
Route::delete('/users/{id}', [FirebaseController::class, 'delete']);
