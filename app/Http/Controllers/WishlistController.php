<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;


class WishlistController extends Controller
{
    // Vérifier si l'utilisateur à une whislist
    public function getWishlist(Request $request, $userId)
    {
        $wishlists = Wishlist::where('user_id', $userId)
            ->with('products')  // Charger les produits associés
            ->get();    
    
        // Ajouter l'URL complète pour l'image par défaut et pour les autres images
        foreach ($wishlists as $wishlist) {
            foreach ($wishlist->products as $product) {
                // Ajouter l'URL complète pour l'image par défaut du produit
                if ($product->defaultImage) {
                    $product->defaultImage = url('storage/' . $product->defaultImage);
                }
    
                // Ajouter l'URL complète pour les autres images du produit
                foreach ($product->images as $image) {
                    // Ajouter l'URL complète pour chaque image du produit
                    $image->image_path = url('storage/' . $image->image_path);
                }
            }
        }
    
        return response()->json([
            'wishlists' => $wishlists
        ], 200);
    }
    
    // Créer une liste de souhait (ajouter le produit si c'est la première)
    public function createWishlist(Request $request)
    {
        $userId = $request->input('userId');
        $productId = $request->input('productId', null);  // Optionnel, null si non passé
        $wishlistName = $request->input('name');  // Nom de la wishlist

        // Vérifier si l'utilisateur a déjà une wishlist
        $existingWishlists = Wishlist::where('user_id', $userId)->count();

        // Créer une nouvelle wishlist avec le nom passé
        $wishlist = Wishlist::create([
            'user_id' => $userId,
            'name' => $wishlistName,  // Utiliser le nom passé dans la requête
        ]);

        // Si c'est la première wishlist et qu'un produit est fourni, ajouter le produit
        WishlistItem::create([
            'wishlist_id' => $wishlist->id,
            'product_id' => $productId
        ]);

        return response()->json([
            'message' => 'Wishlist créée avec succès',
            'wishlist' => $wishlist,
            'product_added' => ($existingWishlists == 0 && $productId) ? true : false
        ], 201);
    }

    // Ajouter un produit à la liste de souhaits
    public function addProduct(Request $request)
    {
        // Récupérer les données depuis la requête
        $userId = $request->input('userId');
        $wishlistId = $request->input('wishlistId');
        $productId = $request->input('productId');
    
        // Récupérer la wishlist de l'utilisateur
        $wishlist = Wishlist::where('id', $wishlistId)->where('user_id', $userId)->first();
    
        if (!$wishlist) {
            return response()->json(['message' => 'Wishlist non trouvée'], 404);
        }
    
        // Récupérer le produit
        $product = Product::find($productId);
    
        if (!$product) {
            return response()->json(['message' => 'Produit introuvable'], 404);
        }
    
        // Ajouter le produit à la wishlist
        $wishlistItem = new WishlistItem([
            'product_id' => $product->id,
        ]);
    
        $wishlist->items()->save($wishlistItem);
    
        return response()->json(['message' => 'Produit ajouté à la wishlist'], 200);
    }
    
    public function removeProduct($wishlistId, $productId)
    {
        // Rechercher l'élément dans la wishlist
        $wishlistItem = WishlistItem::where('wishlist_id', $wishlistId)
                                    ->where('product_id', $productId)
                                    ->first();

        if ($wishlistItem) {
            $wishlistItem->delete();
            return response()->json(['message' => 'Produit retiré de la wishlist']);
        }

        return response()->json(['message' => 'Produit introuvable'], 404);
    }
}
