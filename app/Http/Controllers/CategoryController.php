<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    // Récupérer toutes les catégories
    public function getCategories()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    // Récupérer le titre de la catégorie
    public function getCategoryById($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Catégorie non trouvée'], 404);
        }

        return response()->json($category);
    }
}
