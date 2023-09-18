<?php

use App\Http\Controllers\MessageController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/message', [MessageController::class, 'index']);
Route::get("/message/{id}", [MessageController::class, "getById"]);
Route::post('/message', [MessageController::class, 'store']);
Route::post('/message/edit/{id}', [MessageController::class, 'update']);
