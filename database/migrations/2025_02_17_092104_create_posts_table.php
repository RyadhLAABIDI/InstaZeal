<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->string('media')->nullable(); // Image ou vidéo
            $table->enum('media_type', ['image', 'video']); // Type de média
            $table->enum('visibility', ['public', 'private', 'friends'])->default('public'); // Visibilité du post
            $table->timestamps();
            $table->softDeletes(); // Ajoute la colonne `deleted_at` pour la suppression douce
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
