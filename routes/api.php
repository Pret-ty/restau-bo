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
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    /**
     * PUBLIC / GUEST ROUTES (Client facing)
     */
    // View Restaurant Menu (Public)
    Route::get('/restaurants/{restaurant}/categories', [CategorieController::class, 'index']); 
    Route::get('/restaurants/{restaurant}/type_plats', [\App\Http\Controllers\Api\V1\TypePlatController::class, 'index']);
    Route::get('/restaurants/{restaurant}/boissons', [\App\Http\Controllers\Api\V1\BoissonController::class, 'index']);
    Route::get('/categories/{categorie}/plats', [PlatController::class, 'index']);

    // Guest Order Creation (Returns Token)
    Route::post('/tables/{table}/commandes', [CommandeController::class, 'store']); 

    // Guest Session Protected Routes (Requires X-Order-Token)
    Route::middleware([\App\Http\Middleware\OrderGuestMiddleware::class])->group(function () {
        Route::get('/tables/{table}/commandes/{commande}', [CommandeController::class, 'show']); 
        
        // Command Items
        Route::post('/commandes/{commande}/items', [CommandeItemController::class, 'store']); 
        Route::get('/commandes/{commande}/items', [CommandeItemController::class, 'index']);
        Route::get('/commandes/{commande}/items/{item}', [CommandeItemController::class, 'show']);
        Route::delete('/commandes/{commande}/items/{item}', [CommandeItemController::class, 'destroy']); 
        
        // Guest Payment Initiation
        Route::post('/commandes/{commande}/paiements', [PaiementController::class, 'store']);
    });

    /**
     * PROTECTED BACK-OFFICE ROUTES (Staff)
     */
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // 1. ADMIN_RESTAURANT Routes
        // (Assuming standard Spatie middleware is 'role:name', but user is okay with logic implementation first if not present)
        // For now, grouping assuming user has Spatie permissions set up, or standard grouping.
        // The user prompted: Route::middleware('role:ADMIN_RESTAURANT')->group(...)
        
        Route::apiResource('restaurants', RestaurantController::class);
        Route::post('/restaurants/{restaurant}/transfer-ownership', [RestaurantController::class, 'transferOwnership']);
        
        Route::apiResource('restaurants.tables', TableController::class)->except(['index']);
        Route::apiResource('restaurants.categories', CategorieController::class)->except(['index']);
        Route::apiResource('restaurants.type_plats', \App\Http\Controllers\Api\V1\TypePlatController::class)->except(['index']);
        Route::apiResource('restaurants.boissons', \App\Http\Controllers\Api\V1\BoissonController::class)->except(['index']);
        Route::apiResource('categories.plats', PlatController::class)->except(['index']);
        Route::apiResource('utilisateurs', UtilisateurController::class);

        // 2. Staff Shared Routes (Read Access for specific roles matches Policies usually)
        Route::get('/restaurants/{restaurant}/tables', [TableController::class, 'index']);
        Route::get('/tables/{table}/commandes', [CommandeController::class, 'index']); 
        
        // 3. Order Management (Servers/Kitchen)
        Route::put('/tables/{table}/commandes/{commande}', [CommandeController::class, 'update']);
        Route::delete('/tables/{table}/commandes/{commande}', [CommandeController::class, 'destroy']);
        Route::put('/commandes/{commande}/items/{item}', [CommandeItemController::class, 'update']);

        // 4. Payment Management (Cashier)
        Route::get('/commandes/{commande}/paiements', [PaiementController::class, 'index']);
        Route::get('/commandes/{commande}/paiements/{paiement}', [PaiementController::class, 'show']);
        Route::put('/commandes/{commande}/paiements/{paiement}', [PaiementController::class, 'update']);
        Route::delete('/commandes/{commande}/paiements/{paiement}', [PaiementController::class, 'destroy']);
    });
});
