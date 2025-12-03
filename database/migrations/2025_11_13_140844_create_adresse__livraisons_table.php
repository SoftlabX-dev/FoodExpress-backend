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
       Schema::create('adresses_livraison', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('full_name');
    $table->string('phone');
    $table->string('street_address');
    $table->text('delivery_instructions')->nullable();
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
        // ✅ CORRECTION : Utiliser le même nom que dans up()
        Schema::dropIfExists('adresses_livraison');
    }
};
