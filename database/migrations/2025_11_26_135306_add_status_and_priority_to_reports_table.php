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
        Schema::table('reports', function (Blueprint $table) {
            $table->enum('status', ['unread', 'read', 'resolved'])->default('unread')->after('message');
            $table->enum('priority', ['low', 'medium', 'high'])->default('low')->after('status');
            $table->timestamp('read_at')->nullable()->after('priority');
            $table->timestamp('resolved_at')->nullable()->after('read_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            //
        });
    }
};
