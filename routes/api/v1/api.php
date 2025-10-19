<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Property\PropertyController;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/user', [UserController::class, 'getUser']);
    Route::put('/user/update-profile', [UserController::class, 'updateUser']);
    /** property routes */ 
    Route::apiResource('properties', PropertyController::class);
});
Route::get('/featured-properties/{property_type?}', [PropertyController::class, 'featuredProperties']);
Route::get('/get-property/{property}', [PropertyController::class, 'getProperty']);
Route::post('/search-properties', [PropertyController::class, 'searchProperties']);
