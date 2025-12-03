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
        Schema::create('payements', function (Blueprint $table) {
          $table->id();
            // Relation 1:1 → UNIQUE
            $table->foreignId('commande_id')
                  ->constrained('commandes')
                  ->unique()
                  ->onDelete('cascade');
            // Informations du paiement
            $table->enum('statut', ['pending', 'successful', 'failed'])
                  ->default('pending');
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 10, 2);

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
        Schema::dropIfExists('payements');
    }
};
