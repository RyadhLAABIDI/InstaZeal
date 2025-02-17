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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Lien avec l'utilisateur qui a commenté
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade'); // Lien avec le post commenté
            $table->text('content'); // Contenu du commentaire
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade'); // Lien avec un commentaire parent (si réponse)
            $table->timestamps();

            // Ajouter un index sur parent_id pour améliorer les performances des requêtes liées aux réponses
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
