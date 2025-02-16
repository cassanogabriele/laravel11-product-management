<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'image_path'];
    
    // Définir la table explicitement (facultatif si Laravel peut deviner le nom)
    protected $table = 'product_images';
}
