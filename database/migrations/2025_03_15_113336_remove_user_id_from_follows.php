<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reverse the issue by removing the unnecessary column.
     */
    public function up(): void
    {
        Schema::table('follows', function (Blueprint $table) {
            $table->dropColumn('user_id'); // Suppression de la colonne user_id
        });
    }

    /**
     * Revert the changes if needed.
     */
    public function down(): void
    {
        Schema::table('follows', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(); // On le remet si on rollback
        });
    }
};
