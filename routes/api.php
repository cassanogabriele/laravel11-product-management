<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\PanierController;


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
 

// L'utilisateur 
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/update-profile', [AuthController::class, 'updateUserProfile']);   
});

// Les produits

// Récupérer tous les produits du site
Route::get('/allProducts', [ProductController::class, 'getAllProducts']);
// Récupérer tous les produits de l'utilisateur connecté
Route::get('products/{id}', [ProductController::class, 'getProducts']);
// Récupérer un article
Route::get('product/{id}', [ProductController::class, 'getProductById']);

// Récupérer les informations du vendeur et ses produits
Route::get('/seller/{id}', [UserController::class, 'getSellerDetails']);

// Récupérer les catégories des produits
Route::get('/categories', [CategoryController::class, 'getCategories']);

// Pour la page d'accueil

// Récupérer les articles les plus récents 
Route::get('recentProducts', [ProductController::class, 'getRecentProducts']);
// Enregistrer chaque produit vu par l'utilisatuer
Route::post('/product/viewed/{productId}/{userId}', [ProductController::class, 'recordViewedProduct']);
// Récupérer les produits vu par l'utilisateur
Route::get('/products/recently-viewed/{userId}', [ProductController::class, 'getRecentlyViewed']);

// Récupérer les produits par catégorie 
Route::get('/articles/category/{categoryId}', [ProductController::class, 'getProductByCategory']);
// Récupérer les titres des catégories 
Route::get('/categories/{id}', [CategoryController::class, 'getCategoryById']);

// Récupérer 3 articles par catégorie (page d'accueil)
Route::get('/products-by-category', [ProductController::class, 'getLimitedProductsByCategory']);

// Ajouter un article 
Route::post('addProduct', [ProductController::class, 'addProduct']);
// Mettre à jour un article 
Route::put('updateProduct/{id}', [ProductController::class, 'updateProduct']);
// Mettre à jour un article 
Route::delete('deleteProduct/{id}', [ProductController::class, 'deleteProduct']);

// Liste de souhait  

// Vérifier si l'utilisateur à une liste de souhaites
Route::get('/wishlist/{userId}', [WishlistController::class, 'getWishlist']);
// Créer une liste de souhait 
Route::post('/createWishlist', [WishlistController::class, 'createWishlist']);
// Récupérer les listes de souhaits de l'utilisateur 
Route::get('wishlists/{userId}', [WishlistController::class, 'getUserWishlistsWithProducts']);
// Ajouter un produit
Route::post('/addProductToWhishlist', [WishlistController::class, 'addProduct']);
// Supprimer un produit à la liste de souhait
Route::delete('/removeProductFromWishlist/{wishlistId}/{productId}', [WishlistController::class, 'removeProduct']);

// Panier 

// Ajouter un produit au panier
Route::post('/addProductToCart', [PanierController::class, 'addToCart']); 
// Afficher le panier
Route::get('/cart', [PanierController::class, 'showCart']); 
// Supprimer un produit du panier
Route::delete('/cart/{userId}/{productId}', [PanierController::class, 'removeFromCart']);
// Mise à jour du panier, quand on modifie la quantité
Route::put('updateCartItem', [PanierController::class, 'updateCartItem']);

// Aperçu du panier 
Route::get('/cart-preview', [PanierController::class, 'showCartPreview']);
Route::get('/cart-total', [PanierController::class, 'getCartTotalItems']);



