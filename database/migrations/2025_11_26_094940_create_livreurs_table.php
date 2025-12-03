<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up(): void
    {
        Schema::create('livreurs', function (Blueprint $table) {
            $table->id();

            // Informations personnelles
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->unique();

            // Statut du livreur
            $table->enum('statut', ['active', 'inactive'])->default('active');

            // Localisation et disponibilité
            $table->boolean('available')->default(true);
            $table->string('current_location')->nullable(); // ex: "Agadir"
            
            // Authentification si tu veux chaque livreur avec login
            $table->string('password');

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('livreurs');
    }
};
