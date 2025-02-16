<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Récupérer les informations d'un vendeur et ses produits
    public function getSellerDetails($id)
    {
        // Vérifier si le vendeur existe
        $seller = User::with('products')->find($id);

        // Modifier le chemin de la photo de profil du vendeur
        if ($seller->photo) {
            $seller->photo = url('storage/' . $seller->photo);
        }

        // Modifier les chemins des images des produits
        foreach ($seller->products as $product) {
            if ($product->defaultImage) {
                $product->defaultImage = url('storage/' . $product->defaultImage);
            }
        }

        if (!$seller) {
            return response()->json(['message' => 'Vendeur non trouvé'], 404);
        }

        // Retourner les informations du vendeur et ses produits
        return response()->json($seller);
    }
}
