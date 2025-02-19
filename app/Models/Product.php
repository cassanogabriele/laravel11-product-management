<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';

    protected $fillable = [
        'libelle',
        'reference',
        'description',
        'quantite',
        'prix',
        'poids',
        'category_id',
        'defaultImage',
        'id_utilisateur', 
    ];

    // Relation avec l'utilisateur
    public function user()
    {
        return $this->belongsTo(User::class, 'id_utilisateur', 'id'); 
    }

    // Relation avec la table "product_images"
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }        

    // Relation avec la table "Catégories"
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
