<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;


class WishlistController extends Controller
{
    // Récupérer les listes de souhait de l'utilisateur
    public function getWishlist(Request $request, $userId)
    {
        $wishlists = Wishlist::where('user_id', $userId)
            ->with('products')  
            ->get();    
    
        foreach ($wishlists as $wishlist) {
            foreach ($wishlist->products as $product) {
                if ($product->defaultImage) {
                    $product->defaultImage = url('storage/' . $product->defaultImage);
                }
    
                foreach ($product->images as $image) {
                    $image->image_path = url('storage/' . $image->image_path);
                }
            }
        }
    
        return response()->json([
            'wishlists' => $wishlists
        ], 200);
    }
    
    // Créer une liste de souhait (ajouter le produit si c'est la première liste)
    public function createWishlist(Request $request)
    {
        $userId = $request->input('userId');
        $productId = $request->input('productId', null);  
        $wishlistName = $request->input('name');  

        // Vérifier si l'utilisateur a déjà une liste de souhait
        $existingWishlists = Wishlist::where('user_id', $userId)->count();

        // Créer une nouvelle wishlist 
        $wishlist = Wishlist::create([
            'user_id' => $userId,
            'name' => $wishlistName,  
        ]);

        // Si c'est la première liste de souhait, ajouter le produit
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
    
        $product = Product::find($productId);
    
        if (!$product) {
            return response()->json(['message' => 'Produit introuvable'], 404);
        }
    
        // Ajouter le produit à la liste de souhait
        $wishlistItem = new WishlistItem([
            'product_id' => $product->id,
        ]);
    
        $wishlist->items()->save($wishlistItem);
    
        return response()->json(['message' => 'Produit ajouté à la wishlist'], 200);
    }
    
    // Supprimer un produit de la liste de souhaits
    public function removeProduct($wishlistId, $productId)
    {
        // Rechercher l'élément dans la liste de souhait
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
