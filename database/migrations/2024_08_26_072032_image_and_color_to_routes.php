<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ImageAndColorToRoutes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->string('color')->after('route_id')->default("not_set_color");
            $table->string('color_view')->after('route_id')->default("not_set_color");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->string('color')->after('route_id')->default("not_set_color");
            $table->string('color_view')->after('route_id')->default("not_set_color");
        });
    }
}
