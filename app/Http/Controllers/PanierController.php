<?php

namespace App\Http\Controllers;

use App\Models\Panier;
use App\Models\Tarif;
use App\Models\Product;
use Illuminate\Http\Request;

class PanierController extends Controller
{
    // Ajouter un produit au panier
    public function addToCart(Request $request)
    {    
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantite' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
    
        // Vérifie si le produit est déjà dans le panier
        $panier = Panier::where('user_id', $request->userId)
                        ->where('product_id', $product->id)
                        ->first();
    
        // Vérification dans les logs
        if ($panier) {
            \Log::info('Produit déjà dans le panier, mise à jour...');
            $panier->quantite += $request->quantite;
            $panier->prix = $product->prix * $panier->quantite;
            $panier->save();
        } else {
            \Log::info('Ajout d\'un nouveau produit au panier...');
            Panier::create([
                'user_id' => $request->userId,
                'product_id' => $product->id,
                'quantite' => $request->quantite,
                'prix' => $product->prix * $request->quantite,
            ]);
        }
    
        return response()->json(['message' => 'Produit ajouté au panier']);
    }
    

    // Afficher le panier  
    public function showCart(Request $request)
    {
        // Récupérer l'ID de l'utilisateur envoyé par Angular
        $userId = $request->userId;
    
        // Vérifier que l'ID utilisateur est bien présent
        if (!$userId) {
            return response()->json(['error' => 'Utilisateur non spécifié'], 400);
        }
    
        // Récupérer les articles du panier pour cet utilisateur
        $cartItems = Panier::with('product.user') 
                            ->where('user_id', $userId) 
                            ->get();
    
        $cartData = [];
    
        // Regrouper les articles par vendeur
        $groupedCartItems = [];

        // Compte le nombre d'articles
        $uniqueProductCount = 0; 
    
        foreach ($cartItems as $cartItem) {
            $product = $cartItem->product;
            $vendeur = $product->user; 
            $poids = $product->poids;

            // Vérification des frais de livraison en fonction du poids du produit
            $tarif = Tarif::where('poids_min', '<=', $poids)
                          ->where('poids_max', '>=', $poids)
                          ->first();
        
            // Si un tarif est trouvé, on récupère son tarif, sinon, on met à zéro ou un autre tarif par défaut
            $frais = $tarif ? $tarif->tarif : 0;
    
            // Ajouter l'URL complète pour l'image par défaut du produit
            if ($product->defaultImage) {
                $product->defaultImage = url('storage/' . $product->defaultImage);
            }
    
            // Préparer les données de l'article
            $itemData = [
                'product_id' => $product->id,
                'libelle' => $product->libelle,
                'quantite' => $cartItem->quantite,
                'prix' => $cartItem->prix,
                'defaultImage' => $product->defaultImage,
                'vendeur' => [
                    'name' => $vendeur->name, 
                    'email' => $vendeur->email, 
                ],
                'poids' => $poids,
                'frais_livraison' => $frais,
                'total_article' => $cartItem->quantite * $cartItem->prix + $frais, 
            ];
            
            if (!isset($groupedCartItems[$vendeur->id])) {
                $groupedCartItems[$vendeur->id] = [
                    'vendeur' => $vendeur, 
                    'items' => []
                ];
            }

            $uniqueProductCount = count($cartItems);    
   
            $groupedCartItems[$vendeur->id]['items'][] = $itemData;
        }
    
        // Calculer le total du panier (y compris les frais de livraison)
        $total = 0;
        
        foreach ($groupedCartItems as $vendorGroup) {
            foreach ($vendorGroup['items'] as $item) {
                $total += $item['total_article'];
            }
        }
    
        // Retourner les données groupées par vendeur et le total du panier
        return response()->json([
            'cartItems' => $groupedCartItems,
            'total' => $total,
            'uniqueProductCount' => $uniqueProductCount, 
        ]);
    }    

    public function showCartPreview(Request $request)
    {
        $userId = $request->userId;
        
      
        if (!$userId) {
            return response()->json(['error' => 'Utilisateur non spécifié'], 400);
        }
        
        $cartItems = Panier::with('product.user') 
                            ->where('user_id', $userId) 
                            ->get();
        
        $cartData = [];
        
        $groupedCartItems = [];   
        $uniqueProductCount = 0; 
        $total = 0;  
        
        foreach ($cartItems as $cartItem) {      
            $product = $cartItem->product;
            $vendeur = $product->user;
            $poids = $product->poids;
    
            $tarif = Tarif::where('poids_min', '<=', $poids)
                          ->where('poids_max', '>=', $poids)
                          ->first();
              
            $frais = $tarif ? $tarif->tarif : 0;
    
            if ($product->defaultImage) {
                $product->defaultImage = url('storage/' . $product->defaultImage);
            }
        
            $itemData = [
                'product_id' => $product->id,
                'libelle' => $product->libelle,
                'quantite' => $cartItem->quantite,
                'prix' => $cartItem->prix,
                'defaultImage' => $product->defaultImage,
                'vendeur' => [
                    'name' => $vendeur->name,
                    'email' => $vendeur->email,
                ],
                'poids' => $poids,
                'frais_livraison' => $frais,
                'total_article' => $cartItem->quantite * $cartItem->prix + $frais, 
            ];
    
      
            if (!isset($groupedCartItems[$vendeur->id])) {
                $groupedCartItems[$vendeur->id] = [
                    'vendeur' => $vendeur, 
                    'items' => []
                ];
            }
    
            if (!isset($groupedCartItems[$product->id])) {             
                $uniqueProductCount++; 
            }
    
            $groupedCartItems[$vendeur->id]['items'][] = $itemData;
            $total += $itemData['total_article'];
        }
        
        // Limiter à 3 articles au total
        $limitedGroupedCartItems = [];     
    
        foreach ($groupedCartItems as $vendorGroup) {
            // Limiter les articles par vendeur à 3
            $limitedItems = array_slice($vendorGroup['items'], 0, 3); // Limiter à 3 articles par vendeur
    
            // Ajouter les articles limités à la nouvelle structure
            $limitedGroupedCartItems[$vendorGroup['vendeur']->id] = [
                'vendeur' => $vendorGroup['vendeur'],
                'items' => $limitedItems
            ];
        }
        
        // Retourner les données groupées par vendeur et le total du panier limité
        return response()->json([
            'cartItems' => $limitedGroupedCartItems,  
            'total' => $total,          
            'uniqueProductCount' => $uniqueProductCount, 
        ]);
    }
    
    // Supprimer un produit du panier
    public function removeFromCart(Request $request, $userId, $productId)
    {
        $deleted = Panier::where('user_id', $userId)->where('product_id', $productId)->delete();
    
        if ($deleted) {
            return response()->json(['message' => 'Produit supprimé du panier']);
        } else {
            return response()->json(['error' => 'Produit non trouvé dans le panier'], 404);
        }
    }

    // Mettre à jour la quantité d'un produit dans le panier
    public function updateCartItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantite' => 'required|integer|min:1',
        ]);

        $panierItem = Panier::where('user_id', $request->user_id)
                            ->where('product_id', $request->product_id)
                            ->first();
        
        if ($panierItem) {
            $panierItem->quantite = $request->quantite;
            $panierItem->save();
        }

        return response()->json(['message' => 'Quantité mise à jour avec succès']);
    }
}
