<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthenticationController;
use \App\Http\Controllers\Api\BookController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['namespace' => 'Api', 'prefix' => 'v1'], function () {
    Route::post('login', [AuthenticationController::class, 'store']);
    Route::post('register', [AuthenticationController::class, 'register']);

    Route::middleware('auth:api')->group( function () {
        Route::post('logout', [AuthenticationController::class, 'destroy']);

        Route::get('/books', [BookController::class, 'index']);
        Route::get('/book/{id}', [BookController::class, 'show']);
        Route::post('/rent/{id}', [BookController::class, 'rent']);
        Route::put('/return/{id}', [BookController::class, 'return']);

        Route::get('/rental-history', [BookController::class, 'rentals']);

        Route::get('/stats', [BookController::class, 'stats']);
    });
});
