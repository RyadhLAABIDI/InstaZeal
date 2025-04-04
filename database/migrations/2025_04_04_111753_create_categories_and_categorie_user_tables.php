<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Categorie;

return new class extends Migration
{
    public function up()
    {
        // Créer la table des catégories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom de la catégorie (ex. Sport, Cinéma & Séries)
            $table->timestamps();
        });

        // Insérer les catégories prédéfinies
        $categories = [
            'Sport',
            'Éducation',
            'Cinéma & Séries',
            'Cuisine & Gastronomie',
            'Voyage',
            'Mode & Beauté',
            'Technologie',
            'Musique',
            'Art & Culture',
            'Animaux & Nature',
            'Entrepreneuriat & Business',
            'Gaming',
            'Santé & Bien-être',
            'Finance & Investissement',
            'Lifestyle'
        ];

        foreach ($categories as $category) {
            Categorie::create(['name' => $category]);
        }

        // Créer la table de liaison entre les utilisateurs et les catégories (relation many-to-many)
        Schema::create('categorie_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Lier à la table 'users'
            $table->foreignId('categorie_id')->constrained('categories')->onDelete('cascade'); // Lier à la table 'categories'
            $table->timestamps();
        });
    }

    public function down()
    {
        // Supprimer la table de liaison 'categorie_user'
        Schema::dropIfExists('categorie_user');
        
        // Supprimer la table 'categories'
        Schema::dropIfExists('categories');
    }
};