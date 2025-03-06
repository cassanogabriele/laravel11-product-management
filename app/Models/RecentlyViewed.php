<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecentlyViewed extends Model
{
    use HasFactory;

    protected $table = 'recently_viewed';

    protected $fillable = ['user_id', 'product_id'];

    // Définir la relation entre RecentlyViewed et Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id'); 
    }

    // Récupère les produits récemment vus par l'utilisateur
    public static function getRecentlyViewed($userId)
    {
        return self::where('user_id', $userId)
                    ->latest()
                    ->take(5) // Récupère les 5 derniers articles vus
                    ->with('product') // Charge les informations des produits
                    ->get();
    }
}
