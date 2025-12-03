<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 1. Supprimer la contrainte foreign key
            $table->dropForeign(['driver_id']);

            // 2. Supprimer la colonne
            $table->dropColumn('driver_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Recréer la colonne
            $table->unsignedBigInteger('driver_id')->nullable();

            // Recréer la foreign key
            $table->foreign('driver_id')->references('id')->on('users');
        });
    }
};
