<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Création de la table 'users' si elle n'existe pas encore
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female']);
            $table->text('bio')->nullable();
            $table->string('profile_image')->nullable();
            $table->boolean('is_private')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // Ajout de la colonne 'registration_complete' après la colonne 'is_private'
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('registration_complete')
                  ->default(false)
                  ->after('is_private'); // Ajouter la colonne après 'is_private'
        });

        // Création de la table 'password_reset_tokens'
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Création de la table 'sessions'
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        // Désactiver temporairement les contraintes FK
        Schema::disableForeignKeyConstraints();
        
        // Supprimer les tables
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        
        // Réactiver les contraintes
        Schema::enableForeignKeyConstraints();
    }
};
