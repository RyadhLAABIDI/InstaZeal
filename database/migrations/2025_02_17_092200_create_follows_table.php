<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('follows')) {
            Schema::create('follows', function (Blueprint $table) {
                $table->id();
                $table->foreignId('follower_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('followed_id')->constrained('users')->onDelete('cascade');
                $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
                $table->enum('relationship', ['friend', 'close_friend'])->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('follows')) { // ✅ Vérifie l'existence de la table
            Schema::table('follows', function (Blueprint $table) {
                // Supprime les clés étrangères si elles existent
                $table->dropForeign(['follower_id']);
                $table->dropForeign(['followed_id']);
            });
            Schema::dropIfExists('follows');
        }
    }
};