<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RoutesOutdoor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('routes_outdoors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id');
            $table->integer('route_id');
            $table->integer('route_name');
            $table->integer('place_id');
            $table->integer('country_id');
            $table->integer('area_id');
            $table->integer('place_route_id');
            $table->string('grade');
            $table->string('zone')->nullable();
            $table->string('value')->nullable();
            $table->string('flash_value')->nullable();
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
        Schema::dropIfExists('routes_outdoors');
    }
}
