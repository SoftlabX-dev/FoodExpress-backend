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
    Schema::table('plats', function (Blueprint $table) {
        $table->boolean('isAvailable')->default(true);
        $table->boolean('isPopular')->default(false);
        $table->boolean('isFeatured')->default(false);
        $table->decimal('discount', 5, 2)->default(0); // exemple: 10.50% ou 5 DH
    });
}

public function down()
{
    Schema::table('plats', function (Blueprint $table) {
        $table->dropColumn(['isAvailable', 'isPopular', 'isFeatured', 'discount']);
    });
}

};
