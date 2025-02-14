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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name'); // Champ pour le prénom
            $table->string('last_name');  // Champ pour le nom de famille
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->date('date_of_birth'); // Champ pour la date de naissance
            $table->enum('gender', ['male', 'female']); // Champ pour le sexe
            $table->rememberToken();
            
            // Ajout des nouveaux champs
            $table->text('bio')->nullable();   // Champ pour la bio de l'utilisateur
            $table->string('profile_image')->nullable(); // Change 'image' en 'profile_image'


            $table->timestamps();
            $table->softDeletes(); // Ajoute la colonne 'deleted_at' pour SoftDeletes
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
