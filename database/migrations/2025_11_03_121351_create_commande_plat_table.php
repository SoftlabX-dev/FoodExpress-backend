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
  public function up()
{
   Schema::create('commande_plat', function (Blueprint $table) {
    $table->id();

    $table->foreignId('commande_id')
          ->constrained('commandes')
          ->onDelete('cascade');

    $table->foreignId('plat_id')
          ->constrained('plats')
          ->onDelete('cascade');

    $table->integer('quantite');
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
        Schema::dropIfExists('commande_plat');
    }
};
