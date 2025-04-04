<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Exécuter la migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();  // ID unique pour la notification
            $table->unsignedBigInteger('user_id');  // ID de l'utilisateur qui reçoit la notification
            $table->string('type');  // Type de notification (ex: "follow", "message", etc.)
            $table->text('message');  // Le contenu du message de notification
            $table->boolean('is_read')->default(false);  // Statut de lecture de la notification (par défaut à false)
            $table->timestamps();  // Timestamp de création et mise à jour de la notification

            // Définir une clé étrangère qui référence l'utilisateur qui reçoit la notification
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Répudier la migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
