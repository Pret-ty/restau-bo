<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\RestaurantController;
use App\Http\Controllers\Api\V1\TableController;
use App\Http\Controllers\Api\V1\CategorieController;
use App\Http\Controllers\Api\V1\PlatController;
use App\Http\Controllers\Api\V1\CommandeController;
use App\Http\Controllers\Api\V1\CommandeItemController;
use App\Http\Controllers\Api\V1\PaiementController;
use App\Http\Controllers\Api\V1\UtilisateurController;



Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Auth protégées
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });


    // Resources
    Route::post('/restaurants/{restaurant}/transfer-ownership', [RestaurantController::class, 'transferOwnership']);
    Route::apiResource('restaurants', RestaurantController::class);
    Route::apiResource('restaurants.tables', TableController::class);
    Route::apiResource('restaurants.categories', CategorieController::class);
    Route::apiResource('categories.plats', PlatController::class);
    Route::apiResource('tables.commandes', CommandeController::class);
    Route::apiResource('commandes.items', CommandeItemController::class);
    Route::apiResource('commandes.paiements', PaiementController::class);
    Route::apiResource('utilisateurs', UtilisateurController::class);
});
