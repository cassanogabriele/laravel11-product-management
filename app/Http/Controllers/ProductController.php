<?php
namespace App\Http\Controllers;

use App\Models\Product; 
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getProduct()
    {
        return response()->json(Product::all(), 200); 
    }

    public function getProductById($id)
    {
        $product = Product::find($id);

        if(is_null($product))
        {
            return response()->json(['message' => 'Produit introuvable', 404]);
        }

        return response()->json(Product::find($id), 200);
    }

    public function addProduct(Request $request)
    {
        $validatedData = $request->validate([
            'libelle' => 'required|string|max:255',
            'reference' => 'required|string|max:255',
            'description' => 'required|string',
            'quantite' => 'required|integer',
            'prix' => 'required|numeric',
        ]);
        
        $product = Product::create($request->all());
        return response($product, 200); 
    }   

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
        ]);
        

        $product->update($request->all());

        return response($product, 200); 
    }

    public function deleteProduct(Request $request, $id)
    {
        $product = Product::find($id);

        if(is_null($product))
        {
            return response()->json(['message' => 'Produit introuvable', 404]);
        } 

        $product->delete();

        return response(null, 204); 
    }    
}


