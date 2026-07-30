<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StrukturAnggotaController;


Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/ganti-password', [LoginController::class, 'changepassword']);
    Route::post('/reset-password', [LoginController::class, 'Resetpassword']);
    Route::prefix('struktur_anggota')->group(function () {
        Route::post('/data', [StrukturAnggotaController::class, 'index']);
        Route::post('/data/store', [StrukturAnggotaController::class, 'store']);
        Route::post('/data/update', [StrukturAnggotaController::class, 'update']);
        Route::post('/data/destroy', [StrukturAnggotaController::class, 'destroy']);

    });
}

);