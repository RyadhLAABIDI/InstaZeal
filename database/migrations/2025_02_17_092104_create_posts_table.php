<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('title')->nullable(); // Titre du post
            $table->string('media')->nullable(); // Image ou vidéo
            $table->enum('media_type', ['image', 'video']); // Type de média (image ou vidéo)
            $table->enum('visibility', ['public', 'private', 'friends', 'close_friends'])->default('public'); // Visibilité du post
            $table->string('categorie')->nullable(); // Ajout du champ categorie
            $table->timestamps();
            $table->softDeletes(); // Suppression douce

            // Définition de la clé étrangère
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};