<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

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

// Récupérer tous les produits
Route::get('products', [ProductController::class, 'getProduct']);
// Récupérer un article
Route::get('product/{id}', [ProductController::class, 'getProductById']);
// Ajouter un article 
Route::post('addProduct', [ProductController::class, 'addProduct']);
// Mettre à jour un article 
Route::put('updateProduct/{id}', [ProductController::class, 'updateProduct']);
// Mettre à jour un article 
Route::delete('deleteProduct/{id}', [ProductController::class, 'deleteProduct']);
