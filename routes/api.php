<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/users/register', [UserController::class, 'register'])->name('register');
Route::post('/users/login', [UserController::class, 'login'])->name('login');
Route::middleware('auth:api')->group(function () {
    Route::prefix('/users')->group(function () {
        // Refresh Token
        Route::post('/refresh', [UserController::class, 'refresh']);
        // User Management
        Route::get('/current', [UserController::class, 'get']);
        Route::patch('/current', [UserController::class, 'update']);
        Route::delete('/logout', [UserController::class, 'logout']);
    });

    Route::prefix('/contacts')->group(function () {
        // Contact Management
        Route::post('/', [ContactController::class, 'create']);
        Route::get('/', [ContactController::class, 'search']);
        Route::get('/{id}', [ContactController::class, 'get'])->where('id', '[0-9]+');
        Route::put('/{id}', [ContactController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/{id}', [ContactController::class, 'delete'])->where('id', '[0-9]+');

        // Address Management
        // Route::get('/{idContact}/addresses', [AddressController::class, 'create'])->where('idContact', '[0-9]+');
    });
});
