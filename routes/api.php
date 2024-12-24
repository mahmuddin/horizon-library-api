<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\LoanManagementController;
use App\Http\Controllers\Api\UserCategoryController;
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
        Route::get('/', [ContactController::class, 'list']);
        Route::get('/search', [ContactController::class, 'search']);
        Route::get('/{id}', [ContactController::class, 'get'])->where('id', '[0-9]+');
        Route::put('/{id}', [ContactController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/{id}', [ContactController::class, 'delete'])->where('id', '[0-9]+');

        // Address Management
        Route::post('/{idContact}/addresses', [AddressController::class, 'create'])->where('idContact', '[0-9]+');
        Route::get('/{idContact}/addresses', [AddressController::class, 'list'])->where('idContact', '[0-9]+');
        Route::get('/{idContact}/addresses/{idAddress}', [AddressController::class, 'get'])->where('idContact', '[0-9]+')->where('idAddress', '[0-9]+');
        Route::put('/{idContact}/addresses/{idAddress}', [AddressController::class, 'update'])->where('idContact', '[0-9]+')->where('idAddress', '[0-9]+');
        Route::delete('/{idContact}/addresses/{idAddress}', [AddressController::class, 'delete'])->where('idContact', '[0-9]+')->where('idAddress', '[0-9]+');
    });

    Route::prefix('/user_categories')->group(function () {
        // User Category Management
        Route::post('/', [UserCategoryController::class, 'create']);
        Route::get('/', [UserCategoryController::class, 'list']);
        Route::get('/search', [UserCategoryController::class, 'search']);
        Route::get('/{id}', [UserCategoryController::class, 'get'])->where('id', '[0-9]+');
        Route::put('/{id}', [UserCategoryController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/{id}', [UserCategoryController::class, 'delete'])->where('id', '[0-9]+');
    });

    Route::prefix('/loans')->group(function () {
        // Loan Management
        Route::post('/', [LoanManagementController::class, 'create']);
        Route::get('/', [LoanManagementController::class, 'list']);
        Route::get('/search', [LoanManagementController::class, 'search']);
        Route::get('/{id}', [LoanManagementController::class, 'get'])->where('id', '[0-9]+');
        Route::put('/{id}', [LoanManagementController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/{id}', [LoanManagementController::class, 'delete'])->where('id', '[0-9]+');
    });

    Route::prefix('/authors')->group(function () {
        // Authors Management

    });

    Route::prefix('/books')->group(function () {});
});
