<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// URL Prefix: http://localhost:8000/api/admin/
// endpoint for admin user
route::prefix('admin')->group(function() {
    route::get('user', [UserController::class , 'index']);
});

// URL Prefix: http://localhost:8000/api/user/
// endpoint for basic users
route::apiResource('user', UserController::class)->except(['index']);



