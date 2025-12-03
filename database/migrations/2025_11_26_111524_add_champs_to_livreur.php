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
        Schema::table('livreurs', function (Blueprint $table) {
          $table->string('vehicle_type'); 
            $table->string('vehicle_plate')->nullable();
              $table->decimal('rating', 3, 2)->default(0.00); 
            $table->integer('total_deliveries')->default(0);
             $table->integer('completed_deliveries')->default(0);
            $table->decimal('total_earnings', 10, 2)->default(0.00);
            $table->string('profile_image')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('livreurs', function (Blueprint $table) {
            //
        });
    }
};
