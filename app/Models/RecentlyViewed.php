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
}
