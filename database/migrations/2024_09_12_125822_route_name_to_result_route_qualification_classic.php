<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RouteNameToResultRouteQualificationClassic extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('result_route_qualification_classic', function (Blueprint $table) {
            $table->string('route_name')->after('route_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('result_route_qualification_classic', function (Blueprint $table) {
            $table->string('route_name')->after('route_id')->nullable();
        });
    }
}
