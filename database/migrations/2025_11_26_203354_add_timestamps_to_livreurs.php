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
        $table->timestamps(); // ajoute created_at et updated_at
    });
}

public function down()
{
    Schema::table('livreurs', function (Blueprint $table) {
        $table->dropTimestamps();
    });
}

};
