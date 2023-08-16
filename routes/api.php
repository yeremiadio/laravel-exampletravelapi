<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

//register new user
Route::post('/register', [AuthenticationController::class, 'register']);
//login user
Route::post('/login', [AuthenticationController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::group(['prefix' => 'user'], function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::group(['middleware' => ['role:superadmin']], function () {
            Route::post('/create', [UserController::class, 'store']);
            Route::patch('/update/{id}', [UserController::class, 'update']);
            Route::post('/role/{id}', [UserController::class, 'assignRoleUser']);
            Route::delete('/delete/{id}', [UserController::class, 'destroy']);
        });
    });
    Route::group(['prefix' => 'destination'], function () {
        Route::get('/', [DestinationController::class, 'index']);
        Route::get('/{id}', [DestinationController::class, 'show']);
        Route::group(['middleware' => ['role:admin']], function () {
            Route::post('/create', [DestinationController::class, 'store']);
            Route::put('/update/{id}', [DestinationController::class, 'update']);
            Route::delete('/delete/{id}', [DestinationController::class, 'destroy']);
        });
    });
    Route::group(['prefix' => 'review'], function () {
        Route::get('/', [ReviewController::class, 'index']);
        Route::get('/{id}', [ReviewController::class, 'show']);
        Route::post('/{id}', [ReviewController::class, 'store']);
        Route::put('/update/{id}', [ReviewController::class, 'update']);
        Route::delete('/delete/{id}', [ReviewController::class, 'destroy']);
    });
    Route::post('logout', [AuthenticationController::class, 'logout']);
});
