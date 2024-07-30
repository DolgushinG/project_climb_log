<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GuidRoutesOutdoor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guid_routes_outdoors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('route_name');
            $table->integer('place_id');
            $table->integer('country_id');
            $table->integer('area_id');
            $table->string('author')->nullable();
            $table->integer('amount_bolt')->nullable();
            $table->integer('length')->nullable();
            $table->integer('place_route_id');
            $table->string('image')->nullable();
            $table->string('grade');
            $table->string('web_link')->nullable();
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
        Schema::dropIfExists('guid_routes_outdoors');
    }
}
