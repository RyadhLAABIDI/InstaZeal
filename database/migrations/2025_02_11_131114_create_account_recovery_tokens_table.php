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
        // Vérifie si la table n'existe pas déjà avant de la créer
        if (!Schema::hasTable('account_recovery_tokens')) {
            Schema::create('account_recovery_tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id'); 
                $table->string('token')->unique();
                $table->timestamp('expires_at');
                $table->timestamps();

                // Clé étrangère avec suppression en cascade
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_recovery_tokens');
    }
};