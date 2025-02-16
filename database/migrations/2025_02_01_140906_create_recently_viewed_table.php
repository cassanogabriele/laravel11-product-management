<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recently_viewed', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('user_id'); 
            $table->unsignedBigInteger('product_id'); 
            $table->timestamps(); 

            // Ajout de la clé étrangère pour l'utilisateur
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Ajout de la clé étrangère pour le produit
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recently_viewed');
    }
};
