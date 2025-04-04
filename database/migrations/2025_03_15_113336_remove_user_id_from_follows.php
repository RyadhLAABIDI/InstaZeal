<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Vérifie si la table et la colonne existent avant de supprimer
        if (Schema::hasTable('follows') && Schema::hasColumn('follows', 'user_id')) {
            Schema::table('follows', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('follows')) {
            Schema::table('follows', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable();
            });
        }
    }
};