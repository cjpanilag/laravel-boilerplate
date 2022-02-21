<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderDetailController;
use App\Http\Controllers\ShipmentTrackerController;

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

// URL Prefix: http://localhost:8000/api/admin
// endpoint for admin user
route::prefix('admin')->group(function() {
    route::middleware(['auth:api'])->group(function() {
        route::prefix('user')->group(function() {
            route::apiResource('', UserController::class)->except(['self']);
            route::put('delete/{user}', [UserController::class, 'destroy']);
            route::put('approve/{user}', [UserController::class, 'approve']);
            route::put('restore/{user}', [UserController::class, 'restore']);
            route::get('count', [UserController::class, 'count']);  
        });

        // URL Prefix: http://localhost:8000/api/admin/store
        // endpoint for store admin
        route::prefix('store')->group(function () {
            route::get('', [StoreController::class, 'storeAdmin']);
            route::get('self', [StoreController::class, 'self']);
            route::apiResource('product', ProductController::class)->only(['store', 'update']);
            route::get('product/{store}', [ProductController::class, 'getStoreAdminProduct']);
        });
        
        // URL Prefix: http://localhost:8000/api/admin/shipment/update
        // Route for shipment activity
        route::prefix('shipment')->group(function() {
            route::post('update/{order}', [ShipmentTrackerController::class, 'updateOrderStatus']);
        });
    });
});

// URL Prefix: http://localhost:8000/api/register
route::post('register', [UserController::Class, 'register']);

// URL Prefix: http://localhost:8000/api/login
route::post('login', [AuthController::class, 'authenticate']);

// ping the server
route::get('ping', [AuthController::class, 'ping']);

// URL Prefix: http://localhost:8000/api/logout
route::post('logout', [AuthController::class, 'logout'])->middleware(['auth:api']);

// URL Prefix: http://localhost:8000/api/user
// endpoint for all user 
route::prefix('user')->group(function() {
    route::middleware(['auth:api'])->group(function() {
        route::get('self', [UserController::class, 'self']);
        route::get('category', [UserController::class, 'userCategory']);
    });
});

// URL Prefix: http://localhost:8000/api/store
// endpoint for store
route::prefix('store')->group(function() {
    route::get('product', [ProductController::class, 'index']);
    route::get('{store}', [StoreController::class, 'show']);
    route::middleware(['auth:api'])->group(function() {
        route::apiResource('', StoreController::class);
        route::get('product/{product}', [ProductController::class, 'show']);
    });
});

// URL Prefix: http://localhost:8000/api/cart
// endpoint product cart
route::apiResource('cart', CartController::class)->middleware(['auth:api']);

// URL Prefix: http://localhost:8000/api/order
// endpoint for orders
route::prefix('order')->group(function() {
    route::middleware(['auth:api'])->group(function() {
        route::apiResource('', OrderController::class)->except(['destroy']);
        route::delete('{order}', [OrderController::class, 'destroy']);
        route::apiResource('detail', OrderDetailController::class);
    });
});


