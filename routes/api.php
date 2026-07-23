<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;


Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/ganti-password', [LoginController::class, 'changepassword']);
    Route::post('/reset-password', [LoginController::class, 'Resetpassword']);
}
);