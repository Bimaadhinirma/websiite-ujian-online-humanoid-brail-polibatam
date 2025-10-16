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
        // NOTE: This uses Laravel's schema change() which requires
        // the doctrine/dbal package at runtime. Install via:
        // composer require doctrine/dbal
        Schema::table('periods', function (Blueprint $table) {
            $table->integer('duration_minutes')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to NOT NULL (restore previous default if you want)
        Schema::table('periods', function (Blueprint $table) {
            $table->integer('duration_minutes')->nullable(false)->change();
        });
    }
};
