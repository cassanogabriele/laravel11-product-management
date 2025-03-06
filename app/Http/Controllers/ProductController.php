<?php
namespace App\Http\Controllers;

use App\Models\Product; 
use App\Models\RecentlyViewed;
use App\Models\User; 
use Illuminate\Http\Request;
use App\Models\ProductImage; 
use App\Models\Category;  
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // Récupérer tous les produits du site 
    public function getAllProducts()
    {
        $products = Product::all();

        // Ajouter l'URL complète pour l'image de chaque produit
        foreach ($products as $product) {
            if ($product->defaultImage) {
                $product->defaultImage = url('storage/' . $product->defaultImage);
            }
        }

        return response()->json($products, 200);
    }
    
    // Récupérer les produits de l'utilisateur connecté
    public function getProducts(Request $request, $userId)
    {
        $products = Product::where('id_utilisateur', $userId)->get();
    
         foreach ($products as $product) {
            if ($product->defaultImage) {
                $product->defaultImage = url('storage/' . $product->defaultImage);
            }
        }
    
        return response()->json($products, 200);
    }    

    public function getProductByCategory($categoryId)
    {
        // Récupérer les produits qui appartiennent à une catégorie spécifique et leur utilisateur associé
        $products = Product::with('user')
                           ->where('category_id', $categoryId)
                           ->get();
    
        foreach ($products as $product) {
            if ($product->defaultImage) {
                $product->defaultImage = url('storage/' . $product->defaultImage);
            }
        }
    
        // Retourner les produits avec les informations de l'utilisateur
        return response()->json($products);
    }
    
    // Pour la page d'accueil : afficher 3 articles de chaque catégorie 
    public function getLimitedProductsByCategory()
    {
        $categories = DB::select("
                            SELECT * FROM (
                                SELECT c.id AS category_id, c.name AS category_name, p.id AS product_id, p.libelle, p.defaultImage, p.description,
                                    ROW_NUMBER() OVER (PARTITION BY c.id ORDER BY p.created_at DESC) AS row_num
                                FROM categories c
                                LEFT JOIN products p ON c.id = p.category_id
                            ) AS categorized_products
                            WHERE row_num <= 3;
                        ");
    
        foreach ($categories as $category) {
            if ($category->defaultImage) {
                $category->defaultImage = url('storage/' . $category->defaultImage);
            }
        }
    
        return response()->json($categories);
    }
    
    public function getProductById($id)
    {
        // Récupérer le produit avec les informations de l'utilisateur et ses images associées
        $product = Product::with('user', 'images')->find($id);

        // Vérifier si le produit existe
        if (is_null($product)) {
            return response()->json(['message' => 'Produit introuvable'], 404);
        }

        if ($product->defaultImage) {
            $product->defaultImage = url('storage/' . $product->defaultImage);
        }

        foreach ($product->images as $image) {
            $image->image_path = url('storage/' . $image->image_path);
        }

        return response()->json($product, 200);
    }

    // Récupérer les produits récemment ajoutés 
    public function getRecentProducts($limit = 3)
    {
        $recentProducts = Product::orderBy('created_at', 'desc')->take($limit)->get();

        foreach ($recentProducts as $product) {
            if ($product->defaultImage) {
                $product->defaultImage = url('storage/' . $product->defaultImage);
            }
        }

        return response()->json($recentProducts, 200);
    }
   
    // Enregistrer l'article vu par l'utilisateur
    public function recordViewedProduct($productId, $userId)
    {
        // Vérifier que le produit existe        
        $product = Product::find($productId);
        
        if (!$product) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }

        RecentlyViewed::create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);

        return response()->json(['message' => 'Produit enregistré avec succès']);
    }

    // Récupérer les 3 derniers produits récemment vus par l'utilisateur
    public function getRecentlyViewed($userId)
    {
        $recentProducts = RecentlyViewed::where('user_id', $userId)
                                        ->latest()
                                        ->take(3)
                                        ->with('product')
                                        ->get();
    
        foreach ($recentProducts as $entry) {
            if ($entry->product && !filter_var($entry->product->defaultImage, FILTER_VALIDATE_URL)) {
                $entry->product->defaultImage = url('storage/' . $entry->product->defaultImage);
            }
        }
    
        return response()->json($recentProducts);
    }
    
    // Ajouter un produit
    public function addProduct(Request $request)
    {     
        // Validation des données 
        $validatedData = $request->validate([
            'libelle' => 'required|string|max:255',
            'reference' => 'required|string|max:255',
            'description' => 'required|string',
            'quantite' => 'required|integer',
            'prix' => 'required|numeric',
            'poids' => 'required|numeric',
            'id_utilisateur' => 'required|integer|exists:users,id',
            'category_id' => 'required|integer|exists:categories,id',
            'defaultImage' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'otherImages' => 'nullable|array',
            'otherImages.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Vérifier si la catégorie existe réellement
        $category = Category::find($validatedData['category_id']);
        if (!$category) {
            return response()->json(['error' => 'La catégorie spécifiée est invalide.'], 400);
        }

        // Gestion de l'image par défaut avant la création
        if ($request->hasFile('defaultImage')) {
            // Sauvegarde du chemin relatif dans la base de données
            $defaultImagePath = $request->file('defaultImage')->store('products', 'public');
        } else {
            return response()->json(['error' => 'Aucune image par défaut envoyée.'], 400);
        }

        $poids = (float) $validatedData['poids'];

        // Création du produit sans l'image par défaut
        $product = Product::create([
            'libelle' => $validatedData['libelle'],
            'reference' => $validatedData['reference'],
            'description' => $validatedData['description'],
            'quantite' => $validatedData['quantite'],
            'prix' => $validatedData['prix'],
            'poids' => $poids,
            'id_utilisateur' => $validatedData['id_utilisateur'],
            'category_id' => $validatedData['category_id'],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(), // Mise à jour de l'horodatage
            'defaultImage' => $defaultImagePath, // Enregistrer le chemin relatif
        ]);

        // Gestion des autres images du produit
        if ($request->hasFile('otherImages')) {
            foreach ($request->file('otherImages') as $image) {
                $imagePath = $image->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath, 
                ]);
            }
        }

        return response()->json(['message' => 'Produit créé avec succès!', 'product' => $product], 201);
    }

    // Mettre à jour un produit    
    public function updateProduct(Request $request, $id)
    {
        $product = Product::find($id);

        if(is_null($product))
        {
            return response()->json(['message' => 'Produit introuvable', 404]);
        } 

        $validatedData = $request->validate([
            'libelle' => 'required|string|max:255',
            'reference' => 'required|string|max:255',
            'description' => 'required|string',
            'quantite' => 'required|integer',
            'prix' => 'required|numeric',
            'updated_at' => now(), 
        ]);
        

        $product->update($request->all());

        return response($product, 200); 
    }

    // Supprimer un produit
    public function deleteProduct(Request $request, $id)
    {
        // Récupérer le produit
        $product = Product::find($id);

        // Vérifier s'il existe
        if (is_null($product)) {
            return response()->json(['message' => 'Produit introuvable'], 404);
        }

        // Supprimer les images liées au produit
        foreach ($product->images as $image) {
                      if (file_exists(public_path('uploads/images/' . $image->filename))) {
                unlink(public_path('uploads/images/' . $image->filename));
            }

            // Supprimer l'entrée dans la table 'product_images'
            $image->delete();
        }

        // Supprimer le produit
        $product->delete();

        // Retourner une réponse de succès
        return response(null, 204); 
    }
}


