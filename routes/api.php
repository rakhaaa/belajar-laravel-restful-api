<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ApiAuthMiddleware;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });



// User
Route::controller(UserController::class)->prefix('users')->group(function () {
    Route::post('/', 'register');
    Route::post('login', 'login');
});
Route::middleware(ApiAuthMiddleware::class)->controller(UserController::class)->prefix('users')->group(function () {
    Route::get('current', 'get');
    Route::patch('current', 'update');
    Route::delete('logout', 'logout');
});

// Contact
Route::middleware(ApiAuthMiddleware::class)->controller(ContactController::class)->prefix('contacts')->group(function () {
    Route::post('/', 'create');
    Route::get('/', 'search');
    Route::get('{id}', 'get')->where('id', '[0-9]+');
    Route::put('{id}', 'update')->where('id', '[0-9]+');
    Route::delete('{id}', 'delete')->where('id', '[0-9]+');
});

// Address
Route::middleware(ApiAuthMiddleware::class)->controller(AddressController::class)->prefix('contacts/{idContact}/addresses')->group(function () {
    Route::post('/', 'create')->where('idContact', '[0-9]+');
    Route::get('/', 'list')->where('idContact', '[0-9]+');
    Route::get('{idAddress}', 'get')->where('idContact', '[0-9]+')->where('idAddress', '[0-9]+');
    Route::put('{idAddress}', 'update')->where('idContact', '[0-9]+')->where('idAddress', '[0-9]+');
    Route::delete('{idAddress}', 'delete')->where('idContact', '[0-9]+')->where('idAddress', '[0-9]+');
});
