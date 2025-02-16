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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Insérer les catégories par défaut
        DB::table('categories')->insert([
            ['name' => 'AUDIO'],
            ['name' => 'VÊTEMENTS'],
            ['name' => 'TÉLÉPHONIE'],
            ['name' => 'TÉLÉVISION'],
            ['name' => 'PHOTO'],
            ['name' => 'ACCESSOIRES VIRTUELS'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
