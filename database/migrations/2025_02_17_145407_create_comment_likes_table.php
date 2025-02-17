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
        Schema::create('comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Lien avec l'utilisateur
            $table->foreignId('comment_id')->constrained()->onDelete('cascade'); // Lien avec le commentaire
            $table->timestamps();

            // Ajouter les index pour optimiser les requêtes
            $table->index('user_id');     // Index sur 'user_id'
            $table->index('comment_id');  // Index sur 'comment_id'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_likes');
    }
};
