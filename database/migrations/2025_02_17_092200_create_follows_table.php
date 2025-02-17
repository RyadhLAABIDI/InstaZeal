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
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); 
            $table->foreignId('follower_id')->constrained('users')->onDelete('cascade'); // Lien avec l'utilisateur qui suit
            $table->foreignId('followed_id')->constrained('users')->onDelete('cascade'); // Lien avec l'utilisateur suivi
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending'); // Statut de l'abonnement
            $table->enum('relationship', ['friend', 'close_friend'])->nullable(); // Nouveau champ pour gérer les relations d'amis proches
            $table->timestamps();
            $table->softDeletes(); // Suppression douce pour gérer les abonnements supprimés
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
