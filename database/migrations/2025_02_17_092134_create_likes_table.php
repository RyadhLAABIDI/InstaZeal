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
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Lien avec l'utilisateur
            $table->foreignId('post_id')->constrained()->onDelete('cascade'); // Lien avec le post
            $table->timestamps();

            // Ajouter les index sur 'user_id' et 'post_id'
            $table->index('user_id');  // Index sur 'user_id'
            $table->index('post_id');  // Index sur 'post_id'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
