<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductImagesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table pour les images des produits (création de cette nouvelle table sans affecter la table des produits)
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Lien avec la table products
            $table->string('image_path'); // Le chemin de l'image
            $table->timestamps();
        });
        
        // Ajouter une colonne pour l'image par défaut dans la table 'products'
        Schema::table('products', function (Blueprint $table) {
            $table->string('default_image')->nullable(); // Nouvelle colonne pour l'image par défaut
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pour revenir en arrière, on supprime les nouvelles colonnes et la table des images
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('default_image');
        });

        Schema::dropIfExists('product_images');
    }
}
