<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations - Convert French status values to English
     *
     * @return void
     */
    public function up()
    {
        // Update all existing French status values to English equivalents
        DB::table('commandes')->where('statut', 'en attente')->update(['statut' => 'preparing']);
        DB::table('commandes')->where('statut', 'En cours de préparation')->update(['statut' => 'pending']);
        DB::table('commandes')->where('statut', 'En cours de livraison')->update(['statut' => 'on_delivery']);
        DB::table('commandes')->where('statut', 'Livré')->update(['statut' => 'completed']);
        DB::table('commandes')->where('statut', 'Annulé')->update(['statut' => 'cancelled']);

        // Handle any other case variations
        DB::table('commandes')->where('statut', 'LIKE', '%attente%')->update(['statut' => 'preparing']);
        DB::table('commandes')->where('statut', 'LIKE', '%préparation%')->update(['statut' => 'pending']);
        DB::table('commandes')->where('statut', 'LIKE', '%livraison%')->update(['statut' => 'on_delivery']);
        DB::table('commandes')->where('statut', 'LIKE', '%Livré%')->update(['statut' => 'completed']);
        DB::table('commandes')->where('statut', 'LIKE', '%Annulé%')->update(['statut' => 'cancelled']);
    }

    /**
     * Reverse the migrations - Convert English status values back to French
     *
     * @return void
     */
    public function down()
    {
        DB::table('commandes')->where('statut', 'preparing')->update(['statut' => 'en attente']);
        DB::table('commandes')->where('statut', 'pending')->update(['statut' => 'En cours de préparation']);
        DB::table('commandes')->where('statut', 'on_delivery')->update(['statut' => 'En cours de livraison']);
        DB::table('commandes')->where('statut', 'completed')->update(['statut' => 'Livré']);
        DB::table('commandes')->where('statut', 'cancelled')->update(['statut' => 'Annulé']);
    }
};
