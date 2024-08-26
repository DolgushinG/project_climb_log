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
            $table->string('color')->after('route_id')->default("#000000");
            $table->string('color_view')->after('route_id')->default("#000000");
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
            $table->string('color')->after('route_id')->default("#000000");
            $table->string('color_view')->after('route_id')->default("#000000");
        });
    }
}
